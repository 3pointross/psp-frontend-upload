<?php
/*
 * Button to global documents area
 */
add_action( 'psp_after_documents', 'psp_add_upload_field', 99 );
function psp_add_upload_field() {

    // Gate, can the user login or do they have permission to upload documents?
    if( !is_user_logged_in() || !current_user_can( 'psp_upload_documents' ) ) return;

    echo psp_file_upload_button();

}



/*
 * Button to phase documents area
 */
add_action( 'psp_inside_phase_doc_wrapper_after', 'psp_add_phase_upload_field', 10, 3 );
function psp_add_phase_upload_field( $post_id, $phase_id, $phase_key ) {

    // Gate, can the user login or do they have permission to upload documents?
    if( !is_user_logged_in() || !current_user_can( 'psp_upload_documents' ) ) return;

    echo psp_file_upload_button( $phase_key, $phase_id + 1 );

}

/*
 * Depricated?
 */
function psp_file_upload_button( $phase_key = 'global', $phase_id = 'global' ) {

    return '<p class="pano-add-file-btn"><a href="#pano-modal-upload" class="pano-btn pano-btn-primary js-pano-upload-file" data-phase-key="' . esc_attr($phase_key) . '" data-phase-id="' . esc_attr($phase_id) . '">'. __( 'Add Document', 'psp_projects' ) . '</a></p>';

}

/*
 * Button to tasks document area
 */
add_action( 'psp_after_task_doc_list', 'psp_task_upload_button', 10, 3 );
function psp_task_upload_button( $post_id = null, $task_index = null, $task_id = null ) {

    // Gate, can the user login or do they have permission to upload documents?
    if( !is_user_logged_in() || !current_user_can( 'psp_upload_documents' ) ) return;

    echo '<p class="pano-add-file-btn"><a href="#pano-modal-upload" class="pano-btn pano-btn-primary js-pano-upload-file-inline" data-task-key="' . esc_attr($task_id) . '" data-post-id="' . esc_attr($post_id) . '">'. __( 'Add Document', 'psp_projects' ) . '</a></p>'; ?>
    <div class="psp-hide m-psp-inline-upload">
        <?php do_action( 'psp_task_file_upload', $post_id, $task_index, $task_id ); ?>
    </div>
    <?php
}


add_action( 'wp_footer', 'psp_add_upload_modal_footer' );
function psp_add_upload_modal_footer() {

    wp_reset_postdata();

    global $post;

    if( ( isset($post->post_content) && has_shortcode( $post->post_content, 'project_status' ) ) || is_singular('psp_projects') ) {
        echo '<div id="psp-projects">';
            psp_add_upload_modal();
        echo '</div>';
    }

}

/*
 * The document upload form
 */
add_action( 'psp_task_file_upload', 'psp_add_upload_modal', 99, 3 );
add_action( 'psp_footer', 'psp_add_upload_modal', 99 );
function psp_add_upload_modal( $post_id = null, $task_index = null, $task_id = null) {

    if( !isset($post_id) || empty($post_id) ) {
        $post_id = get_the_ID();
    }

    $id      = 'pano-modal-upload' . ( isset($task_id) ? '-' . $id : '' );
    $class   = 'm-psp-file-upload ' . ( isset($task_id) ? 'psp-task-doc-upload' : 'psp-modal' ); ?>

    <div class="<?php echo esc_attr($class); ?>" id="<?php echo esc_attr($id); ?>">

		<div class="psp-upload-loading">
			<img src="<?php echo esc_url( PSP_FILE_UPLOAD_DIR . '/assets/img/loading.gif'); ?>" alt="Loading" class="psp-fu-loading-image">
		</div>

          <a class="modal_close psp-modal-x" href="#"><i class="fa fa-close"></i></a>

          <div class="psp-modal-header">
               <div class="psp-h2"><?php esc_html_e( 'Add Document', 'psp_projects' ); ?></div>
          </div>

        <form id="pano-upload-form" class="m-pano-upload-form psp-form-fields" action="<?php the_permalink(); ?>" method="post" autocomplete="false" class="form" enctype="multipart/form-data">

            <?php
            $fields = psp_upload_get_fields( $post_id, $task_id );

            if( isset($task_id) && $task_id ) {
                $fields[] = array(
                    'id'    => 'task_key',
                    'type'  => 'hidden',
                    'name'  => 'task_key',
                    'value' =>  $task_id
                );
            }

            $fields = apply_filters( 'psp_fe_upload_fields', $fields, $post_id );

            wp_nonce_field( 'upload_attachment', 'my_image_upload_nonce' );

            foreach( $fields as $field ) {
                call_user_func( 'psp_upload_field_' . $field['type'], $field );
            } ?>

            <?php if( psp_get_project_users($post_id) ): ?>

                 <div class="psp-form-field">

                      <label><?php esc_html_e( 'Notify', 'psp_projects' ); ?></label>

                     <div class="all-upload-line">
                         <label for="psp-do-all">
                             <input type="checkbox" class="all-do-checkbox" id="psp-do-all" name="psp-do-all" value="yes" class="psp-doc-upload-notify-checkbox" id="psp-do-all">
                             <?php esc_html_e( 'All Users', 'psp_projects' ); ?>
                         </label>
                         <label for="psp-do-specific">
                             <input type="checkbox" class="specific-do-checkbox" id="psp-do-specific" name="psp-do-specific" value="specific">
                             <?php esc_html_e( 'Specific Users', 'psp_projects' ); ?>
                         </label>
                    </div>

                     <div class="psp-notify-list">
                 		<?php
		               $users = psp_get_project_users($post_id);
                         $i  = 0;
     				foreach( $users as $user ): $username = psp_get_nice_username( $user ); ?>
     						<div class="psp-notify-user"><label for="psp-upload-user-<?php echo esc_attr($i); ?>"><input type="checkbox" name="psp-user[]" value="<?php echo esc_attr($user['ID']); ?>" class="psp-notify-user-box" id="psp-upload-user-<?php echo esc_attr($i); ?>"><?php echo esc_html($username); ?></label></div>
					<?php $i++; endforeach; ?>
     		    </div>
              </div>

              <?php
              $textarea_id = 'psp-upload-doc-message' . ( isset($task_id) ? '-task' : '' ); ?>

  	 		<div class=" psp-form-field psp-doc-upload-notify-fields">
                    <label for="psp-doc-message"><?php _e('Message','psp_projects'); ?></label>
                    <textarea name="psp-doc-message" id="<?php echo esc_attr($textarea_id); ?>"></textarea>
               </div>

			<?php endif; ?>

            <div class="psp-modal-actions">
                <div class="pano-modal-add-btn"><input type="submit" value="<?php esc_html_e( 'Add Document', 'psp_projects' ); ?>" class="pano-btn pano-btn-primary"> <a href="#" class="modal_close"><?php _e( 'Cancel', 'psp_projects' ); ?></a></div>
            </div>

        </form>
    </div>

<?php

}

/*
 * This is used to return markup in JS for dynamic insertion of doc
 */
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

function psp_upload_field_text( $field ) {

    $value      = ( isset($field['value']) ? $field['value'] : '' );
    $required   = ( isset($field['required']) ? 'required' : '' );
    $class      = 'psp-upload-field psp-form-field ' . ( isset($field['class']) ? $field['class'] : '' ); ?>

    <div class="<?php echo esc_attr($class); ?>">
        <label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['label']); ?></label>
        <input type="text" name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr($value); ?>" id="<?php echo esc_attr($field['id']); ?>" <?php echo esc_attr($required); ?>>
   </div>

    <?php

}

function psp_upload_field_hidden( $field ) { ?>

    <input type="hidden" name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr($field['value']); ?>" id="<?php echo esc_attr($field['id']); ?>">

    <?php
}

function psp_upload_field_file( $field ) {

    $required = isset($field['required']) ? 'required' : '';
    $class = 'psp-upload-field psp-form-field psp-upload-field-file ' . ( isset($field['class']) ? $field['class'] : '' ); ?>

    <div class="<?php echo esc_attr($class); ?>">
        <label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['label']); ?></label>
        <input type="file" name="<?php echo esc_attr($field['name']); ?>" class="files" size="50" id="<?php echo esc_attr($field['id']); ?>" <?php echo esc_attr($required); ?>>
   </div>

    <?php

}

function psp_upload_field_radio( $field ) {

    $required = isset($field['required']) ? 'required' : '';
    $class = 'psp-upload-field psp-form-field psp-upload-field-radio ' . ( isset($field['class']) ? $field['class'] : '' ); ?>

    <div class="<?php echo esc_attr($class); ?>">
        <label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['label']); ?></label>
        <?php
        foreach( $field['options'] as $option ) { ?>
            <label for="<?php echo esc_attr($option['id']); ?>"><input type="radio" name="<?php echo esc_attr($option['name']); ?>" id="<?php echo esc_attr($option['id']); ?>" value="<?php echo esc_attr($option['value']); ?>"> <?php echo esc_html($option['label']); ?></label>
        <?php } ?>
   </div>

    <?php

}

function psp_upload_field_checkbox( $field ) {

    $required = isset($field['required']) ? 'required' : '';

    $class = 'psp-upload-field psp-form-field psp-upload-field-checkbox ' . ( isset($field['class']) ? $field['class'] : '' ); ?>

    <div class="<?php echo esc_attr($class); ?>">

        <?php if( isset($field['label']) && !empty($field['label'] ) ): ?>
            <label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['label']); ?></label>
        <?php
        endif;
        foreach( $field['options'] as $option ) { ?>
            <label for="<?php echo esc_attr($option['id']); ?>"><input type="checkbox" name="<?php echo esc_attr($option['name']); ?>" id="<?php echo esc_attr($option['id']); ?>" value="<?php echo esc_attr($option['value']); ?>"> <?php echo esc_html($option['label']); ?></label>
        <?php } ?>
   </div>


    <?php

}

function psp_upload_get_fields( $post_id = null ) {

    if( !isset($post_id) || empty($post_id) ) {
        $post_id = get_the_ID();
    }

    $fields = apply_filters( 'psp_file_upload_fields', array(
        array(
            'id'    => 'post_id',
            'type'  => 'hidden',
            'name'  =>  'post_id',
            'value' => $post_id
        ),
        array(
            'id'    => 'psp_ajax',
            'type'  => 'hidden',
            'name'  => 'psp-ajax',
            'value' => '0'
        ),
        array(
            'id'    => 'phase_key',
            'type'  => 'hidden',
            'name'  => 'phase_key',
            'value' => 'global'
        ),
        array(
            'id'    => 'phase_id',
            'type'  => 'hidden',
            'name'  => 'phase_id',
            'value' => 'global'
        ),
        array(
            'id'    => 'file-name',
            'type'  => 'text',
            'required'  =>  true,
            'name'  =>  'file-name',
            'label' =>  __( 'Title', 'psp_projects' )
        ),
        array(
            'id'    => 'file-description',
            'type'  => 'text',
            'name'  =>  'file-description',
            'label' =>  __( 'Description', 'psp_projects' )
        ),
        array(
            'id'    =>  'no-approval',
            'type'  =>  'checkbox',
            'name'  =>  'no-approval',
            'class' =>  'psp-needs-approval-field',
            'options'   =>  array(
                array(
                    'id'    =>  'psp-doc-no-approval',
                    'value' =>  'yes',
                    'label' =>  __( 'No Approval Needed', 'psp_projects' ),
                    'name'  =>  'no-approval'
                )
            )
        ),
        array(
            'id'        => 'file-type',
            'type'      => 'radio',
            'required'  =>  true,
            'name'      =>  'file-type',
            'label'     =>  __( 'File Type', 'psp_projects' ),
            'options'   =>  array(
                array(
                    'id'    =>  'file-type-upload',
                    'value' =>  'upload',
                    'label' =>  __( 'Upload', 'psp_projects' ),
                    'name'  =>  'file-type'
                ),
                array(
                    'id'    =>  'file-type-web',
                    'value' =>  'web',
                    'label' =>  __( 'Web Address', 'psp_projects' ),
                    'name'  =>  'file-type'
                )
            )
        ),
        array(
            'class' => 'psp-web-address-field',
            'id'    => 'file_url',
            'type'  => 'text',
            'value' =>  'https://',
            'name'  =>  'file_url',
            'label' =>  __( 'Web Address', 'psp_projects' )
        ),
        array(
            'class' =>  'psp-upload-file-field',
            'id'    => 'upload_attachment',
            'type'  => 'file',
            'name'  =>  'upload_attachment',
            'label' =>  __( 'Upload File', 'psp_projects' )
        ),
    ) );

    return $fields;

}
