jQuery(document).ready(function($){
    $("#relevanssi_sees_show").click(function(e) {
        $("#relevanssi_sees_button_container").hide();
        $("#relevanssi_sees_container").show();
        $("#relevanssi_sees_hide").show();
        $("#relevanssi_sees_show").hide();
    });

    $("#relevanssi_sees_hide").click(function(e) {
        $("#relevanssi_sees_button_container").show();
        $("#relevanssi_sees_container").hide();
        $("#relevanssi_sees_show").show();
        $("#relevanssi_sees_hide").hide();
	});
});