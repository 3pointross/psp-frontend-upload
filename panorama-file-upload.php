<?php
/**
 * Plugin Name: Project Panorama Frontend Upload
 * Plugin URI: https://github.com/3pointross/psp-frontend-upload
 * Description: Let your clients and project managers upload files from the front end
 * Version: 1.5.3
 * Author: SnapOrbital
 * Author URI: http://www.projectpanorama.com
 * License: GPL2
 * Text Domain: psp_projects
 */

/**
 * Initialize the plugin and load textdomain for localization
 */
add_action('plugins_loaded', 'psp_upload_localize_init');
function psp_upload_localize_init() {
    load_plugin_textdomain( 'psp_projects', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

$constants = array(
    'PSP_FILE_UPLOAD_VER'   =>  '1.5.4',
    'PSP_FILE_UPLOAD_DIR'   =>  plugins_url( '', __FILE__ ),
);

foreach( $constants as $constant => $val ) {
    if( !defined( $constant ) ) define( $constant, $val );
}

/**
 * Add the field and form to the page
 */
add_action( 'psp_after_documents', 'psp_add_upload_field', 99 );
function psp_add_upload_field() {

    if( !is_user_logged_in() || !current_user_can( 'psp_upload_documents' ) ) return;

    echo psp_file_upload_button();

}

add_action( 'psp_inside_phase_doc_wrapper_after', 'psp_add_phase_upload_field', 10, 3 );
function psp_add_phase_upload_field( $post_id, $phase_id, $phase_key ) {

if( !is_user_logged_in() || !current_user_can( 'psp_upload_documents' ) ) return;

    echo psp_file_upload_button( $phase_key, $phase_id + 1 );
}

function psp_file_upload_button( $phase_key = 'global', $phase_id = 'global' ) {

    return '<p class="pano-add-file-btn"><a href="#pano-modal-upload" class="pano-modal-btn pano-btn pano-btn-primary js-pano-upload-file" data-phase-key="' . esc_attr($phase_key) . '" data-phase-id="' . esc_attr($phase_id) . '">'. __( 'Add Document', 'psp_projects' ) . '</a></p>';

}

add_action( 'psp_footer', 'psp_add_upload_modal', 99 );
function psp_add_upload_modal() {

    global $post; ?>

    <div class="psp-modal" id="pano-modal-upload">
		
		<div class="psp-upload-loading">
			<img src="<?php echo esc_url( PSP_FILE_UPLOAD_DIR . '/assets/img/loading.gif'); ?>" alt="Loading" class="psp-fu-loading-image">
		</div>

        <h2><?php esc_html_e( 'Add Document', 'psp_projects' ); ?></h2>

        <form id="pano-upload-form" action="<?php the_permalink(); ?>" method="post" class="form" enctype="multipart/form-data">

            <input type="hidden" name="post_id" value="<?php esc_attr_e($post->ID); ?>">
            <input type="hidden" name="phase_key" value="global">
            <input type="hidden" name="phase_id" value="global">

            <?php wp_nonce_field( 'upload_attachment', 'my_image_upload_nonce' ); ?>

            <p>
                <label for="file-name"><?php esc_html_e( 'Title', 'psp_projects' ); ?></label>
                <input type="text" name="file-name" value="" id="file-name" required>
            </p>

            <p>
                <label for="file-name"><?php esc_html_e( 'Description', 'psp_projects' ); ?></label>
                <input type="text" name="file-description" value="" id="file-description">
            </p>

            <p>
                <label for="file-type"><?php esc_html_e( 'File Type', 'psp_projects' ); ?></label>
                <input type="radio" value="upload" name="file-type" id="file-type-upload" checked> <?php esc_html_e('Upload','psp_projects'); ?> &nbsp;&nbsp; <input type="radio" name="file-type" id="file-type-web" value="web"> <?php esc_html_e('Web Address','psp_projects'); ?>
            </p>


            <p class="psp-web-address-field">
                <label for="file_url"><?php esc_html_e( 'Web Address', 'psp_projects' ); ?></label>
                <input type="text" name="file_url" value="http://">
            </p>

            <p class="psp-upload-file-field">
                <label for="upload_attachment"><?php esc_html_e( 'Upload File', 'psp_projects' ); ?></label>
                <input type="file" name="upload_attachment" class="files" size="50"  />
            </p>

            <?php if( psp_get_project_users() ): ?>

                <p><strong><?php esc_html_e( 'Notify', 'psp_projects' ); ?></strong></p>

                <p class="all-upload-line">
                    <label for="psp-do-all">
                        <input type="checkbox" class="all-do-checkbox" id="psp-do-all" name="psp-do-all" value="yes" class="psp-doc-upload-notify-checkbox" id="psp-do-all">
                        <?php esc_html_e( 'All Users', 'psp_projects' ); ?>
                    </label>
                    <label for="psp-do-specific">
                        <input type="checkbox" class="specific-do-checkbox" id="psp-do-specific" name="psp-do-specific" value="specific">
                        <?php esc_html_e( 'Specific Users', 'psp_projects' ); ?>
                    </label>
                </p>

                <ul class="psp-notify-list">
            		<?php
					$users = psp_get_project_users();
                    $i = 0;
					foreach( $users as $user ):
						$username = psp_get_nice_username( $user ); ?>
						<li><label for="psp-upload-user-<?php echo esc_attr($i); ?>"><input type="checkbox" name="psp-user[]" value="<?php echo esc_attr($user['ID']); ?>" class="psp-notify-user-box" id="psp-upload-user-<?php echo esc_attr($i); ?>"><?php echo esc_html($username); ?></label></li>
					<?php $i++; endforeach; ?>
    		    </ul>

       	 		<p class="psp-doc-upload-notify-fields">
                    <label for="psp-doc-message"><?php _e('Message','psp_projects'); ?></label>
                    <textarea name="psp-doc-message"></textarea>
                </p>
			<?php endif; ?>

            <div class="psp-modal-actions">
                <p class="pano-modal-add-btn"><input type="submit" value="<?php esc_html_e( 'Add Document', 'psp_projects' ); ?>" class="pano-btn pano-btn-primary"> <a href="#" class="modal_close"><?php _e( 'Cancel', 'psp_projects' ); ?></a></p>
            </div>

        </form>
    </div>

<?php

}

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

    $post_id    = $_POST['post_id'];
    $file_name  = $_POST['file-name'];
    $file_desc  = $_POST['file-description'];
    $field_key  = 'field_52a9e4634b147';
    $cuser      = wp_get_current_user();
    $phase_key  = ( $_POST['phase_key'] == 'global' ? '' : $_POST['phase_key'] );

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
    $new_file      = array(
        array(
            'title'           => $file_name,
            'description'     => $file_desc,
            'status'          => 'In Review',
            'file'            => $attach_id,
            'url'             => $file_url,
            'document_phase'  => $phase_key,
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

    if( isset( $_POST[ 'psp-user' ] ) && !empty( $_POST[ 'psp-user' ] ) ) {

        $users      = $_POST[ 'psp-user' ];
        $subject    = psp_username_by_id( $cuser->ID ) . " " . __( 'has posted a new file to ', 'psp_projects' ) . get_the_title( $post_id );

        $message    = "<h3 style='font-size: 18px; font-weight: normal; font-family: Arial, Helvetica, San-serif;'>" . get_the_title( $post_id ) . "</h3>";
        $message    .= "<p><strong>" . psp_username_by_id( $cuser->ID ) . " " . __( 'posted ', 'psp_projects') . "<a href='" . $file_url . "'>" . $file_name . "</a> " . __( 'to the project', 'psp_projects' ) . " <a href='" . get_the_permalink( $post_id ) . "'>" . get_the_title( $post_id ) . "</a></p>";
        $message    .= wpautop( $_POST[ 'psp-doc-message' ] );

            foreach( $users as $user ) psp_send_progress_email( $user, $subject, $message, $post_id );

    }

    if( isset($_POST['psp-ajax']) ) {

        $response = array(
            'counts'    =>  psp_count_documents( $post_id ),
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

function psp_get_single_document_markup( $post_id, $index ) {

    /**
     * Need to return the whole list?
     * @var [type]
     */

     ob_start();

    $docs = get_field( 'documents', $post_id );

    $doc = end($docs);
    $doc['index'] = count($docs) - 1;

    include( psp_template_hierarchy('projects/phases/documents/single.php') );

    return ob_get_clean();

}

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

add_action('psp_head','panorama_add_assets');
function panorama_add_assets() { ?>
    <link rel="stylesheet" type="text/css" href="<?php echo PSP_FILE_UPLOAD_DIR; ?>/assets/css/pano-upload.css">
    <script src="<?php echo PSP_FILE_UPLOAD_DIR; ?>/assets/js/jquery.validation.min.js"></script>
    <script src="<?php echo PSP_FILE_UPLOAD_DIR; ?>/assets/js/pano-upload.js"></script>
<?php
}

add_action( 'wp_enqueue_scripts', 'psp_front_upload_add_assets' );
function psp_front_upload_add_assets() {

	wp_register_style( 'psp-file-upload', PSP_FILE_UPLOAD_DIR . '/assets/css/pano-upload.css', null, PSP_FILE_UPLOAD_VER );
	wp_register_script( 'psp-validate', PSP_FILE_UPLOAD_DIR . '/assets/js/jquery.validation.min.js', array( 'jquery' ), PSP_FILE_UPLOAD_VER, false );
	wp_register_script( 'psp-file-upload', PSP_FILE_UPLOAD_DIR . '/assets/js/pano-upload.js', array( 'jquery' ), PSP_FILE_UPLOAD_VER, false );

	if( get_post_type() == 'psp_projects' && is_single() ) {

		wp_enqueue_style( 'psp-file-upload' );
		wp_enqueue_script( 'psp-validate' );
		wp_enqueue_script( 'psp-file-upload' );

	}

}

add_action( 'init', 'psp_upload_add_user_permissions' );
function psp_upload_add_user_permissions() {

    $roles = array(
        'psp_project_owner',
        'psp_project_manager',
        'administrator',
        'editor',
        'subscriber',
        'psp_project_creator'
    );

    foreach( $roles as $role ) {
        $role_object = get_role($role);
        if( !empty( $role_object ) ) $role_object->add_cap( 'psp_upload_documents' );
    }

}

require 'vendor/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/3pointross/psp-frontend-upload/',
	__FILE__,
	'psp-frontend-upload'
);

//Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');