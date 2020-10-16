class ContextMenu{

    constructor() {}

    enable(){

        jQuery(document).ready( function () {

            jQuery("#contextmenu").addClass("is-fadingOut");

            jQuery('#contextmenu a').on('click',function(e) {
                var value = $(this).text();
                e.preventDefault();
                if( $(this).hasClass('ticked') ) {
                    $(this).addClass('unticked');
                    $(this).removeClass('ticked');
                    return false;
                } else if( $(this).hasClass('unticked') ) {
                    $(this).addClass('ticked');
                    $(this).removeClass('unticked');
                    return false;
                } else if( jQuery(this).hasClass("print") ){
                    window.print();
                } else {
                    var href = jQuery(this).attr("href");
                    location.href = href;
                    return false;
                }
            });

            // Context Menu Click
            jQuery(document).on("contextmenu", function(e) {
                var delay=0;
                if( $('#contextmenu').hasClass('is-fadingIn') ) delay=0;
                e.preventDefault();
                $('#contextmenu').removeClass('is-fadingIn');
                $('#contextmenu').addClass('is-fadingOut');

                setTimeout(function() {
                    $('#contextmenu').css('top',e.clientY);
                    $('#contextmenu').css('left',e.clientX);
                    $('#contextmenu').removeClass('is-fadingOut');
                    $('#contextmenu').addClass('is-fadingIn');
                },delay);

            });

            // hide Context-Menu
            jQuery(document).on("click", function (e) {
                jQuery("#contextmenu").addClass("is-fadingOut");
            });

        });

    }


}