<#
.SYNOPSIS
	Exports the LocalWP WordPress database to a timestamped SQL dump.

.DESCRIPTION
	Discovers the MySQL connection for a LocalWP site (port from sites.json,
	credentials from wp-config.php located by walking up from the theme
	directory) and runs mysqldump. The dump is written to db-backups/ inside
	the theme (git-ignored) and is interchangeable with the dumps produced by
	scripts/db-backup.sh on macOS.

.PARAMETER Domain
	The LocalWP site domain. Defaults to lake-tahoe-travel.local.

.EXAMPLE
	.\scripts\db-backup.ps1
	.\scripts\db-backup.ps1 -Domain another-site.local
#>
[CmdletBinding()]
param(
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

$port = Get-LocalSiteMySqlPort -SiteDomain $Domain
$dbName = Get-WpConfigValue -ConfigPath $wpConfigPath -Constant 'DB_NAME'
$dbUser = Get-WpConfigValue -ConfigPath $wpConfigPath -Constant 'DB_USER'
$dbPassword = Get-WpConfigValue -ConfigPath $wpConfigPath -Constant 'DB_PASSWORD'
$mysqldump = Get-LocalMySqlBinary -BinaryName 'mysqldump.exe'

$backupDir = Join-Path $themeRoot 'db-backups'
if ( -not ( Test-Path -LiteralPath $backupDir ) ) {
	New-Item -ItemType Directory -Path $backupDir | Out-Null
}

$timestamp = Get-Date -Format 'yyyy-MM-dd-HHmmss'
$outputFile = Join-Path $backupDir "ltt-db-$timestamp.sql"

Write-Host "Exporting '$dbName' from $Domain (127.0.0.1:$port) ..." -ForegroundColor Cyan

& $mysqldump `
	"--host=127.0.0.1" `
	"--port=$port" `
	"--user=$dbUser" `
	"--password=$dbPassword" `
	'--default-character-set=utf8mb4' `
	'--single-transaction' `
	'--quick' `
	"--result-file=$outputFile" `
	$dbName

if ( $LASTEXITCODE -ne 0 ) {
	Remove-Item -LiteralPath $outputFile -ErrorAction SilentlyContinue
	throw "mysqldump failed with exit code $LASTEXITCODE. Is the LocalWP site running?"
}

$sizeKb = [math]::Round( ( Get-Item -LiteralPath $outputFile ).Length / 1KB, 1 )
Write-Host "Backup created: $outputFile ($sizeKb KB)" -ForegroundColor Green
