(function( $ ) {
	'use strict';

	/**
	 * All of the code for admin-facing Import Export JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 */

    $(document).ready(function(){

        // Export AJAX Call
        $('#dsaefw_export_fees').click(function(e) {
            e.preventDefault();
            var action = $('input[name="dsaefw_action"]').val();
            var security = $('input[name="dsaefw_export_fees_action_nonce"]').val();
            var $this = $(this);
            if( action && security ){
                $this.css( 'pointer-events', 'none' );
                $.ajax({
                    type: 'POST',
                    url: dasefw_import_export_vars.ajax_url,
                    data: {
                        'action': action,
                        'security': security
                    },
                    success: function( response ) {
                        var div_wrap = $('<div></div>').addClass('notice');
                        var p_text = $('<p></p>').text(response.data.message);
                        if( response.success ){
                            div_wrap.addClass('notice-success');
                        } else {
                            div_wrap.addClass('notice-error');
                        }
                        div_wrap.append(p_text);
                        $(div_wrap).insertAfter($('#wp__notice-list'));

                        //download link generation
                        if( response.data.download_path ){
                            var link = document.createElement('a');
                            link.href = response.data.download_path;
                            link.download = '';
                            document.body.appendChild(link);
                            link.click();
                        }
                        $this.removeClass( 'is-busy' );
                        $this.css( 'pointer-events', 'auto' );
                        setTimeout(function(){
                            div_wrap.fadeOut();
                            link.remove();
                        }, 2000);
                    }
                });
            }
        });

        // Import AJAX Call
        $(document).on( 'click', '#dsaefw_import_fees', function(e){
        // $('#dsaefw_import_fees').click(function(e){
            e.preventDefault();

            var action = $('input[name="dsaefw_action"]').val();
            var security = $('input[name="dsaefw_import_fees_action_nonce"]').val();
            var $this = $(this);

            // Check if a file has been selected
            var fileInput = $('input[name="dsaefw_import_fees_file"]')[0];
            if (fileInput.files.length === 0) {

                $this.removeClass( 'is-busy' );

                var div_wrap = $('<div></div>').addClass('notice');
                var p_text = $('<p></p>').text(dasefw_import_export_vars.file_upload_msg);
                div_wrap.addClass('notice-error');
                div_wrap.append(p_text);
                $(div_wrap).insertAfter($('#wp__notice-list'));

                setTimeout( function(){
                    div_wrap.fadeOut();
                }, 3000 );

                return false;
            }

            if( action && security ) {
                $this.css( 'pointer-events', 'none' );
                var fd = new FormData();
                fd.append('import_file', fileInput.files[0]);  
                fd.append('action', action);
                fd.append('security', security);
                $.ajax({
                    type: 'POST',
                    url: dasefw_import_export_vars.ajax_url,
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function( response ){
                        
                        var div_wrap = $('<div></div>').addClass('notice');
                        var p_text = $('<p></p>').text(response.data.message);
                        if(response.success){
                            div_wrap.addClass('notice-success');
                        } else {
                            div_wrap.addClass('notice-error');
                            $this.css( 'pointer-events', 'auto' );
                        }
                        div_wrap.append(p_text);
                        $(div_wrap).insertAfter($('#wp__notice-list'));

                        $('input[name="dsaefw_import_fees_file"]').val('');
                        $this.removeClass( 'is-busy' );
                        $this.css( 'pointer-events', 'auto' );

                        setTimeout( function(){
                            div_wrap.fadeOut();
                        }, 3000 );
                    }
                });
            }
        });
    });
})( jQuery );