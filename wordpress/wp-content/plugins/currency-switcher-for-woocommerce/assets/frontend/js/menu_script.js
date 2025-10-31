jQuery(document).ready(function ($) {
    $(document).on('click', 'li.wccs-click-for-menu', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).attr('id');
        
        // var code = $(this).find('a').attr('href');
        // code = code.substr(1);
        // alert(code);

        if ( id === '' || typeof id === "undefined" ) {
            var code = $(this).find('a').attr('href');
            code = code.substr(1);
        } else {
            var code = id.substr(id.length - 3);
        }
        
        
        if(code){
            $('<form>', {
                "id": "getInvoiceImage",
                "html": '<input type="hidden"name="wcc_switcher" value="' + code + '" />',
                "action": '',
                "method": 'POST'
            }).appendTo(document.body).submit();
        }
    });
});