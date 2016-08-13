// leanModal v1.1 by Ray Stone - http://finelysliced.com.au
// Dual licensed under the MIT and GPL

(function($){$.fn.extend({leanModal:function(options){var defaults={top:50,overlay:0.5,closeButton:null};var overlay=$("<div id='lean_overlay'></div>");$("body").append(overlay);options=$.extend(defaults,options);return this.each(function(){var o=options;$(this).click(function(e){var modal_id=$(this).attr("href");$("#lean_overlay").click(function(){close_modal(modal_id)});$(o.closeButton).click(function(){close_modal(modal_id)});var modal_height=$(modal_id).outerHeight();var modal_width=$(modal_id).outerWidth();
    $("#lean_overlay").css({"display":"block",opacity:0});$("#lean_overlay").fadeTo(200,o.overlay);$(modal_id).css({"display":"block","position":"fixed","opacity":0,"z-index":11000,"left":50+"%","margin-left":-(modal_width/2)+"px","top":o.top+"px"});$(modal_id).fadeTo(200,1);e.preventDefault()})});function close_modal(modal_id){$("#lean_overlay").fadeOut(200);$(modal_id).css({"display":"none"})}}})})(jQuery);


jQuery(document).ready(function($) {

    $('.psp-doc-upload-notify-checkbox').change(function() {

        if( $(this).prop('checked') ) {
            $('.psp-doc-upload-notify-fields').slideDown( 'slow' );
        } else {
            $('.psp-doc-upload-notify-fields').slideUp( 'slow' );
        }

    });

    function panoAlterFields() {

        var currentState = $('input[name=file-type]:checked').val();

        if (currentState == 'upload') {
            $('.psp-upload-file-field').show();
            $('.psp-web-address-field').hide();
        } else {
            $('.psp-web-address-field').show();
            $('.psp-upload-file-field').hide();
        }

    }

    panoAlterFields();
    $('.pano-modal-btn').leanModal({closeButton: ".modal_close"});
    $('.pano-modal-btn a').click(function() {
       console.log('fired');
    });

    $('input[name=file-type]').change(function() {

        panoAlterFields();

    });

    $('#pano-upload-form').validate({
        rules: {
            file_url: {
                required: '#file-type-web:checked',
                url: true
            },
            upload_attachment: {
                required: '#file-type-upload:checked'
            }
        }
    });

});
