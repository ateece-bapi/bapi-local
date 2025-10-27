#!/usr/bin/env bash
# Usage:
#   ./import-site.sh /path/to/bapi-db-dump.sql /path/to/wp-content.tar.gz
#
# This script assumes:
# - docker-compose is up (docker-compose up -d)
# - ./wordpress is mounted to container /var/www/html
# - .env contains DB credentials
# - wp-cli service exists (see docker-compose.yml)

set -euo pipefail

DB_DUMP=${1:-}
WPCONTENT_ARCHIVE=${2:-}

if [[ -z "$DB_DUMP" || -z "$WPCONTENT_ARCHIVE" ]]; then
  echo "Usage: $0 /path/to/dump.sql /path/to/wp-content.tar.gz"
  exit 2
fi

# Import DB
echo "Importing DB dump into container 'db'..."
# get env for mysql credentials from .env
source .env
docker cp "$DB_DUMP" db:/tmp/dump.sql
docker exec -i $(docker-compose ps -q db) sh -c "exec mysqldump >/dev/null 2>&1 || true; mysql -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} < /tmp/dump.sql"
docker exec -i $(docker-compose ps -q db) sh -c "rm -f /tmp/dump.sql"
echo "DB import complete."

# Extract wp-content into ./wordpress/wp-content
echo "Extracting wp-content archive into ./wordpress/wp-content (existing files will be backed up to ./wp-content.bak)..."
if [ -d "./wordpress/wp-content" ]; then
  mv ./wordpress/wp-content ./wp-content.bak.$(date +"%s")
fi
mkdir -p ./wordpress
tar -xzf "$WPCONTENT_ARCHIVE" -C ./wordpress
echo "wp-content extraction complete."

# Fix file ownership
echo "Fixing ownership to www-data:www-data inside container..."
docker-compose exec wordpress chown -R www-data:www-data /var/www/html/wp-content

# Run WP-CLI search-replace to rewrite URLs
echo "Running search-replace (production -> local)..."
docker-compose run --rm wpcli search-replace "$PRODUCTION_URL" "$LOCAL_URL" --skip-columns=guid --all-tables --allow-root --precise
docker-compose run --rm wpcli --allow-root rewrite flush

echo "Import finished. Visit $LOCAL_URL to check the site."