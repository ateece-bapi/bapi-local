#!/usr/bin/env bash
# Stream a (gzipped or plain) SQL dump into the running DB container and extract wp-content.
# Usage: ./scripts/import-large-site.sh /path/to/bapi-dump.sql.gz /path/to/wp-content.tar.gz
set -euo pipefail

DB_DUMP=${1:-}
WPCONTENT_ARCHIVE=${2:-}

if [[ -z "$DB_DUMP" || -z "$WPCONTENT_ARCHIVE" ]]; then
  echo "Usage: $0 /path/to/bapi-dump.sql(.gz) /path/to/wp-content.tar.gz"
  exit 2
fi

if [ ! -f .env ]; then
  echo ".env not found. Copy .env.example -> .env and edit it."
  exit 3
fi

# Load .env (export variables)
set -o allexport
source .env
set +o allexport

echo "Starting core containers (db + wordpress + phpmyadmin)..."
docker-compose up -d db wordpress phpmyadmin

DB_CONTAINER_ID=$(docker-compose ps -q db)
if [[ -z "$DB_CONTAINER_ID" ]]; then
  echo "Could not find db container id."
  exit 4
fi

echo "Importing DB into ${MYSQL_DATABASE} (this may take a while)..."

# Detect gzip by extension or by file magic
if file --brief --mime-type "$DB_DUMP" | grep -q gzip || [[ "$DB_DUMP" == *.gz ]]; then
  gunzip -c "$DB_DUMP" | docker exec -i "$DB_CONTAINER_ID" sh -c "mysql -u'${MYSQL_USER}' -p'${MYSQL_PASSWORD}' '${MYSQL_DATABASE}'"
else
  docker exec -i "$DB_CONTAINER_ID" sh -c "mysql -u'${MYSQL_USER}' -p'${MYSQL_PASSWORD}' '${MYSQL_DATABASE}'" < "$DB_DUMP"
fi

echo "DB import complete."

# Extract wp-content into ./wordpress (back up existing)
if [ -d "./wordpress/wp-content" ]; then
  timestamp=$(date +%s)
  echo "Backing up existing ./wordpress/wp-content -> ./wp-content.bak.$timestamp"
  mv ./wordpress/wp-content ./wp-content.bak.$timestamp
fi

echo "Extracting wp-content archive into ./wordpress..."
mkdir -p ./wordpress
tar -xzf "$WPCONTENT_ARCHIVE" -C ./wordpress
echo "wp-content extraction complete."

# Fix file ownership (best-effort)
echo "Adjusting permissions inside wordpress container..."
docker-compose exec -T wordpress chown -R www-data:www-data /var/www/html/wp-content || true

# Run WP-CLI search-replace (handles serialized data)
echo "Running WP-CLI search-replace to update URLs (production -> local)..."
docker-compose run --rm wpcli search-replace "$PRODUCTION_URL" "$LOCAL_URL" --skip-columns=guid --all-tables --allow-root --precise || true

echo "Flushing rewrite rules..."
docker-compose run --rm wpcli rewrite flush --allow-root || true

echo "Import finished. Visit $LOCAL_URL to check the site."