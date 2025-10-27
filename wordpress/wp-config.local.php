<?php
/**
 * Local wp-config include.
 * Place this file at ./wordpress/wp-config.local.php and include it from your real wp-config.php,
 * or copy/merge constants into wp-config.php as needed.
 *
 * This template reads DB values from environment variables (from .env when using docker-compose env_file).
 * It's safe for development — do NOT commit it to the repository.
 */

if ( ! defined( 'ABSPATH' ) ) {
    // Load from env if available, otherwise fallback values.
    define( 'DB_NAME', getenv( 'MYSQL_DATABASE' ) ?: 'bapi_local' );
    define( 'DB_USER', getenv( 'MYSQL_USER' ) ?: 'bapi_user' );
    define( 'DB_PASSWORD', getenv( 'MYSQL_PASSWORD' ) ?: 'change_me_local' );
    define( 'DB_HOST', getenv( 'WORDPRESS_DB_HOST' ) ?: 'db:3306' );

    // Charset & collation
    // utf8mb4 is recommended for modern WP installations (emoji, better unicode support).
    define( 'DB_CHARSET', 'utf8mb4' );
    define( 'DB_COLLATE', '' );

    // Authentication Unique Keys and Salts.
    // For local development you may use placeholder values, but consider copying production salts
    // or generate new ones at: https://api.wordpress.org/secret-key/1.1/salt/
    define( 'AUTH_KEY',         'put your unique phrase here' );
    define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
    define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
    define( 'NONCE_KEY',        'put your unique phrase here' );
    define( 'AUTH_SALT',        'put your unique phrase here' );
    define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
    define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
    define( 'NONCE_SALT',       'put your unique phrase here' );

    // Local development settings
    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_LOG', true );
    define( 'WP_DEBUG_DISPLAY', false );
    define( 'WP_MEMORY_LIMIT', '256M' );
    define( 'WP_MAX_MEMORY_LIMIT', '256M' );

    // Explicitly mark this environment for conditional logic in plugins/themes
    define( 'WP_ENVIRONMENT_TYPE', 'local' );

    // Disable persistent object cache locally unless you configure redis/memcached
    define( 'WP_CACHE', false );

    // Uncomment to force local URLs (usually not necessary because import/search-replace will update DB)
    // define('WP_HOME', 'http://localhost:8000');
    // define('WP_SITEURL', 'http://localhost:8000');

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        @ini_set( 'display_errors', '0' );
        @ini_set( 'log_errors', '1' );
    }
}