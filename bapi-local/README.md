```markdown
# bapi-local (local WordPress environment)

Quick overview
- Work locally with Docker: WordPress (PHP 8.2), MySQL 8.0, phpMyAdmin, WP-CLI, MailHog, optional Redis.
- Keep secrets out of Git: use .env (not committed). Use .env.example in repo.

Basic workflow
1. Copy .env.example -> .env and edit DB credentials and URLs.
2. Add the docker-compose.yml, import script and wp-config.local.php (from the files above).
3. Start Docker:
   docker-compose up -d
4. Export DB and wp-content from production and transfer to your machine:
   # DB (recommended streaming gzip)
   mysqldump -u PROD_DB_USER -p'PROD_DB_PASS' --single-transaction --quick --lock-tables=false PROD_DB_NAME | gzip -c > /tmp/bapi-dump.sql.gz
   scp user@prod:/tmp/bapi-dump.sql.gz .
   # wp-content
   tar -czf /tmp/wp-content.tar.gz -C /path/to/wordpress wp-content
   scp user@prod:/tmp/wp-content.tar.gz .
   # OR rsync uploads only:
   rsync -azP user@prod:/path/to/wordpress/wp-content/uploads/ ./wordpress/wp-content/uploads/

5. Run import script to populate local DB and wp-content:
   chmod +x import-large-site.sh
   ./import-large-site.sh /path/to/bapi-dump.sql.gz /path/to/wp-content.tar.gz

6. Reset admin password if needed:
   docker-compose run --rm wpcli user list --allow-root
   docker-compose run --rm wpcli user update admin --user_pass="DevPass123!" --allow-root

7. Visit the site:
   http://localhost:8000
   phpMyAdmin: http://localhost:8081
   MailHog UI: http://localhost:8025

Special notes
- Drop-ins: copy any production drop-in files (object-cache.php, db.php, advanced-cache.php) and mu-plugins into ./wordpress/wp-content/ before starting WordPress if you need parity. If object-cache expects Redis, ensure REDIS_HOST in .env is 'redis' and start the redis service.
- If you get DB auth errors: confirm .env credentials match your imported DB. If necessary, recreate the db container with correct MYSQL_* env values before importing.
- After import, the script runs wp search-replace to swap production -> local URLs (it skips GUIDs). Verify serialized-data-heavy plugins by browsing the admin.

If you'd like, I can:
- Produce a sanitized export command you can run on production (paste DB name + DB user) to generate bapi-dump.sql.gz and wp-content.tar.gz,
- Provide a small wp-config.php merge snippet tailored to your production wp-config (if you paste a sanitized copy).
```