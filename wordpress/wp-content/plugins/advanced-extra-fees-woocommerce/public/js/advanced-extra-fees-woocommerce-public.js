(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
     * 
	 */
    $(document).ready(function () {
        
        $(document).on('change', 'input[name="payment_method"]', function () {
            $('body').trigger('update_checkout');
        });

        init_tooltip();
        
        /**
         * Block Compatiblility
         */  
        // On load block checkout, apply fee by default
        add_fee_data_in_seesion();

        // Apply fee on change state of payment method selection
        let previouslyChosenPaymentMethod = '';
        if( wp.data ) {
            wp.data.subscribe( function () {
                const chosenPaymentMethod =
                wp.data.select( wc.wcBlocksData.PAYMENT_STORE_KEY ).getActivePaymentMethod();
                if ( chosenPaymentMethod !== previouslyChosenPaymentMethod ) {
                    previouslyChosenPaymentMethod = chosenPaymentMethod;
                    add_fee_data_in_seesion();
                }
            }, wc.wcBlocksData.PAYMENT_STORE_KEY );
        }

        // Function to update the fee label
        updateFeeLabel();

        // Also run it whenever the DOM updates (e.g., when items are added/removed)
        const observer = new MutationObserver(updateFeeLabel);
        observer.observe(document.body, { childList: true, subtree: true });

    });

    /**
     * Method definations
     */
    function init_tooltip() {
        setTimeout( function(){ 
            $('.wc-dsaefw-help-tip').each(function () {
                return $(this).tipTip({ 
                    content: $(this).data('tip'),
                    keepAlive: true, 
                    edgeOffset: 2 
                });
            });
        }, 1000 );
    }

    function add_fee_data_in_seesion() {
        
        if ( !window?.wc?.blocksCheckout ) {
            return;
        }
        
        $('.wp-block-woocommerce-checkout').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
        
        const { select } = wp.data;
        const { PAYMENT_STORE_KEY } = window.wc.wcBlocksData;
        const chosenPaymentMethod = select( PAYMENT_STORE_KEY ).getActivePaymentMethod();

        wc.blocksCheckout.extensionCartUpdate({
            namespace: 'dotstore-advance-extra-checkout-data',
            data: {
                isCheckout: dasefw_public_vars.is_checkout ? true : false,
                payment_method: chosenPaymentMethod,
            },
        }).finally(() => {
            // Update mini cart fragment for updated cart data
            setTimeout(function() {
                $(document.body).trigger('wc_fragment_refresh');
            },300);
            $('.wp-block-woocommerce-checkout').unblock();
        });

        return;
    }

    function updateFeeLabel() {  
        $.each( dasefw_public_vars.fee_tooltip_data, function( fee_slug, fee_html ){
            if( $('.wc-block-components-totals-fees__'+fee_slug).length > 0 ) {
                var $valueElement = $('.wc-block-components-totals-fees__'+fee_slug).find('.wc-block-components-totals-item__value');
                if ($valueElement.length && $('.dsaefw-help-tip-'+fee_slug).length === 0) {
                    var $tooltip = $('<span class="wc-dsaefw-help-tip wc-block-components-tooltip dsaefw-help-tip-' + fee_slug + '" data-tip="' + fee_html + '"></span>');
                    $valueElement.after($tooltip);
                }
            }
        });
        init_tooltip();
    }

})( jQuery );


