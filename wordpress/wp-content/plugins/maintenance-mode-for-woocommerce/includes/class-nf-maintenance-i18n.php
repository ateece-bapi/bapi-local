<?php

if ( !class_exists( 'Nf_Maintenance_i18n' ) ) {

    class Nf_Maintenance_i18n {

    	public function load_plugin_textdomain() {

            $path = 'maintenance-mode-for-woocommerce/languages';

            $mofile = sprintf( '%s-%s.mo', 'maintenance-mode-for-woocommerce', get_locale() );

            // Load local translations
          	load_plugin_textdomain(
        			'maintenance-mode-for-woocommerce',
        			false,
        			$path
            );

            // Merge user & wp translations
            $domain_path = path_join( WP_PLUGIN_DIR, "maintenance-mode-for-woocommerce/languages" );
            load_textdomain( 'maintenance-mode-for-woocommerce', path_join( $domain_path, $mofile ) );

    	}

    }

}