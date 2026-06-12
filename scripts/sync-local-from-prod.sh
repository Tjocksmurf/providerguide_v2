#!/usr/bin/env bash
#
# Refresh the local DDEV environment from production.
#
#   - dumps the production database over SSH (credentials are read on the server
#     from settings.local.php, so nothing secret lives in this script)
#   - imports it into the local DDEV database
#   - rsyncs the public files directory (images, uploads)
#   - rebuilds caches
#
# Usage:   ./scripts/sync-local-from-prod.sh
# Run it from the host (not inside the container). Requires: ddev, ssh, rsync.
#
set -euo pipefail

REMOTE_HOST="cbb"
REMOTE_PATH="/home/mailbook/providerguide.com.au/ndis"

# Resolve the project root (parent of this scripts/ dir).
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

DUMP="/tmp/providerguide-prod-$(date +%Y%m%d-%H%M%S).sql.gz"

echo "==> Ensuring DDEV is running..."
ddev start >/dev/null

echo "==> Dumping production database from ${REMOTE_HOST}..."
# Parse DB credentials on the server by including settings.local.php, then dump.
ssh "$REMOTE_HOST" "cd '$REMOTE_PATH' && \
  CREDS=\$(php -d error_reporting=0 -r '\$databases=[]; include \"web/sites/default/settings.local.php\"; \$d=\$databases[\"default\"][\"default\"]; echo \$d[\"database\"].\"\n\".\$d[\"username\"].\"\n\".\$d[\"password\"].\"\n\".(\$d[\"host\"] ?? \"localhost\");') && \
  DB=\$(printf '%s\n' \"\$CREDS\" | sed -n 1p) && \
  DBUSER=\$(printf '%s\n' \"\$CREDS\" | sed -n 2p) && \
  DBPASS=\$(printf '%s\n' \"\$CREDS\" | sed -n 3p) && \
  DBHOST=\$(printf '%s\n' \"\$CREDS\" | sed -n 4p) && \
  mysqldump --no-tablespaces --single-transaction --quick \
    -u \"\$DBUSER\" -p\"\$DBPASS\" -h \"\$DBHOST\" \"\$DB\" 2>/dev/null | gzip" > "$DUMP"

echo "    Dump size: $(du -h "$DUMP" | cut -f1)"

echo "==> Importing into local DDEV database..."
ddev import-db --file="$DUMP"
rm -f "$DUMP"

echo "==> Syncing public files (sites/default/files)..."
# tar-over-SSH (the server has no rsync). Skips derivative/cache dirs that
# Drupal regenerates on demand.
mkdir -p "${PROJECT_DIR}/web/sites/default/files"
ssh "$REMOTE_HOST" "cd '${REMOTE_PATH}/web/sites/default/files' && \
  tar czf - --exclude=css --exclude=js --exclude=php --exclude=styles ." \
  | tar xzf - -C "${PROJECT_DIR}/web/sites/default/files"

echo "==> Rebuilding caches..."
ddev drush cr

# Optional: scrub user emails so local can never email real users.
# Uncomment if you want sanitised data locally.
# ddev drush sql:sanitize -y

echo ""
echo "Local is now in sync with production."
echo "Admin login link:"
ddev drush uli
