#!/usr/bin/env bash
#
# Exports the LocalWP WordPress database to a timestamped SQL dump.
#
# Discovers the MySQL connection for a LocalWP site (port/socket from
# sites.json, credentials from wp-config.php located by walking up from the
# theme directory) and runs mysqldump. The dump is written to db-backups/
# inside the theme (git-ignored) and is interchangeable with the dumps
# produced by scripts/db-backup.ps1 on Windows.
#
# Usage:
#   ./scripts/db-backup.sh [domain]
#
#   domain   LocalWP site domain. Defaults to lake-tahoe-travel.local.

set -euo pipefail

DOMAIN="${1:-lake-tahoe-travel.local}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
SITES_JSON="${HOME}/Library/Application Support/Local/sites.json"

fail() {
	echo "ERROR: $1" >&2
	exit 1
}

find_wp_config() {
	local dir="$1"
	while [ -n "${dir}" ] && [ "${dir}" != "/" ]; do
		if [ -f "${dir}/wp-config.php" ]; then
			printf '%s' "${dir}/wp-config.php"
			return 0
		fi
		dir="$(dirname "${dir}")"
	done
	return 1
}

wp_config_value() {
	local constant="$1"
	local value
	value="$(grep -E "define\([[:space:]]*'${constant}',[[:space:]]*'[^']*'[[:space:]]*\)" "${WP_CONFIG}" \
		| head -n 1 \
		| sed -E "s/.*define\([[:space:]]*'${constant}',[[:space:]]*'([^']*)'[[:space:]]*\).*/\1/")"
	[ -n "${value}" ] || fail "Constant '${constant}' not found in '${WP_CONFIG}'."
	printf '%s' "${value}"
}

find_mysql_binary() {
	local binary="$1"
	local found
	found="$(find "${HOME}/Library/Application Support/Local/lightning-services" \
			-type f -path "*/mysql-*/bin/*/bin/${binary}" 2>/dev/null | sort -r | head -n 1)"
	if [ -z "${found}" ]; then
		found="$(command -v "${binary}" || true)"
	fi
	[ -n "${found}" ] || fail "${binary} not found. Is a MySQL service installed in LocalWP?"
	printf '%s' "${found}"
}

WP_CONFIG="$(find_wp_config "${THEME_ROOT}")" \
	|| fail "wp-config.php not found in any parent directory of '${THEME_ROOT}'. Is the theme inside a WordPress installation?"
[ -f "${SITES_JSON}" ] || fail "LocalWP sites.json not found at '${SITES_JSON}'. Is LocalWP installed?"
command -v python3 >/dev/null 2>&1 || fail "python3 is required to read LocalWP sites.json."

SITE_INFO="$(DOMAIN="${DOMAIN}" SITES_JSON="${SITES_JSON}" python3 <<'PY'
import json, os, sys

with open(os.environ["SITES_JSON"], "r", encoding="utf-8") as handle:
	sites = json.load(handle)

match = next(((site_id, s) for site_id, s in sites.items() if s.get("domain") == os.environ["DOMAIN"]), None)
if match is None:
	known = ", ".join(sorted(s.get("domain", "?") for s in sites.values()))
	sys.exit(f"Site with domain '{os.environ['DOMAIN']}' not found in LocalWP. Known domains: {known}")

site_id, site = match
mysql = site.get("services", {}).get("mysql", {})
ports = mysql.get("ports", {}).get("MYSQL", [])
port = ports[0] if ports else ""
socket = os.path.expanduser(f"~/Library/Application Support/Local/run/{site_id}/mysql/mysqld.sock")
print(f"{port}\t{socket}")
PY
)" || fail "${SITE_INFO}"

MYSQL_PORT="$(printf '%s' "${SITE_INFO}" | cut -f1)"
MYSQL_SOCKET="$(printf '%s' "${SITE_INFO}" | cut -f2)"

DB_NAME="$(wp_config_value DB_NAME)"
DB_USER="$(wp_config_value DB_USER)"
DB_PASSWORD="$(wp_config_value DB_PASSWORD)"
MYSQLDUMP="$(find_mysql_binary mysqldump)"

CONN_ARGS=()
if [ -S "${MYSQL_SOCKET}" ]; then
	CONN_ARGS+=("--socket=${MYSQL_SOCKET}")
elif [ -n "${MYSQL_PORT}" ]; then
	CONN_ARGS+=(--host=127.0.0.1 "--port=${MYSQL_PORT}")
else
	fail "No MySQL port or socket found for '${DOMAIN}'. Is the site running in LocalWP?"
fi

BACKUP_DIR="${THEME_ROOT}/db-backups"
mkdir -p "${BACKUP_DIR}"
TIMESTAMP="$(date +%Y-%m-%d-%H%M%S)"
OUTPUT_FILE="${BACKUP_DIR}/ltt-db-${TIMESTAMP}.sql"

echo "Exporting '${DB_NAME}' from ${DOMAIN} ..."
"${MYSQLDUMP}" \
	"${CONN_ARGS[@]}" \
	"--user=${DB_USER}" \
	"--password=${DB_PASSWORD}" \
	--default-character-set=utf8mb4 \
	--single-transaction \
	--quick \
	"--result-file=${OUTPUT_FILE}" \
	"${DB_NAME}"

echo "Backup created: ${OUTPUT_FILE}"
