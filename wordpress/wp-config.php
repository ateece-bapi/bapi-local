<?php
// Load local overrides if present (keeps secrets out of VCS)
if ( file_exists( __DIR__ . '/wp-config.local.php' ) ) {
    require_once __DIR__ . '/wp-config.local.php';
}