<#
.SYNOPSIS
	Restores a SQL dump into the LocalWP WordPress database.

.DESCRIPTION
	Discovers the MySQL connection for a LocalWP site (port from sites.json,
	credentials from wp-config.php located by walking up from the theme
	directory) and imports a dump created by scripts/db-backup.ps1 or
	scripts/db-backup.sh.

	Before importing, a safety backup of the current database is written to
	db-backups/ inside the theme so the previous state can be recovered if
	needed.

.PARAMETER BackupFile
	Path to the .sql file to import, relative to the current directory or
	absolute.

.PARAMETER Domain
	The LocalWP site domain. Defaults to lake-tahoe-travel.local.

.EXAMPLE
	.\scripts\db-restore.ps1 .\db-backups\ltt-db-2026-09-09-143000.sql
	.\scripts\db-restore.ps1 C:\Users\Nestor\Downloads\dump-from-teammate.sql
#>
[CmdletBinding()]
param(
	[Parameter( Mandatory = $true, Position = 0 )]
	[string]$BackupFile,

	[Parameter()]
	[string]$Domain = 'lake-tahoe-travel.local'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Get-LocalSiteMySqlPort {
	param([string]$SiteDomain)

	$sitesJsonPath = Join-Path $env:APPDATA 'Local\sites.json'
	if ( -not ( Test-Path -LiteralPath $sitesJsonPath ) ) {
		throw "LocalWP sites.json not found at '$sitesJsonPath'. Is LocalWP installed?"
	}

	$sites = ( Get-Content -LiteralPath $sitesJsonPath -Raw | ConvertFrom-Json ).PSObject.Properties.Value
	$site = $sites | Where-Object { $_.domain -eq $SiteDomain } | Select-Object -First 1
	if ( -not $site ) {
		$known = ( $sites | ForEach-Object { $_.domain } ) -join ', '
		throw "Site with domain '$SiteDomain' not found in LocalWP. Known domains: $known"
	}

	$port = $site.services.mysql.ports.MYSQL | Select-Object -First 1
	if ( -not $port ) {
		throw "No MySQL port registered for '$SiteDomain' in sites.json. Is the site created in LocalWP?"
	}

	return [int]$port
}

function Find-WpConfig {
	param([string]$StartDir)

	$dir = $StartDir
	while ( $dir ) {
		$candidate = Join-Path $dir 'wp-config.php'
		if ( Test-Path -LiteralPath $candidate ) {
			return $candidate
		}
		$parent = Split-Path -Parent $dir
		if ( $parent -eq $dir ) {
			break
		}
		$dir = $parent
	}

	throw "wp-config.php not found in any parent directory of '$StartDir'. Is the theme inside a WordPress installation?"
}

function Get-WpConfigValue {
	param(
		[string]$ConfigPath,
		[string]$Constant
	)

	$match = Select-String -LiteralPath $ConfigPath -Pattern "define\(\s*'$Constant',\s*'([^']*)'\s*\)" | Select-Object -First 1
	if ( -not $match ) {
		throw "Constant '$Constant' not found in '$ConfigPath'."
	}

	return $match.Matches[0].Groups[1].Value
}

function Get-LocalMySqlBinary {
	param([string]$BinaryName)

	$roots = @(
		( Join-Path $env:APPDATA 'Local\lightning-services' ),
		( Join-Path $env:LOCALAPPDATA 'Programs\Local\resources\extraResources\lightning-services' )
	)

	foreach ( $root in $roots ) {
		$found = Get-ChildItem -Path ( Join-Path $root "mysql-*\bin\win64\bin\$BinaryName" ) -ErrorAction SilentlyContinue |
			Sort-Object FullName -Descending |
			Select-Object -First 1
		if ( $found ) {
			return $found.FullName
		}
	}

	throw "$BinaryName not found under LocalWP lightning-services. Is a MySQL service installed in LocalWP?"
}

$themeRoot = Split-Path -Parent $PSScriptRoot
$wpConfigPath = Find-WpConfig -StartDir $themeRoot

if ( -not [System.IO.Path]::IsPathRooted( $BackupFile ) ) {
	$BackupFile = Join-Path ( Get-Location ).Path $BackupFile
}
if ( -not ( Test-Path -LiteralPath $BackupFile ) ) {
	throw "Backup file not found: '$BackupFile'."
}
$BackupFile = ( Resolve-Path -LiteralPath $BackupFile ).Path

$port = Get-LocalSiteMySqlPort -SiteDomain $Domain
$dbName = Get-WpConfigValue -ConfigPath $wpConfigPath -Constant 'DB_NAME'
$dbUser = Get-WpConfigValue -ConfigPath $wpConfigPath -Constant 'DB_USER'
$dbPassword = Get-WpConfigValue -ConfigPath $wpConfigPath -Constant 'DB_PASSWORD'
$mysqldump = Get-LocalMySqlBinary -BinaryName 'mysqldump.exe'
$mysql = Get-LocalMySqlBinary -BinaryName 'mysql.exe'

$confirmation = Read-Host "This will OVERWRITE the '$dbName' database on $Domain. Type YES to continue"
if ( $confirmation -cne 'YES' ) {
	Write-Host 'Aborted. No changes were made.' -ForegroundColor Yellow
	exit 0
}

$backupDir = Join-Path $themeRoot 'db-backups'
if ( -not ( Test-Path -LiteralPath $backupDir ) ) {
	New-Item -ItemType Directory -Path $backupDir | Out-Null
}

$timestamp = Get-Date -Format 'yyyy-MM-dd-HHmmss'
$safetyFile = Join-Path $backupDir "ltt-db-pre-restore-$timestamp.sql"

Write-Host "Creating safety backup of the current database ..." -ForegroundColor Cyan

& $mysqldump `
	"--host=127.0.0.1" `
	"--port=$port" `
	"--user=$dbUser" `
	"--password=$dbPassword" `
	'--default-character-set=utf8mb4' `
	'--single-transaction' `
	'--quick' `
	"--result-file=$safetyFile" `
	$dbName

if ( $LASTEXITCODE -ne 0 ) {
	Remove-Item -LiteralPath $safetyFile -ErrorAction SilentlyContinue
	throw "Safety backup failed with exit code $LASTEXITCODE. Is the LocalWP site running? Import aborted."
}
Write-Host "Safety backup: $safetyFile" -ForegroundColor Green

Write-Host "Importing '$BackupFile' into '$dbName' ..." -ForegroundColor Cyan

& $mysql `
	"--host=127.0.0.1" `
	"--port=$port" `
	"--user=$dbUser" `
	"--password=$dbPassword" `
	'--default-character-set=utf8mb4' `
	$dbName `
	-e "source $BackupFile"

if ( $LASTEXITCODE -ne 0 ) {
	throw "Import failed with exit code $LASTEXITCODE. The database may be partially restored; the safety backup is at '$safetyFile'."
}

Write-Host "Restore complete. If anything looks wrong, restore the safety backup:" -ForegroundColor Green
Write-Host "  .\scripts\db-restore.ps1 `"$safetyFile`"" -ForegroundColor Green
