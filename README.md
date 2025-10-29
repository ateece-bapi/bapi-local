```markdown
# bapi-local — Local WordPress environment

Quick overview
- Local Docker stack: WordPress (PHP 8.2), MySQL 8.0, phpMyAdmin, WP-CLI, MailHog, optional Redis.
- Keep secrets out of Git: use a local `.env` (do not commit). Use `.env.example` as the template.

Table of contents
- [Prerequisites](#prerequisites)
- [Quick start](#quick-start)
- [Importing site data](#importing-site-data)
- [Common tasks](#common-tasks)
- [Service URLs](#service-urls)
- [Drop-ins and mu-plugins](#drop-ins-and-mu-plugins)
- [Troubleshooting](#troubleshooting)
- [Security & removing sensitive files from history](#security--removing-sensitive-files-from-history)
- [Extras](#extras)

Prerequisites
- Docker and docker-compose installed.
- Copy `.env.example` to `.env` and edit values before starting the stack.

Quick start
1. Copy and edit env:
   ```
   cp .env.example .env
   # edit .env to set DB credentials, site URL, etc.
   ```
2. Start Docker:
   ```
  docker compose up -d
   ```

Importing site data
- Recommended workflow: export production DB and wp-content, transfer to your machine, then run the import script.

Example server-side DB export (gzip):
```
mysqldump -u PROD_DB_USER -p'PROD_DB_PASS' --single-transaction --quick --lock-tables=false PROD_DB_NAME | gzip -c > /tmp/bapi-dump.sql.gz
```

Transfer to local host (examples):
```
scp user@prod:/tmp/bapi-dump.sql.gz .
scp user@prod:/tmp/wp-content.tar.gz .
# Or rsync uploads only:
rsync -azP user@prod:/path/to/wordpress/wp-content/uploads/ ./wordpress/wp-content/uploads/
```

Run import script (supports gzipped or plain SQL and a wp-content tarball):
```
chmod +x scripts/import-site.sh
./scripts/import-site.sh /path/to/bapi-dump.sql(.gz) /path/to/wp-content.tar.gz
```

Common tasks
- List users:
  ```
  docker compose run --rm wpcli user list --allow-root
  ```
- Reset admin password:
  ```
  docker compose run --rm wpcli user update admin --user_pass="DevPass123!" --allow-root
  ```
- Reinitialize DB if env was wrong:
  ```
  docker compose down -v
  # ensure .env is correct
  docker compose up -d
  ```

Service URLs
- Site: http://localhost:8000
- phpMyAdmin: http://localhost:8081
- MailHog UI: http://localhost:8025

Drop-ins and mu-plugins
If production uses drop-ins (object-cache.php, db.php, advanced-cache.php) or mu-plugins, copy them into `./wordpress/wp-content/` (or appropriate subfolders) for parity. Examples:
- `object-cache.php` → `wordpress/wp-content/object-cache.php`
- mu-plugins → `wordpress/wp-content/mu-plugins/`

Troubleshooting
- DB auth errors: confirm `.env` DB credentials match the imported DB.
- If DB was initialized incorrectly, remove DB volume and recreate:
  ```
  docker compose down -v
  docker compose up -d
## Sensitive files
Never commit or track files like `phpinfo.php`, database dumps, or other diagnostics. Remove them from the repo and production servers. Use `.gitignore` to keep these files out of version control.
## Updating WordPress, plugins, and themes safely
To update WordPress core, plugins, or themes in Docker:
1. Backup your database and `wp-content` directory.
2. Use WP-CLI in the container:
  ```
  docker compose run --rm wpcli plugin update --all --allow-root
  docker compose run --rm wpcli theme update --all --allow-root
  docker compose run --rm wpcli core update --allow-root
  ```
3. Test your site locally before deploying updates to production.
  ```
- Import errors: ensure your SQL dump is compatible with MySQL 8.0 and check `docker-compose logs db` for details.

Security & removing sensitive files from history
- Add secrets to `.env` and do not commit. Ensure `.env` is listed in `.gitignore`.
- To stop tracking a file but keep it locally:
  ```
  git rm --cached path/to/file
  git commit -m "Stop tracking path/to/file"
  git push origin main
  ```
- To remove a sensitive file from commit history (rewrites history — coordinate with collaborators):
  - Recommended: git-filter-repo
    ```
    pip install git-filter-repo
    git clone --mirror https://github.com/OWNER/REPO.git
    cd REPO.git
    git filter-repo --invert-paths --path path/to/file
    git push --force
    ```
  - Alternative: BFG Repo-Cleaner
    ```
    git clone --mirror https://github.com/OWNER/REPO.git
    java -jar bfg.jar --delete-files YOUR-FILENAME REPO.git
    cd REPO.git
    git reflog expire --expire=now --all && git gc --prune=now --aggressive
    git push --force
    ```

Extras
- Server-side export: if you provide the production DB name and user, I can give a tailored one-liner for export.
- wp-config.php local overrides (create `wp-config.local.php` and keep it out of repo):
```php
// Load local overrides (not committed)
if ( file_exists( __DIR__ . '/wp-config.local.php' ) ) {
    include __DIR__ . '/wp-config.local.php';
}
```

Notes
- The import script streams DB imports to avoid copying large files into containers.
- Always coordinate history rewrites with other collaborators to avoid disruption.
```