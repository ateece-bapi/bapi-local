<?php
/**
 * Local wp-config include.
 * Copy this file to ./wordpress/wp-config.local.php and include it from your real wp-config.php,
 * or merge constants into wp-config.php as needed.
 *
 * This template reads DB values from environment variables (from .env when using docker-compose env_file).
 */

if ( ! defined( 'ABSPATH' ) ) {
    // Load from env if available, otherwise fallback values.
    define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'bapi_local');
    define('DB_USER', getenv('MYSQL_USER') ?: 'bapi_user');
    define('DB_PASSWORD', getenv('MYSQL_PASSWORD') ?: 'change_me_local');
    define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: 'db:3306');
    define('DB_CHARSET', 'utf8');
    define('DB_COLLATE', 'utf8_unicode_ci');

    // Security keys: copy from production or generate locally for dev.
    define('AUTH_KEY',         'put your unique phrase here');
    define('SECURE_AUTH_KEY',  'put your unique phrase here');
    define('LOGGED_IN_KEY',    'put your unique phrase here');
    define('NONCE_KEY',        'put your unique phrase here');
    define('AUTH_SALT',        'put your unique phrase here');
    define('SECURE_AUTH_SALT', 'put your unique phrase here');
    define('LOGGED_IN_SALT',   'put your unique phrase here');
    define('NONCE_SALT',       'put your unique phrase here');

    // Local development settings
    define('WP_DEBUG', true);
    define('WP_DEBUG_LOG', true);
    define('WP_DEBUG_DISPLAY', false);
    define('WP_MEMORY_LIMIT', '256M');
    define('WP_MAX_MEMORY_LIMIT', '256M');

    // If you want to force site URLs (optional — search-replace will usually handle this)
    // define('WP_HOME', 'http://localhost:8000');
    // define('WP_SITEURL', 'http://localhost:8000');

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        @ini_set( 'display_errors', 0 );
        @ini_set( 'log_errors', 1 );
    }
}