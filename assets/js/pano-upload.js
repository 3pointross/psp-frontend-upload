jQuery(document).ready(function($) {

    function psp_reset_upload_form() {

        $('#pano-modal-upload').find('input[type="text"]').val('');
        $('#pano-modal-upload').find('textarea').val('');
        $('#pano-modal-upload').find('input[type="checkbox"]').prop( 'checked', false );
        $('#pano-modal-upload').find('input[type="radio"]').prop( 'checked', false );
        $('#pano-upload-form').find('input[type="submit"]').prop( 'disabled', false ).removeClass('disabled');
        $('#pano-upload-form .psp-notify-list').find('.psp-notify-list').hide();
		
		$('body').removeClass('psp-modal-on');

    }
    psp_reset_upload_form();

    // When the button is clicked, populate the location hidden field with the buttons key
    $('.js-pano-upload-file').click(function(e) {

        var phase_id    = $(this).data('phase-id');

        $('#pano-modal-upload input[name="phase_key"]').val( $(this).data('phase-key') );
        $('#pano-modal-upload input[name="phase_id"]').val( phase_id );

        if( phase_id != 'global' ) {
            $('#pano-modal-upload').attr( 'action', window.location + '%23phase-documents-' + phase_id++ );
        } else {
            $('#pano-modal-upload').attr( 'action', window.location );
        }

    });

    if( window.location.hash.indexOf('phase-documents-') ) {

		var target = window.location.hash;
		$(target).find('.doc-list-toggle').click(function() {
			$('html,body').animate({
				scrollTop: $(target).offset().top
			}, 1000 );
		});

	}

    if($('.all-upload-line').length) {

    $('.all-do-checkbox').click(function() {

        if( $(this).is(':checked') ) {
            $(this).parents('#pano-upload-form').find('.psp-doc-upload-notify-fields').slideDown('fast');
            $(this).parents('#pano-upload-form').find('input.specific-do-checkbox').prop('checked',false);
            $(this).parents('#pano-upload-form').find('ul.psp-notify-list input').prop('checked',true);
            $(this).parents('#pano-upload-form').find('ul.psp-notify-list').slideUp('fast');
        } else {
            $(this).parents('#pano-upload-form').find('ul.psp-notify-list input').prop('checked',false);
        }

    });

    $('.specific-do-checkbox').click(function() {

        if( $(this).is(':checked') ) {
            $(this).parents('#pano-upload-form').find('.psp-doc-upload-notify-fields').slideDown('fast');
            $(this).parents('#pano-upload-form').find('ul.psp-notify-list').slideDown('fast');
            $(this).parents('#pano-upload-form').find('input.all-do-checkbox').prop('checked',false);
        } else {
            $(this).parents('#pano-upload-form').find('ul.psp-notify-list').slideUp('fast');
            $(this).parents('#pano-upload-form').find('ul.psp-notify-list input').prop('checked',false);
        }

    });

    $('#pano-upload-form').append( '<input type="hidden" name="psp-ajax" value="1">' );

    $('#pano-upload-form').submit(function(e) {

        e.preventDefault();

        var ajaxurl     = $('#psp-ajax-url').val();
        var formdata    = new FormData( $(this)[0] );
        var phase_id    = $('#pano-upload-form').find('input[name="phase_id"]').val();
        var phase_key   = $('#pano-upload-form').find('input[name="phase_key"]').val();

        $('#pano-upload-form').find('input[type="submit"]').prop( 'disabled', true ).addClass('disabled');
		
		$('.psp-upload-loading').show();

        $.ajax({
            url: ajaxurl + '?action=psp_process_attach_file',
            type: 'post',
            data: formdata,
            processData: false,
            contentType: false,
            success: function( response ) {

                if( phase_id == 'global' ) {
                    target = $('#psp-documents');
                } else {
                    target = $('#phase-'+phase_id);
                }

                $(target).find('.phase-docs-empty-message').hide();
                $(target).find('.psp-documents-row').prepend( response.data.results.markup );
                $('#pano-modal-upload').fadeOut('slow');

                $( target ).find('.doc-status').leanModal({ closeButton: "." });

                psp_reset_upload_form();
				
				$('.psp-upload-loading').hide();

                if( phase_id != 'global' ) {
                    psp_update_phase_documents_stats( target );
                }

                // TODO: Still need to update the counts


            }
        });

    });
	
    function psp_upload_count_phase_documents( target ) {

        var total = 0;
        var approved = 0;

        $(target).find('.psp-document').each(function() {

            total++;

            if( $(this).data('status') == 'approved' || $(this).data('status') == '' || $(this).data('status') == 'none' ) {
                approved++;
            }

        });

        $(target).find('.doc-approved-count').html(approved);
        $(target).find('.doc-total-count').html(total);

    }


}

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
	
	function psp_update_phase_documents_stats() {
		
	}

});
