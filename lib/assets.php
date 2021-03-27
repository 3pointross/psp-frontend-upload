<?php
add_action('psp_head','panorama_add_assets');
function panorama_add_assets() {

     if( version_compare( PSP_VER, '2.0' ) <= 0 ) {  ?>
          <link rel="stylesheet" type="text/css" href="<?php echo PSP_FILE_UPLOAD_DIR; ?>/assets/css/pano-upload.css?ver=<?php echo PSP_FILE_UPLOAD_VER; ?>">
          <script src="<?php echo PSP_FILE_UPLOAD_DIR; ?>/assets/js/jquery.validation.min.js?ver=<?php echo PSP_FILE_UPLOAD_VER; ?>"></script>
          <script src="<?php echo PSP_FILE_UPLOAD_DIR; ?>/assets/js/pano-upload.js?ver=<?php echo PSP_FILE_UPLOAD_VER; ?>"></script>
    <?php }

}

add_action( 'wp_enqueue_scripts', 'psp_front_upload_add_assets', 9999999, 1 );
function psp_front_upload_add_assets() {

     wp_register_style( 'psp-file-upload', PSP_FILE_UPLOAD_DIR . '/assets/css/pano-upload.css', null, PSP_FILE_UPLOAD_VER );
	wp_register_script( 'psp-validate', PSP_FILE_UPLOAD_DIR . '/assets/js/jquery.validation.min.js', array( 'jquery' ), PSP_FILE_UPLOAD_VER, false );
	wp_register_script( 'psp-file-upload', PSP_FILE_UPLOAD_DIR . '/assets/js/pano-upload.js', array( 'jquery' ), PSP_FILE_UPLOAD_VER, false );

     global $post;

	if( get_post_type() == 'psp_projects' || ( isset($post->post_content) && has_shortcode($post->post_content, 'project_status') ) ) {
          wp_enqueue_style('psp-file-upload');
          wp_enqueue_script('psp-validate');
          wp_enqueue_script('psp-file-upload');
	}

}
