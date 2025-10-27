```markdown
# bapi-local (local WordPress environment)

Quick overview
- Work locally with Docker: WordPress (PHP 8.2), MySQL 8.0, phpMyAdmin, WP-CLI, MailHog, optional Redis.
- Keep secrets out of Git: use .env (not committed). Use .env.example in repo.

Basic workflow
1. Copy .env.example -> .env and edit DB credentials and URLs:
   cp .env.example .env
   code .env

2. Start Docker:
   docker-compose up -d

3. Export DB and wp-content from production and transfer to your machine:
   # DB (recommended streaming gzip)
   mysqldump -u PROD_DB_USER -p'PROD_DB_PASS' --single-transaction --quick --lock-tables=false PROD_DB_NAME | gzip -c > /tmp/bapi-dump.sql.gz
   scp user@prod:/tmp/bapi-dump.sql.gz .
   # wp-content
   tar -czf /tmp/wp-content.tar.gz -C /path/to/wordpress wp-content
   scp user@prod:/tmp/wp-content.tar.gz .
   # OR rsync uploads only:
   rsync -azP user@prod:/path/to/wordpress/wp-content/uploads/ ./wordpress/wp-content/uploads/

4. Run import script to populate local DB and wp-content:
   chmod +x scripts/import-site.sh
   ./scripts/import-site.sh /path/to/bapi-dump.sql(.gz) /path/to/wp-content.tar.gz

5. Reset admin password if needed:
   docker-compose run --rm wpcli user list --allow-root
   docker-compose run --rm wpcli user update admin --user_pass="DevPass123!" --allow-root

6. Visit the site:
   http://localhost:8000
   phpMyAdmin: http://localhost:8081
   MailHog UI: http://localhost:8025

Special notes
- The import script supports gzipped or plain SQL dumps and will stream the DB import to avoid copying large files into containers.
- Drop-ins: copy any production drop-in files (object-cache.php, db.php, advanced-cache.php) and mu-plugins into ./wordpress/wp-content/ before starting WordPress if you need parity. If object-cache expects Redis, ensure REDIS_HOST in .env is 'redis' and start the redis service.
- If you get DB auth errors: confirm .env credentials match your imported DB. If necessary, recreate the db container with correct MYSQL_* env values before importing:
  docker-compose down -v
  cp .env.example .env
  # edit .env
  docker-compose up -d

If you'd like, I can provide a one-line server-side export command tailored to your production DB name and user, or a wp-config.php include snippet to load local overrides safely.
```