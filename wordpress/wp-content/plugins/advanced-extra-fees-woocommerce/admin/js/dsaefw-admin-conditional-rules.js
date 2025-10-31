(function( $ ) {
	'use strict';

    $(document).ready(function() {

        //Init Woo seach select2 on load
        init_search_select2();

        //Init select2 on load
        init_select2();

        var count = $( '#total_row' ).val();
        // Add new field for
        $('body').on('click', '#dsaefw-add-rule', function () {
            
            // Add WC loader
            dsaefw_loader_show( $('#dsaefw-fee-conditional-rules .inside') );

			var dsaefw_cr_table = $('#dsaefw-rules tbody');
            // let count = $('#dsaefw-rules tbody tr').length - 1;

            // Create the new row (tr)
            var tr = $('<tr></tr>');
            tr.attr( 'id', 'row_' + count );
            tr.attr( 'valign', 'top');
			dsaefw_cr_table.append(tr);

            // generate td of 1st column condition
			var td1 = $('<td></td>');
            td1.attr( 'class', 'titledesc th_product_fees_conditions_condition' );
            td1.attr( 'scope', 'row');
			tr.append(td1);

            // Create select dropdown for condition
            var select1 = $('<select></select>');
            select1.attr( 'rel-id', count );
            select1.attr( 'id', 'product_fees_conditions_condition_' + count );
            select1.attr( 'name', 'fees[product_fees_conditions_condition][]' );
            select1.attr( 'class', 'product_fees_conditions_condition' );
            td1.append(select1);

            // Add options to the select dropdown
            $.each(dsaefw_acr_vars.dsaefw_filter_conditions, function( optgroup_key, optgroup_value ) {
                var optgroup = $('<optgroup></optgroup>');
                optgroup.attr( 'label', optgroup_key );
                $.each( optgroup_value, function( key, value ) {
                    var option = $('<option></option>');
                    option.val( key );
                    option.html(value).text(); // decode html entities like special characters
                    optgroup.append(option);
                });
                select1.append(optgroup);
            });
            select1.val('product').trigger('change');

            // generate td of 2nd column condition
			var td2 = $('<td></td>');
            td2.attr( 'class', 'select_condition_for_in_notin' );
			tr.append(td2);

            // Create select dropdown for condition
            var select2 = $('<select></select>');
            select2.attr( 'name', 'fees[product_fees_conditions_is][]' );
            select2.attr( 'class', 'product_fees_conditions_is_' + count );
            td2.append(select2);

            // Add options to the select dropdown (Default country is selected so we have to add it manually)
            $.each( dsaefw_acr_vars.dsaefw_filter_action.product, function( key, value ) {
                var option = $('<option></option>');
                option.val( key );
                option.html(value).text(); // decode html entities like special characters
                select2.append(option);
            });

            // generate td of 3rd column condition
			var td3 = $('<td></td>');
            td3.attr( 'id', 'column_' + count );
            td3.attr( 'class', 'condition-value' );
			tr.append(td3);

            var selection = $('<select></select>');
            selection.attr( 'id', 'product-filter-' + count );
            selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
            selection.attr( 'class', 'ds-woo-search' );
            selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_product_placeholder );
            selection.attr( 'data-allow_clear', 'true' );
            selection.attr( 'multiple', 'multiple' );
            selection.attr( 'data-width', '100%' );
            selection.attr( 'data-sortable', 'true' );
            td3.append(selection);

            //Init select2 for newly added HTML
            init_search_select2();

            // generate td of 4th column condition
			var td4 = $('<td></td>');
			tr.append(td4);

            // generate delete button for rule
            var delete_a = $('<a></a>');
            delete_a.attr( 'rel-id', count );
            delete_a.attr( 'class', 'dsaefw-delete-rule' );
            delete_a.attr( 'href', 'javascript:void(0);' );
            delete_a.attr( 'title', dsaefw_acr_vars.dsaefw_filter_delete_title );
            td4.append(delete_a);

            var deleteicon = $('<i/>');
			deleteicon.attr( 'class', 'dashicons dashicons-trash');
			delete_a.append(deleteicon);

            //No filter toggle based on rule add/remove
            if( dsaefw_cr_table.find('tr').length > 1 ) {
                $('tr.dsaefw-no-filter-tr').removeClass('dsaefw-no-filter-tr-show').addClass('dsaefw-no-filter-tr-hide');
            }
            count++;
            
            // Remove WC loader
            setTimeout(function() {
                dsaefw_loader_hide( $('#dsaefw-fee-conditional-rules .inside') );
            }, 200);
        });

        // Add condition value based on condition selection
        $(document).on('change', '.product_fees_conditions_condition', function() {
            var condition = $(this).val();
            var count = $(this).attr('rel-id');
            var selection, option;

            // Add options to the select dropdown
            if( dsaefw_acr_vars.dsaefw_filter_action[condition] ) {
                
                var select2 = $('.product_fees_conditions_is_' + count);
                select2.empty();

                $.each(dsaefw_acr_vars.dsaefw_filter_action[condition], function( key, value ) {
                    option = $('<option></option>');
                    option.val( key );
                    option.html(value).text(); // decode html entities like special characters
                    select2.append(option);
                });
            }

            $('#column_' + count).empty();
            var td = $('#dsaefw-fee-conditional-rules tbody tr td#column_' + count);
            
            if( 'product' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_product_placeholder );
                selection.attr( 'class', 'ds-woo-search' );
                
            } else if ( 'category' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_category_placeholder );
                selection.attr( 'data-action', 'dsaefw_json_search_categories' );
                selection.attr( 'class', 'ds-woo-search' );

            } else if( 'tag' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_tag_placeholder );
                selection.attr( 'data-action', 'dsaefw_json_search_tags' );
                selection.attr( 'class', 'ds-woo-search' );

            } else if( 'country' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_country_placeholder );
                selection.attr( 'class', 'ds-select' );

                $.each(dsaefw_acr_vars.dsaefw_country_data, function( key, value ) {
                    option = $('<option></option>');
                    option.val( key );
                    option.html(value).text(); // decode html entities like special characters
                    selection.append(option);
                });
            } else if( 'state' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_state_placeholder );
                selection.attr( 'class', 'ds-select' );

                $.each(dsaefw_acr_vars.dsaefw_country_data, function( country_code, country_name ) {
                    $.each(dsaefw_acr_vars.dsaefw_state_data[country_code], function( state_code, state_name ) {
                        option = $('<option></option>');
                        option.val( country_code + ':' + state_code );
                        option.html(country_name + ' -> ' + state_name ).text(); // decode html entities like special characters
                        selection.append(option);
                    });
                });
            } else if( 'city' === condition ) {
                selection = $('<textarea></textarea>');
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + ']' );
                selection.attr( 'placeholder', dsaefw_acr_vars.textarea_city_placeholder );
            } else if( 'postcode' === condition ) {
                selection = $('<textarea></textarea>');
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + ']' );
                selection.attr( 'placeholder', dsaefw_acr_vars.textarea_postcode_placeholder );
            } else if( 'zone' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_zone_placeholder );
                selection.attr( 'class', 'ds-select' );
                
                $.each(dsaefw_acr_vars.dsaefw_zone_data, function( zone_id, zone_name ) {
                    option = $('<option></option>');
                    option.val( zone_id );
                    option.html( zone_name ).text(); // decode html entities like special characters
                    selection.append(option);
                });
            } else if( 'product_qty' === condition ) {
                selection = $('<input/>');
                selection.attr( 'type', 'number' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'class', 'qty-class' );
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + ']' );
                selection.attr( 'placeholder', dsaefw_acr_vars.input_product_qty_placeholder );
            } else if( 'cart_total' === condition ) {
                selection = $('<input/>');
                selection.attr( 'type', 'number' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + ']' );
                selection.attr( 'placeholder', dsaefw_acr_vars.input_cart_total_placeholder );
                selection.attr( 'step', '0.01');
            } else if( 'cart_totalafter' === condition ) {
                selection = $('<input/>');
                selection.attr( 'type', 'number' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + ']' );
                selection.attr( 'placeholder', dsaefw_acr_vars.input_cart_totalafter_placeholder );
                selection.attr( 'step', '0.01');
            } else if( 'cart_productspecific' === condition ) {
                selection = $('<input/>');
                selection.attr( 'type', 'number' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + ']' );
                selection.attr( 'placeholder', dsaefw_acr_vars.input_cart_productspecific_placeholder );
                selection.attr( 'step', '0.01');
            } else if( 'quantity' === condition ) {
                selection = $('<input/>');
                selection.attr( 'type', 'number' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'class', 'qty-class' );
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + ']' );
                selection.attr( 'placeholder', dsaefw_acr_vars.input_quantity_placeholder );
            } else if( 'weight' === condition ) {
                selection = $('<input/>');
                selection.attr( 'type', 'number' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + ']' );
                selection.attr( 'placeholder', dsaefw_acr_vars.input_weight_placeholder );
                selection.attr( 'step', '0.01');
            } else if( 'coupon' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_coupon_placeholder );
                selection.attr( 'class', 'ds-select' );
                if( dsaefw_acr_vars.dsaefw_coupon_data ) {
                    option = $('<option></option>');
                    option.val( -1 ).html( dsaefw_acr_vars.dsaefw_all_coupon_title ).text(); // decode html entities like special characters
                    selection.append(option);
                    $.each(dsaefw_acr_vars.dsaefw_coupon_data, function( coupon_id, coupon_name ) {    
                        option = $('<option></option>');
                        option.val( coupon_id ).html( coupon_name ).text(); // decode html entities like special characters
                        selection.append(option);
                    });
                }
            } else if( 'shipping_class' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_shipping_class_placeholder );
                selection.attr( 'class', 'ds-select' );
                if( dsaefw_acr_vars.dsaefw_shipping_class_data ) {
                    $.each(dsaefw_acr_vars.dsaefw_shipping_class_data, function( shipping_class_id, shipping_class_name ) {    
                        option = $('<option></option>');
                        option.val( shipping_class_id ).html( shipping_class_name ).text(); // decode html entities like special characters
                        selection.append(option);
                    });
                }
            } else if( 'payment' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_payment_placeholder );
                selection.attr( 'class', 'ds-select' );
                if( dsaefw_acr_vars.dsaefw_payment_data ) {
                    $.each(dsaefw_acr_vars.dsaefw_payment_data, function( payment_id, payment_name ) {    
                        option = $('<option></option>');
                        option.val( payment_id ).html( payment_name ).text(); // decode html entities like special characters
                        selection.append(option);
                    });
                }
            } else if( 'shipping_method' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_shipping_method_placeholder );
                selection.attr( 'class', 'ds-select' );
                if( dsaefw_acr_vars.dsaefw_shipping_method_data ) {
                    $.each(dsaefw_acr_vars.dsaefw_shipping_method_data, function( shipping_method_id, shipping_method ) {
                        option = $('<option></option>');
                        option.val( shipping_method_id ).html( shipping_method.full_title ).text(); // decode html entities like special characters
                        selection.append(option);
                    });
                }
            } else if( 'user' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_user_placeholder );
                selection.attr( 'data-action', 'dsaefw_json_search_users' );
                selection.attr( 'data-display_id', 'true' );
                selection.attr( 'class', 'ds-woo-search' );

            } else if( 'user_role' === condition ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_user_role_placeholder );
                selection.attr( 'class', 'ds-select' );
                if( dsaefw_acr_vars.dsaefw_user_role_data ) {
                    $.each(dsaefw_acr_vars.dsaefw_user_role_data, function( user_role_id, user_role_name ) {    
                        option = $('<option></option>');
                        option.val( user_role_id ).html( user_role_name ).text(); // decode html entities like special characters
                        selection.append(option);
                    });
                }
            } else if( condition.indexOf('pa_') !== -1 ) {
                selection = $('<select></select>');
                selection.attr( 'name', 'fees[product_fees_conditions_values][value_' + count + '][]' );
                selection.attr( 'data-allow_clear', 'true' );
                selection.attr( 'multiple', 'multiple' );
                selection.attr( 'data-width', '100%' );
                selection.attr( 'data-sortable', 'true' );
                selection.attr( 'id', condition + '-filter-' + count );
                selection.attr( 'data-placeholder', dsaefw_acr_vars.select2_product_attribute_placeholder.replace('%s',dsaefw_acr_vars.dsaefw_pa_placeholder_labels[condition]) );
                selection.attr( 'class', 'ds-woo-search' );
                selection.attr( 'data-action', 'dsaefw_json_search_pa_terms' );
                selection.attr( 'data-product_attribute', condition );
            }

            td.append(selection);

            // For note section
            if( 'product_qty' === condition ) {
                note_template( selection, dsaefw_acr_vars.dsaefw_note_product_qty, dsaefw_acr_vars.dsaefw_note_product_qty_url, true );
            } else if( 'cart_totalafter' === condition ) {
                note_template( selection, dsaefw_acr_vars.dsaefw_note_cart_totalafter, dsaefw_acr_vars.dsaefw_note_cart_totalafter_url, true );
            } else if( 'cart_productspecific' === condition ) {
                note_template( selection, dsaefw_acr_vars.dsaefw_note_cart_productspecific, dsaefw_acr_vars.dsaefw_note_cart_productspecific_url, true );
            }

            //Init select2 on load
            init_select2();

            //Init select2 for newly added HTML
            init_search_select2();
        });

        // Delete filter rule
        $(document).on('click', '.dsaefw-delete-rule', function() {

            // Add WC loader
            dsaefw_loader_show( $('#dsaefw-fee-conditional-rules .inside') );

            var rel_id = $(this).attr('rel-id');
            var row_id = 'row_' + rel_id;

            $('#'+row_id).remove();
            
            if( $('#dsaefw-rules tr').length <= 1 ) {
                if( $('tr.dsaefw-no-filter-tr').hasClass('dsaefw-no-filter-tr-show') ){
                    $('tr.dsaefw-no-filter-tr').removeClass('dsaefw-no-filter-tr-show').addClass('dsaefw-no-filter-tr-hide');
                } else {
                    $('tr.dsaefw-no-filter-tr').removeClass('dsaefw-no-filter-tr-hide').addClass('dsaefw-no-filter-tr-show');
                
                }
            }

            // Remove WC loader
            setTimeout(function() {
                dsaefw_loader_hide( $('#dsaefw-fee-conditional-rules .inside') );
            }, 200);
        });
    });

    // Function for prepare note template
    var note_template = function( appendInto, msg, url, link = false ){
        var span = $('<span></span>');
        span.attr( 'class', 'dsaefw-condition-note' );

        var strong = $('<strong></strong>');
        strong.html( dsaefw_acr_vars.dsaefw_note_title + ': ');
        span.append(strong);

        span.append( msg );
        
        if( link ){
            var a = $('<a></a>');
            a.attr( 'href', url );
            a.attr( 'target', '_blank' );
            a.html( dsaefw_acr_vars.dsaefw_note_link_title );
            span.append(a);
        }
        appendInto.after(span);
    };

})( jQuery );