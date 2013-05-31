$(document).ready(function(){
    bootstrap_alert = function() {};
    bootstrap_alert.warning = function(message){
        $('#alertBar').html('<div class="alert">' + 
        '<a class="close" data-dismiss="alert">×</a><span>' + 
        message + '</span></div>');
    };
    /*
    $("[id^='lang-']").click(function(){
        <?php $(this).attr("id"); ?>
    });
    * */
});
