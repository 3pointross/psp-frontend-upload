jQuery(document).ready(function($) {

    function psp_reset_upload_form() {

        $('.m-psp-file-upload').find('input[type="text"]').val('');
        $('.m-psp-file-upload').find('textarea').val('');
        $('.m-psp-file-upload').find('input[type="checkbox"]').prop( 'checked', false );
        $('.m-psp-file-upload').find('input[type="radio"]').prop( 'checked', false );
        $('#pano-upload-form').find('input[type="submit"]').prop( 'disabled', false ).removeClass('disabled');
        $('#pano-upload-form .psp-notify-list').find('.psp-notify-list').hide();

        $('#pano-upload-form #file-type-upload').prop( 'checked', true );

        $('#pano-upload-form .psp-web-address-field').hide();
        $('#pano-upload-form .psp-upload-file-field').show();
        $('#pano-modal-upload').removeClass('processing');

        $('.psp-task-documents .pano-btn').fadeIn('fast');

		$('body').removeClass('psp-modal-on');
        $('#psp-projects').removeClass('psp-has-modal');
        $('.psp-modal-wrap').fadeOut();

    }
    psp_reset_upload_form();

    // When the button is clicked, populate the location hidden field with the buttons key
    $('body').on('click', '.js-pano-upload-file', function(e) {

        var phase_id    = $(this).data('phase-id');

        $('.m-psp-file-upload input[name="phase_key"]').val( $(this).data('phase-key') );
        $('.m-psp-file-upload input[name="phase_id"]').val( phase_id );

        if( phase_id != 'global' ) {
            $('.m-psp-file-upload').attr( 'action', window.location + '%23phase-documents-' + phase_id++ );
        } else {
            $('.m-psp-file-upload').attr( 'action', window.location );
        }

    });

    $('body').on( 'click', '.js-pano-upload-file-inline', function(e) {

        e.preventDefault();
        $(this).parent().siblings('.m-psp-inline-upload').slideDown('fast');
        $(this).slideUp('fast');

    });

    $('body').on( 'click', '#task-panel-tabs .modal_close', function(e) {

        e.preventDefault();
        $(this).parents('.psp-task-documents').find('.m-psp-inline-upload').slideUp('fast');
        $(this).parents('.psp-task-documents').find('.pano-add-file-btn a').slideDown('fast');

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

        $('body').on( 'click', '.all-do-checkbox', function() {

            if( $(this).is(':checked') ) {
                $(this).parents('#pano-upload-form').find('.psp-doc-upload-notify-fields').slideDown('fast');
                $(this).parents('#pano-upload-form').find('input.specific-do-checkbox').prop('checked',false);
                $(this).parents('#pano-upload-form').find('.psp-notify-list input').prop('checked',true);
                $(this).parents('#pano-upload-form').find('.psp-notify-list').slideUp('fast');
            } else {
                $(this).parents('#pano-upload-form').find('.psp-notify-list input').prop('checked',false);
            }

        });

        $('body').on( 'click', '.specific-do-checkbox', function() {

            if( $(this).is(':checked') ) {
                $(this).parents('#pano-upload-form').find('.psp-doc-upload-notify-fields').slideDown('fast');
                $(this).parents('#pano-upload-form').find('.psp-notify-list').slideDown('fast');
                $(this).parents('#pano-upload-form').find('input.all-do-checkbox').prop('checked',false);
            } else {
                $(this).parents('#pano-upload-form').find('.psp-notify-list').slideUp('fast');
                $(this).parents('#pano-upload-form').find('.psp-notify-list input').prop('checked',false);
            }

        });

    }

    $(document).on( 'submit', '.m-pano-upload-form', function(e) {

        e.preventDefault();

        if( !$(this).valid() ) return false;

        $(this).find('input[name="psp-ajax"]').val(1);

        var ajaxurl     = $('#psp-ajax-url').val();
        var formdata    = new FormData( $(this)[0] );

        // Identify where this is being uploaded
        var is_task_panel = false;
        var is_phase      = false;

        var phase_key = $(this).find('#phase_key').val();
        var task_key  = $(this).find('#task_key').val();

        if( task_key ) {
            is_task_panel = true;
        }

        if( phase_key ) {
            is_phase = true;
        }

        $(this).find('input[type="submit"]').prop( 'disabled', true ).addClass('disabled');

	   $('#pano-modal-upload').find('.psp-upload-loading').show();

        $.ajax({
            url: ajaxurl + '?action=psp_process_attach_file',
            type: 'post',
            data: formdata,
            processData: false,
            contentType: false,
            success: function( response ) {

                if( is_task_panel ) {
                    target = $( response.data.results.target.panel );
                    task_target = $( response.data.results.target.task );
                    phase_target = $( response.data.results.target.phase );
                } else {
                    target = $( response.data.results.target );
                }
                /*
                if( phase_id == 'global' ) {
                    target = $('#psp-documents');
                } else {
                    target = $('#phase-'+phase_id);
                } */

                $(target).find('.phase-docs-empty-message').hide();
                $(target).find('.psp-documents-row').prepend( response.data.results.markup );

                if( is_task_panel ) {
                    $('.m-psp-inline-upload').slideUp();
                } else {
                    $('.m-psp-file-upload').fadeOut('slow');
                }

                if( typeof $.fn.leanModal !== "undefined" ) {
                     $( target ).find('.doc-status').leanModal({ closeButton: "." });
                } else {
                     console.log('ohs no!');
                }

                psp_reset_upload_form();

				$('.psp-upload-loading').hide();

                if( is_task_panel ) {
                    psp_update_task_document_stats( target, task_target, response.data.results.counts.task, response.data.results.counts.phase_tasks );
                    psp_update_phase_documents_stats( phase_target, response.data.results.counts.phase, response.data.results.counts.phase_tasks );
                } else if( is_phase ) {
                    psp_update_phase_documents_stats( target, response.data.results.counts.phase, response.data.results.counts.phase_tasks );
                }

                psp_update_document_stats( response.data.results.counts.total );

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

    $('body').on( 'change', '.psp-doc-upload-notify-checkbox', function() {

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

    if( typeof $.fn.leanModal !== "undefined" ) {
         $('.js-pano-upload-file').leanModal({ closeButton: ".modal_close" });
    }

    $('body').on( 'change', '.psp-upload-field input', function() {
        panoAlterFields();
    });

    $('#pano-upload-form').validate({
        rules: {
            file_url: {
                required: '#file-type-web:checked',
            },
            upload_attachment: {
                required: '#file-type-upload:checked'
            }
        }
    });

	function psp_update_phase_documents_stats( target, phase, tasks ) {

        var total_phase_documents = phase.total + tasks.total;

        $( target ).find('.psp-phase-document-count').text( total_phase_documents );

        $(target).find('.psp-doc-approved').show();
        $(target).find('.psp-doc-empty').hide();

        $(target).find('.doc-approved-count').text( phase.approved );
        $(target).find('.doc-total-count').text( phase.total );

	}

    function psp_update_task_document_stats( target, task_target, count, phase_count ) {

        // Update in the task panel
        $('#task-panel-tabs').find('#documents-count').text( count.total );

        // Add the HTML if it doesn't currently exist
        if( !$('body').hasClass('psp-dashboard-tasks-page') && !$(task_target).siblings('.after-task-name').find('.psp-task-documents').length ) {

            if( !$(task_target).siblings('.after-task-name').length ) {
                $(task_target).append('b.after-task-name');
            }

            var phase_id = $(task_target).data('phase_id');
            var task_id  = $(task_target).data('task_id');

            var doc_count_html = '<b class="psp-task-documents js-open-task-panel" data-target="phase-' + phase_id + '-task-' + task_id + '"><i class="fa fa-files-o"></i> <span class="text"></span></b>';

            $(task_target).siblings('.after-task-name').append( doc_count_html );

        }

        if( !$('body').hasClass('psp-dashboard-tasks-page') ) {

            // Update
            $( task_target ).siblings('.after-task-name').find('.psp-task-documents span.text').text( count.total );

            $( task_target ).parents('.psp-task-list-wrapper').find('.task-doc-count').html( '<i class="fa fa-files-o"></i> ' + phase_count.approved + '/' + phase_count.total );

        } else {

            $( task_target ).find('.psp-task-documents span.text').text( count.total );

        }

    }

    function psp_update_document_stats( count ) {

        $('#psp-stat-documents').find('.psp-h3').html( '<span>' + count.approved + '</span>/' + count.total );

        allSummaryCharts.documents.segments[0].value = count.total;
        allSummaryCharts.documents.segments[0].value = count.approved;
        allSummaryCharts.documents.update();

    }

});
