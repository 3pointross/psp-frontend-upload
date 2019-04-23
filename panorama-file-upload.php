<?php
/**
 * Plugin Name: Project Panorama Frontend Upload
 * Plugin URI: https://github.com/3pointross/psp-frontend-upload
 * Description: Let your clients and project managers upload files from the front end
 * Version: 1.6.3.2
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
    'PSP_FILE_UPLOAD_VER'   =>  '1.6.3.2',
    'PSP_FILE_UPLOAD_DIR'   =>  plugins_url( '', __FILE__ ),
);

foreach( $constants as $constant => $val ) {
    if( !defined( $constant ) ) define( $constant, $val );
}

$deps = array(
    'assets',
    'views',
    'controller'
);

foreach( $deps as $dep ) {
    include( 'lib/' . $dep . '.php' );
}

add_action( 'init', 'psp_upload_add_user_permissions' );
function psp_upload_add_user_permissions() {

    // Only run once
    //

    if( get_option('psp_upload_set_user_permissions') == '2' ) {
        return;
    }

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

    update_option( 'psp_upload_set_user_permissions', '2' );

}

require 'vendor/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/3pointross/psp-frontend-upload/',
	__FILE__,
	'psp-frontend-upload'
);

//Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');

add_filter( 'psp_notification_feeds_repeater_args', 'psp_file_upload_notifications' );
function psp_file_upload_notifications( $repeater_args ) {

    $repeater_args['fields']['notification']['args']['options']['file_uploaded'] = __( '...a file is uploaded to a project', 'psp_projects' );

    return $repeater_args;
}

add_filter( 'psp_notifications_replacements_array', 'psp_file_upload_replacements', 10, 3 );
function psp_file_upload_replacements( $replacements, $notification_type, $args ) {

    if( $notification_type == 'file_uploaded' ) {

        $replacements['%username%']     = psp_get_nice_username_by_id( $args['user_id'] );
        $replacements['%file_name%']    = $args['file_name'];
        $replacements['%file_url%']     = $args['file_url'];
        $replacements['%file_desc%']    = $args['file_desc'];
        $replacements['%message%']      = $args['message'];
        $replacements['%project_title%'] = get_the_title( $args['project_id'] );

        $phase_title = '';

        if( !empty($args['phase']) ) {

            $phases = get_field( 'phases', $args['project_id'] );

            foreach( $phases as $phase ) {
                if( $phase['phase-comment-key'] == $args['document_phase'] ) {
                    $replacements['%phase_title%'] = $phase['title'];
                }
            }

        }

        $replacements['%phase_title%'] = '';

    }

    return $replacements;

}

function psp_custom_file_uploaded_notification() {

    $feeds = get_posts( array(
		'post_type'   => "psp-email-feed",
		'numberposts' => -1,
		'order'       => 'ASC',
	) );

	if ( ! empty( $feeds ) && ! is_wp_error( $feeds ) ) {
		foreach ( $feeds as $feed ) {

            $slug   = get_post_meta( $feed->ID, 'psp_email_feed_notification', true );
            $email  = get_post_meta( $feed->ID, 'psp_email_feed_recipient_email', true );

            if( $slug == 'file_uploaded' && $email == '%users%' ) {
                return true;
            }

		}

	}

    return false;

}
