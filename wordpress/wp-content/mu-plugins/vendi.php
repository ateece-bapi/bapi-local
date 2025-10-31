<?php
/**
 * Plugin Name:     Silence plugin notice until the developers address
 * Description:     Filters _load_textdomain_just_in_time doing it wrong messages in 6.7
 */

add_filter(
    'doing_it_wrong_trigger_error',
    function ($value, $function_name, $message) {
        if ( ! $value) {
            return $value;
        }

        if ('_load_textdomain_just_in_time' !== $function_name) {
            return $value;
        }

        return false;
    },
    10,
    4,
);
