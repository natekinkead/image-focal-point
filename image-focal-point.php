<?php
/*
Plugin Name: Image Focal Point for Elementor
Description: Allows setting a focal point on images in the media gallery and integrates with Elementor.
Version: 1.0
Author: Nathan Kinkead
*/

// Add focal point fields to media attachment
add_filter('attachment_fields_to_edit', 'add_focal_point_field', 10, 2);
function add_focal_point_field($fields, $post) {
    $focal_point = get_post_meta($post->ID, 'focal_point', true);
    // $focal_point_y = get_post_meta($post->ID, '_focal_point_y', true);

    // Add fields to the media form
    $fields['focal_point_picker'] = array(
        'label' => 'Focal Point',
        'input' => 'html',
        'html' => "
            <div class='focal-point-picker' style='position: relative; display: inline-block;'>
                <img src='" . esc_url(wp_get_attachment_url($post->ID)) . "' class='focal-point-image' style='max-width:100%;'/>
                <div class='focal-point-marker' style='position: absolute; width: 10px; height: 10px; background: red; border-radius: 50%; transform: translate(-50%, -50%);'></div>
            </div>
        ",
        'helps' => 'Click on the image to set the focal point.'
    );
    $fields['focal_point'] = array(
        'label' => __('Focal Point Value', 'your-text-domain'),
        'input' => 'text',
        'value' => $focal_point ?: '50x50',
        'show_in_edit' => true,
        'extra_rows'   => [
          'nonce' => wp_nonce_field(
            'update_attachment_focal_point', // Action.
            'nonce_attachment_focal_point', // Nonce name.
            true, // Output referer?
            false // Echo?
          ),
        ],
    );

    return $fields;
}

add_action( 'wp_ajax_save-attachment-compat', 'focal_point_media_fields_ajax', 0, 1 );
function focal_point_media_fields_ajax() {
    $nonce = $_REQUEST['nonce_attachment_focal_point'] ?? false;
    // Bail if the nonce check fails.
    if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'update_attachment_focal_point' ) ) {
        return;
    }

    // Bail if the post ID is empty.
    $post_id = intval( $_POST['id'] );
    if ( empty( $post_id ) ) {
        return;
    }
    // Update the post.
    $meta = $_POST['attachments'][ $post_id ]['focal_point'] ?? '';
    $meta = wp_kses_post( $meta );
    update_post_meta( $post_id, 'focal_point', $meta );
    clean_post_cache( $post_id );
}

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', 'enqueue_focal_point_scripts');
add_action('admin_enqueue_scripts', 'enqueue_focal_point_scripts');

function enqueue_focal_point_scripts() {
    wp_enqueue_script('focal-point-js', plugin_dir_url(__FILE__) . 'focal-point.js', array('jquery'), '1.0', true);
    wp_enqueue_style('focal-point-css', plugin_dir_url(__FILE__) . 'focal-point.css', array(), '1.0');
}

// Hook into Elementor for background position
add_filter('elementor/frontend/widget/before_render', 'apply_focal_point_to_elementor');
add_action('elementor/frontend/container/before_render', 'apply_focal_point_to_elementor');

function apply_focal_point_to_elementor($element) {
    $settings = $element->get_settings_for_display();
    if (isset($settings['image']['id'])) {
        $attachment_id = $settings['image']['id'];
        $position_type = 'object';
        $size_type = 'fit';
    } else if (isset($settings['background_image']['id'])) {
        $attachment_id = $settings['background_image']['id'];
        $position_type = 'background';
        $size_type = 'size';
    } else {
        return;
    }
    // Retrieve the focal point from the attachment meta
    $focal_point = get_post_meta($attachment_id, 'focal_point', true);
    if (empty($focal_point)) return;
    $focal_point_exploded = explode('x', $focal_point);
    // $thisAttributes = $element->get_render_attributes();
    $style = '';
    // Position
    if (
        (
            $position_type === 'background' && 
            (
                !isset($settings['background_position']) || 
                empty($settings['background_position']) ||
                $settings['background_position'] === 'center center'
            )
        ) ||
        (
            $position_type === 'object' &&
            (
                !isset($settings['object-position']) ||
                empty($settings['object-position']) ||
                $settings['object-position'] === 'center center'
            )
        )
    ) {
        $style .= sprintf(
            '%s-position: %s%% %s%%; ',
            $position_type,
            esc_attr($focal_point_exploded[0]),
            esc_attr($focal_point_exploded[1]),
        );
    }
    // Size
    if (
        (
            $position_type === 'background' &&
            (
                !isset($settings['background_size']) ||
                empty($settings['background_size']) ||
                $settings['background_size'] === 'cover'
            )
        ) ||
        (
            $position_type === 'object' &&
            (
                !isset($settings['object-fit']) ||
                empty($settings['object-fit']) ||
                $settings['object-fit'] === 'cover'
            )
        )
    ) {
        $style .= sprintf(
            '%s-%s: cover; ',
            $position_type,
            $size_type,
        );
    }
    $element->add_render_attribute('_wrapper', 'style', $style);
    $element->add_render_attribute('_wrapper', 'class', 'image-focal-point');
}

