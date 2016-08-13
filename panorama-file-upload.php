<?php

/**
 * Plugin Name: Project Panorama Frontend Upload
 * Plugin URI: http://www.projectpanorama.com
 * Description: Let your clients and project managers upload files from the front end
 * Version: 1.4.1
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

define( 'PSP_FILE_UPLOAD_VER', '1.4.1' );

/**
 * Add the field and form to the page
 */
add_action( 'psp_after_documents', 'psp_add_upload_field', 99 );
function psp_add_upload_field() {

    if( is_user_logged_in() ) {

        global $post; ?>

        <p class="pano-add-file-btn"><a href="#pano-modal-upload" class="pano-modal-btn pano-btn pano-btn-primary"><?php _e('Add Document','psp_projects'); ?></a></p>

        <div class="pano-modal" id="pano-modal-upload">

            <div class="pano-modal-wrap">

                <h2><?php _e('Add Document','psp_projects'); ?></h2>

                <form id="pano-upload-form" action="<?php the_permalink(); ?>" method="post" class="form" enctype="multipart/form-data">

                    <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">

                    <?php wp_nonce_field( 'upload_attachment', 'my_image_upload_nonce' ); ?>

                    <ol class="psp-upload-form">
                        <li><label for="file-name"><?php _e( 'Title', 'psp_projects' ); ?></label> <input type="text" name="file-name" value="" id="file-name" required></li>
                        <li><label for="file-name"><?php _e( 'Description', 'psp_projects' ); ?></label> <input type="text" name="file-description" value="" id="file-description"></li>
                        <li><label for="file-type"><?php _e( 'File Type', 'psp_projects' ); ?></label> <input type="radio" value="upload" name="file-type" id="file-type-upload" checked> <?php _e('Upload','psp_projects'); ?> &nbsp;&nbsp; <input type="radio" name="file-type" id="file-type-web" value="web"> <?php _e('Web Address','psp_projects'); ?></li>
                        <li class="psp-web-address-field"><label for="file_url"><?php _e( 'Web Address', 'psp_projects' ); ?></label> <input type="text" name="file_url" value="http://"></li>
                        <li class="psp-upload-file-field"><label for="upload_attachment"><?php _e( 'Upload File', 'psp_projects' ); ?></label> <input type="file" name="upload_attachment" class="files" size="50"  /></li>

                        <?php if( psp_get_project_users() ) { ?>

                            <li><input type="checkbox" name="notify-users" value="yes" class="psp-doc-upload-notify-checkbox"> <label for="psp-doc-notify" class="psp-doc-upload-notify"><?php _e('Notify Users','psp_projects'); ?></label>

                                <ul class="psp-notify-list psp-doc-upload-notify-fields">
                                    <li class="all-line"><input type="checkbox" class="all-checkbox" name="psp-notify-all" value="all"> <?php _e( 'All Users', 'psp_projects' ); ?></li>
                            		<?php
            						$users = psp_get_project_users();

            						foreach( $users as $user ) {

            							$username = psp_get_nice_username( $user ); ?>

            							<li><input type="checkbox" name="psp-user[]" value="<?php echo $user["ID"]; ?>" class="psp-notify-user-box"><?php echo $username; ?></li>

            						<?php } ?>
                    		    </ul>
                            </li>
                   	 		<li class="psp-doc-upload-notify-fields"><label for="psp-doc-message"><?php _e('Message','psp_projects'); ?></label>
                                <textarea name="psp-doc-message"></textarea>
                            </li>

        				<?php } ?>
                    </ol>

                    <div class="pano-modal-doc-actions">
                        <p class="pano-modal-add-btn"><input type="submit" value="<?php _e( 'Add Document', 'psp_projects' ); ?>" class="pano-btn pano-btn-primary"> <a href="#" class="modal_close"><?php _e( 'Cancel', 'psp_projects' ); ?></a></p>
                    </div>

                </form>
            </div>
        </div>

<?php }

}

add_action( 'wp_head', 'psp_custom_template_fallback' );
function psp_custom_template_fallback() {

	$use_custom_template 	= get_option( 'psp_use_custom_template' );

	if ( !empty( $use_custom_template ) ) {

		psp_process_attach_file();

	}

}

add_action( 'psp_head', 'psp_process_attach_file' );
function psp_process_attach_file() {

    if( isset( $_POST[ 'post_id' ] ) ) {

        // Check to see if someone has uploaded

        $post_id    = $_POST['post_id'];
        $file_name  = $_POST['file-name'];
        $file_desc  = $_POST['file-description'];
        $field_key  = 'field_52a9e4634b147';
        $cuser      = wp_get_current_user();

        if ( $_POST[ 'file-type' ] == 'upload') {
            if ( $_POST[ 'file-name' ] ) {

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

            }

        } else {

            $attach_id  = NULL;
            $file_url   = $_POST['file_url'];

        }

        $value      = get_field($field_key, $post_id);
        $value[]    = array(
            'title'         => $file_name,
            'description'   => $file_desc,
            'status'        => 'In Review',
            'file'          => $attach_id,
            'url'           => $file_url
        );

        update_field( $field_key, $value, $post_id );

        /* Check and send notifications */

        if( ( isset( $_POST[ 'psp-user' ] ) ) && ( !empty( $_POST[ 'psp-user' ] ) ) ) {

            $users      = $_POST[ 'psp-user' ];
            $subject    = psp_username_by_id( $cuser->ID ) . " " . __( 'has posted a new file to ', 'psp_projects' ) . get_the_title( $post_id );

            $message    = "<h3 style='font-size: 18px; font-weight: normal; font-family: Arial, Helvetica, San-serif;'>" . get_the_title( $post_id ) . "</h3>";
            $message    .= "<p><strong>" . psp_username_by_id( $cuser->ID ) . " " . __( 'posted ', 'psp_projects') . "<a href='" . $file_url . "'>" . $file_name . "</a> " . __( 'to the project', 'psp_projects' ) . " <a href='" . get_the_permalink( $post_id ) . "'>" . get_the_title( $post_id ) . "</a></p>";
            $message    .= wpautop( $_POST[ 'psp-doc-message' ] );


            foreach( $users as $user ) {

                psp_send_email( $user, $subject, $message, $post_id );

            }

        }

    }
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
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url(); ?>/panorama-file-upload/assets/css/pano-upload.css">
    <script src="<?php echo plugins_url(); ?>/panorama-file-upload/assets/js/jquery.validation.min.js"></script>
    <script src="<?php echo plugins_url(); ?>/panorama-file-upload/assets/js/pano-upload.js"></script>
<?php
}

add_action( 'wp_enqueue_scripts', 'psp_front_upload_add_assets' );
function psp_front_upload_add_assets() {

	wp_register_style( 'psp-file-upload', plugins_url() . '/panorama-file-upload/assets/css/pano-upload.css', null, PSP_FILE_UPLOAD_VER );
	wp_register_script( 'psp-validate', plugins_url() . '/panorama-file-upload/assets/js/jquery.validation.min.js', array( 'jquery' ), PSP_FILE_UPLOAD_VER, false );
	wp_register_script( 'psp-file-upload', plugins_url() . '/panorama-file-upload/assets/js/pano-upload.js', array( 'jquery' ), PSP_FILE_UPLOAD_VER, false );

	if( get_post_type() == 'psp_projects' ) {

		wp_enqueue_style( 'psp-file-upload' );
		wp_enqueue_script( 'psp-validate' );
		wp_enqueue_script( 'psp-file-upload' );

	}

}

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://www.projectpanorama.com/updates/?action=get_metadata&slug=panorama-file-upload',
    __FILE__,
    'panorama-file-upload'
);
