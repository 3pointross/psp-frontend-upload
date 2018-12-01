<?php

add_action( 'wp_head', 'psp_custom_template_fallback' );
function psp_custom_template_fallback() {
	$use_custom_template = get_option( 'psp_use_custom_template' );
	if ( !empty( $use_custom_template ) ) psp_process_attach_file();
}

add_action( 'wp_ajax_psp_process_attach_file', 'psp_process_attach_file' );
add_action( 'wp_ajax_nopriv_psp_process_attach_file', 'psp_process_attach_file' );
add_action( 'psp_head', 'psp_process_attach_file' );
function psp_process_attach_file() {

    if( !isset($_POST['file-name']) ) {

        if( isset($_POST['psp-ajax']) ) {
            wp_send_json_error();
            exit();
        }
        return;

    }

    $phase_id   = $_POST['phase_id'];
    $post_id    = $_POST['post_id'];
    $file_name  = $_POST['file-name'];
    $file_desc  = $_POST['file-description'];
    $field_key  = 'field_52a9e4634b147';
    $cuser      = wp_get_current_user();
    $phase_key  = ( $_POST['phase_key'] == 'global' ? '' : $_POST['phase_key'] );
    $task_key   = ( isset( $_POST['task_key'] ) ? $_POST['task_key'] : false );
    $message    = ( isset( $_POST['message'] ) ? $_POST['message'] : false );
	$no_approval = ( isset($_POST['no-approval']) && $_POST['no-approval'] == 'yes' ? true : false );

    if ( $_POST[ 'file-type' ] == 'upload') {

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment = $_FILES['upload_attachment'];
        $files      = $_FILES['upload_attachment'];
        $newFiles   = array();

        $file = array(
            'name'      => $files['name'],
            'type'      => $files['type'],
            'tmp_name'  => $files['tmp_name'],
            'error'     => $files['error'],
            'size'      => $files['size']
        );

        $upload_overrides   = array('test_form' => false);
        $upload             = wp_handle_upload($file, $upload_overrides);

        // $filename should be the path to a file in the upload directory.
        $filename       = $upload['file'];

        // The ID of the post this attachment is for.
        $parent_post_id = $post_id;

        // Check the type of tile. We'll use this as the 'post_mime_type'.
        $filetype       = wp_check_filetype(basename($filename), null);

        // Get the path to the upload directory.
        $wp_upload_dir  = wp_upload_dir();

        // Prepare an array of post data for the attachment.
        $attachment     = array(
            'guid'              => $wp_upload_dir['url'] . '/' . basename( $filename ),
            'post_mime_type'    => $filetype['type'],
            'post_title'        => preg_replace('/\.[^.]+$/', '', basename( $filename ) ),
            'post_content'      => '',
            'post_status'       => 'inherit'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        $file_url = NULL;

        } else {

            $attach_id  = NULL;
            $file_url   = $_POST['file_url'];

    }

    $old_files     = get_field( $field_key, $post_id );
    $status        = apply_filters( 'psp_feu_new_file_status', 'In Review' );

	if( $no_approval ) {
		$status = 'none';
	}

    $new_file      = array(
        array(
            'title'           => $file_name,
            'description'     => $file_desc,
            'status'          => $status,
            'file'            => $attach_id,
            'url'             => $file_url,
            'document_phase'  => $phase_key,
            'document_task'   => $task_key,
    ) );

	if( $old_files ) {
		/* This project already has documents */
		$all_files = array_merge( $old_files, $new_file );
    	update_field( $field_key, $all_files, $post_id );
	    $new_file['index'] = count($old_files) - 1;
	} else {
		/* This project doesn't have documents, we need to create a row
		LEFT OFF: update_field doesn't work if you don't have a field to being with. How can we fix this? Not sure. */
    	update_field( $field_key, $new_file, $post_id );
	    $new_file['index'] = 0;

	}
    // Create an index for when we generate the markup

    /* Check and send notifications */

	$notification_args = array(
	        'project_id'    =>  $post_id,
			'post_id'		=>  $post_id,
	        'phase'         =>  $phase_key,
	        'user_id'       =>  $cuser->ID,
	        'task_key'      =>  $task_key,
	        'file_name'     =>  $file_name,
	        'file_url'      =>  $file_url,
	        'file_desc'     =>  $file_desc,
	        'message'       =>  $message,
	);

	if( isset($_POST['psp-user']) ) {

		$notification_args['user_ids'] = $_POST['psp-user'];

	} else {

		$users = psp_get_project_users( $post_id );

		$user_ids = array();

		foreach( $users as $user ) {

			if( psp_task_is_private( $task_key, $post_id ) || psp_phase_is_private( $phase_key, $post_id ) ) {

				if( !user_can( $user['ID'], 'edit_psp_projects') ) {
					continue;
				}

			}

			$user_ids[] = $user['ID'];
		}


		$notification_args['user_ids'] = $user_ids;

	}

    do_action( 'psp_notify', 'file_uploaded', $notification_args );

    $custom_notification = psp_custom_file_uploaded_notification();

    if( !$custom_notification ) {

        if( isset($_POST['psp-user']) && !empty($_POST['psp-user']) ) {

            $users      = $_POST['psp-user'];
            $subject    = psp_username_by_id( $cuser->ID ) . " " . __( 'has posted a new file to ', 'psp_projects' ) . get_the_title( $post_id );

            $message    = "<h3 style='font-size: 18px; font-weight: normal; font-family: Arial, Helvetica, San-serif;'>" . get_the_title( $post_id ) . "</h3>";
            $message    .= "<p><strong>" . psp_username_by_id($cuser->ID) . " " . __( 'posted ', 'psp_projects') . "<a href='" . $file_url . "'>" . $file_name . "</a> " . __( 'to the project', 'psp_projects' ) . " <a href='" . get_the_permalink( $post_id ) . "'>" . get_the_title( $post_id ) . "</a></p>";
            $message    .= wpautop( $_POST['psp-doc-message'] );

            foreach( $users as $user ) {
                psp_send_progress_email( $user, $subject, $message, $post_id );
            }
        }

    }

    if( isset($_POST['psp-ajax']) ) {

        $type = 'global';

        if( !empty($task_key) ) {

			$parent_phase = psp_get_phase_by_task( $task_key, $post_id );
			$parent_phase['index']++;

			$type   = 'task';
            $target = array(
				'panel'	=> '.task-panel-tabs-content',
				'task'	=> 'a[data-task_id="' . $task_key . '"]',
				'phase'	=> '#phase-' . $parent_phase['index'],
				'phase_key' => $parent_phase['phase_id']
			);

			$phase_key = $parent_phase['phase_id'];

        } elseif( empty($phase_key) ) {
            $type   = 'phase';
            $target = '#psp-documents';
        } else {
            $target = '#phase-' . $phase_id;
        }

        $response = array(
            'type'      =>  $type,
            'target'    =>  $target,
            'counts'    =>  array(
				'total' => psp_count_documents( $post_id ),
				'task'	=> psp_count_documents( $post_id, array( 'task' => $task_key ) ),
				'phase'	=> psp_count_documents( $post_id, array( 'phase' => $phase_key ) ),
				'phase_tasks' => psp_count_documents( $post_id, array( 'phase_tasks' => $phase_key ) ),
			),
            'markup'    =>  psp_get_single_document_markup( $post_id, $new_file['index'] )
        );

        wp_send_json_success( array( 'success' => true, 'results' => $response ) );

        exit();

    } else {

        $target = 'psp-project-'.$post_id.'-doc-'.$new_file['index']; ?>

        <script>
            jQuery(document).ready(function($) {

                $('#<?php echo esc_js($target); ?>').parents('.psp-phase-documents-wrapper').show();
                window.location.hash = '<?php echo esc_js($target); ?>';
                $('#<?php echo esc_js($target); ?>').parents('.psp-phase-documents').find('.doc-list-toggle').click();

            });
        </script>

    <?php }

}

/**
 * Processes the upload
 *
 * @param  [array]  $uploads [description]
 * @param  integer $post_id [description]
 * @return [array]           [description]
 */
function psp_attach_uploads( $uploads, $post_id = 0 ) {
$files = rearrange( $uploads );

    if( $files[ 0 ][ 'name' ]=='' ){
        return false;
    }

    foreach( $files as $file ){
        $upload_file = wp_handle_upload( $file, array('test_form' => false) );
        $attachment = array(
            'post_mime_type' => $upload_file['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload_file['file'])),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $upload_file['file'], $post_id );
        $attach_array[] = $attach_id;
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload_file['file'] );
        wp_update_attachment_metadata( $attach_id, $attach_data );
    }
    return $attach_array;
}
