(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
    console.log('Advanced Extra Fees for WooCommerce Admin JS Loaded');

    $(document).ready(function() {

        $( '.woocommerce-help-tip' ).tipTip( {
            attribute: 'data-tip',
            fadeIn: 50,
            fadeOut: 50,
            delay: 200,
            keepAlive: true,
        } );

        // Show/Hide combine fees field
        $('#dsaefw_combine_fees').change(function() {
            if ($(this).is(':checked')) {
                $('.dsaefw_combine_fees_field').closest('tr').show();
            } else {
                $('.dsaefw_combine_fees_field').closest('tr').hide();
            }
        }).trigger( 'change' );

        // Activate WooCommerce menu while on manage fees page
        if( window.location.search.includes('post_type=wc_conditional_fee') ) {
            $('#toplevel_page_woocommerce, #toplevel_page_woocommerce > a').addClass('wp-has-current-submenu wp-menu-open').removeClass('wp-not-current-submenu');
            $('#toplevel_page_woocommerce ul li').each(function() {
                const url = $(this).find('a').attr('href');
                if( url && url.includes('admin.php?page=wc-settings') ) {
                    $(this).addClass('current');
                }
            });
        }

        $('input[name="post_title"]').attr('required', 'required');

        /** Show/Hide toggle */
        $('#fee_settings_select_fee_type').change(function() {
            const feeType = $(this).val();
            
            // Show all elements first
            $('.js-hide-if-fee_type').show();
            
            // Hide elements based on the selected fee type
            $('.js-hide-if-fee_type-' + feeType).hide();

            if ( $( this ).val() === 'fixed' ) {
                $( '#fee_settings_product_cost' ).attr('type', 'text');
                $( '#fee_settings_product_cost' ).attr( 'placeholder', coditional_vars.currency_symbol );
                $( '#fee_settings_product_cost' ).attr( 'min', '0' );
                $( '#fee_settings_product_cost' ).attr( 'step', '0.01' );
            } else if( $( this ).val() === 'percentage' ) {
                $( '#fee_settings_product_cost' ).attr('type', 'number');
                $( '#fee_settings_product_cost' ).attr( 'placeholder', '%' );
                $( '#fee_settings_product_cost' ).attr( 'min', '0' );
                $( '#fee_settings_product_cost' ).attr( 'step', '0.01' );
            } else if( $( this ).val() === 'both' ) {
                $( '#fee_settings_product_cost' ).attr('type', 'text');
                $( '#fee_settings_product_cost' ).attr( 'placeholder', '% + ' + coditional_vars.currency_symbol );
            }
            $( '#fee_settings_product_cost' ).attr('required', 'required');
            $('#fee_settings_product_cost').val('');
        });

        $('#fee_chk_qty_price').change(function() {
            $('.js-hide-if-fee_per_quantity').hide();
            if( $(this).is(':checked') ) {
                $('.js-hide-if-fee_per_quantity-disabled').show();
            }
        });

        $('#is_allow_custom_weight_base').change(function() {
            $('.js-hide-if-weight_base-disabled').hide();
            if( $(this).is(':checked') ) {
                $('.js-hide-if-weight_base-disabled').show();
            }
        });

        // Use for select2 init in our plugin
        $('.dsaefw_select').selectWoo();

        // Use for datepicker init in our plugin
        $( '#fee_settings_start_date' ).datepicker( {
			dateFormat: $.datepicker._defaults.dateFormat,
            changeMonth: true,
            changeYear: true,
			minDate: '0',
			onSelect: function() {
				var dt = $( this ).datepicker( 'getDate' );
				dt.setDate( dt.getDate() + 1 );
				$( '#fee_settings_end_date' ).datepicker( 'option', 'minDate', dt );
			}
		} );
		$( '#fee_settings_end_date' ).datepicker( {
			dateFormat: $.datepicker._defaults.dateFormat,
            changeMonth: true,
            changeYear: true,
			minDate: '0',
			onSelect: function() {
				var dt = $( this ).datepicker( 'getDate' );
				dt.setDate( dt.getDate() - 1 );
				$( '#fee_settings_start_date' ).datepicker( 'option', 'maxDate', dt );
			}
		} );

        //Validation on time range
        $('#ds_time_from').change(function() {
            filterToTimes();
        });
        $('#ds_time_to').change(function() {
            filterFromTimes();
        });
        filterToTimes(); // Initialize state
        filterFromTimes(); // Initialize state

        $('ul.wc-tabs').show(),
        $('div.panel-wrap').each(function () {
            $(this).find('div.panel:not(:first)').hide();
        }),
        $('ul.wc-tabs a').click(function (e) {

            e.preventDefault();

            var tab = $(this).attr('href');
            var panel = $(this).closest('div.panel-wrap');

            $('ul.wc-tabs li', panel).removeClass('active');
            $(this).parent().addClass('active');
            $('div.panel', panel).hide();
            $(tab).show();
        });

        // Dynamic charges section toggle
        $(document).on( 'click', '.dsaefw-description-wrap p', function(){
	        $(this).toggleClass('dsaefw-hide');
	        $(this).next('.dsaefw-description').toggle();
	        if( $(this).hasClass('dsaefw-hide') ){
	            localStorage.setItem('dsaefw-dynamic-charge-display', 'hide');
	        } else {
	            localStorage.setItem('dsaefw-dynamic-charge-display', 'show');
	        }
	    });
        var show_dynamic_rules = localStorage.getItem('dsaefw-dynamic-charge-display');
	    if( ( null !== show_dynamic_rules || undefined !== show_dynamic_rules ) && ( 'hide' === show_dynamic_rules ) ) {
	        $('.dsaefw-description-wrap p').addClass('dsaefw-hide');
	        $('.dsaefw-description-wrap p + .dsaefw-description').css('display', 'none');
	    } else {
	        $('.dsaefw-description-wrap p').removeClass('dsaefw-hide');
	        $('.dsaefw-description-wrap p + .dsaefw-description').css('display', 'block');
	    }
    });

    function filterToTimes() {
        var ds_time_from = $('#ds_time_from').val();
        $('#ds_time_to option').each(function() {
            if ( $(this).val() <= ds_time_from && $(this).val() !== '' && ds_time_from !== '' ) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
    }

    function filterFromTimes() {
        var ds_time_to = $('#ds_time_to').val();
        $('#ds_time_from option').each(function() {
            if ($(this).val() >= ds_time_to && $(this).val() !== '' && ds_time_to !== '' ) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
    }

    // Global function created for select2
    window.init_select2 = function(){
        /**
         * Select2 dropdown for product selection
         */
        $('.ds-select').filter(':not(.enhanced)').each( function() {
            var ds_select2 = $(this);
            ds_select2.select2({
                placeholder: ds_select2.data( 'placeholder' ),
                allowClear: ds_select2.data( 'allow_clear' ) ? true : false,
            });
        });
    };

    // Global function created for select2 with AJAX search
    window.init_search_select2 = function(){

        /**
         * Select2 dropdown for product selection
         */
        $('.ds-woo-search').filter(':not(.enhanced)').each(function() {
            var ds_select2 = $(this);
            ds_select2.select2({
                placeholder: ds_select2.data( 'placeholder' ),
                allowClear: ds_select2.data( 'allow_clear' ) ? true : false,
                minimumInputLength: ds_select2.data( 'minimum_input_length' ) ? ds_select2.data( 'minimum_input_length' ) : '3',
                ajax: {
                    url: coditional_vars.ajax_url,
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: function(params) {
                        return {
                            search          : params.term,
                            action          : ds_select2.data( 'action' ) || 'dsaefw_json_search_products',
                            display_pid     : ds_select2.data( 'display_id' ) ? true : false,
                            security        : coditional_vars.dsaefw_woo_search_nonce,
                            posts_per_page  : coditional_vars.select2_per_data_ajax,
                            offset          : params.page || 1,
                            pa_data         : ds_select2.data( 'product_attribute' ) || '',
                        };
                    },
                    processResults: function( data ) {
                        var terms = [];
                        if ( data ) {
                            $.each( data, function( id, text ) {
                                terms.push( { id: id, text: text } );
                            });
                        }
                        var pagination = terms.length > 0 && terms.length >= coditional_vars.select2_per_data_ajax ? true : false;
                        return {
                            results: terms,
                            pagination: {
                                more : pagination
                            } 
                        };
                    }
                }
            }).addClass( 'enhanced' );
        });
    };

    // Global function for loader start
    window.dsaefw_loader_show = function( element ) {
        $(element).block({
            message: null,
            overlayCSS: {
                background: 'rgb(255, 255, 255)',
                opacity: 0.6,
            },
        });
    };

    // Global function for loader stop
    window.dsaefw_loader_hide = function ( element ) {
        $(element).unblock();
    };

})( jQuery );
