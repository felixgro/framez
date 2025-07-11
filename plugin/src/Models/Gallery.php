<?php

namespace FrameZ\Models;


use FrameZ\Utils\Path;

class Gallery
{
    /**
     * Register the custom post type for galleries.
     */
    public function register()
    {
        add_action('init', [$this, 'registerPostType']);

        // Add a metabox to the gallery post type
        add_action('add_meta_boxes', function () {
            add_meta_box(
                'framez_gallery_settings',
                __('Settings', 'framez'),
                [$this, 'renderMetaBoxSettings'],
                'framez-gallery',
                'normal',
                'high'
            );

            // add preview metabox
            add_meta_box(
                'framez_gallery_preview',
                __('Gallery', 'framez'),
                [$this, 'renderMetaBoxPreview'],
                'framez-gallery',
                'normal',
                'default'
            );
        });

        // Save the gallery settings when the post is saved
        add_action('save_post_framez-gallery', [$this, 'saveGallerySettings'], 10, 2);

        // Display validation errors on top of the post editor
        add_action('admin_notices', [$this, 'displayValidationErrors']);
        // Display warnings
        add_action('admin_notices', function () {
            $warning = get_transient('framez_gallery_warning');
            if ($warning) {
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p>' . $warning . '</p>';
                echo '</div>';
                // Delete the transient after displaying the warning
                delete_transient('framez_gallery_warning');
            }
        });

        // Genmerate the unique key from the title if not set
        add_action('save_post_framez-gallery', function ($postId) {
            // Check if the post is being updated
            if (!array_key_exists('framez', $_POST) || get_post_type($postId) !== 'framez-gallery') {
                return;
            }

            $key = $_POST['framez']['key'] ?? '';
            if (empty($key)) {
                $title = get_the_title($postId);
                $key = sanitize_title($title);
            }

            $existingKeys = get_posts([
                'post_type' => 'framez-gallery',
                'meta_key' => 'framez[key]',
                'meta_value' => $key,
                'fields' => 'ids',
                'exclude' => [$postId], // Exclude the current post
            ]);

            // If the key already exists, append a number to make it unique
            $filterKeys = array_keys(apply_filters('framez_galleries', $existingKeys));
            array_push($existingKeys, ...$filterKeys);
            if (in_array($key, $existingKeys)) {
                $key = $key . '-' . uniqid();
            }

            if (!empty($_POST['framez']['key']) && $key !== $_POST['framez']['key']) {
                set_transient('framez_gallery_warning', sprintf(__('The gallery key <b>“%s”</b> was already in use, so it has been renamed to <b>“%s”</b> to prevent duplicates.', 'framez'), $_POST['framez']['key'], $key), 30);
            } else if (empty($_POST['framez']['key'])) {
                set_transient('framez_gallery_warning', sprintf(__('Autogenerated gallery key: <b>“%s”</b>', 'framez'), $key), 30);
            }

            if ($key !== $_POST['framez']['key']) {
                $_POST['framez']['key'] = $key; // Update the $_POST array to reflect the new key for validation
            }
        }, 9, 1);

        // Render the frontend assets 
    }

    public function metaData()
    {
        return [
            'key' => [
                'label' => __('Unique Gallery Key', 'framez'),
                'type' => 'text',
                'default' => '',
                'required' => 1,
                'description' => __('Unique key for the gallery. Gets autogenerated from title when empty.', 'framez'),
            ],
            'type' => [
                'label' => __('Gallery Type', 'framez'),
                'type' => 'select',
                'default' => 'directory',
                'required' => 1,
                'options' => [
                    '' => __('Select Gallery Type', 'framez'),
                    'directory' => __('Directory', 'framez'),
                    'media' => __('Media Library', 'framez'),
                    'custom' => __('Custom', 'framez'),
                ],
            ],
            'directory_path' => [
                'label' => __('Directory Path', 'framez'),
                'type' => 'text',
                'default' => '',
                'required' => 0,
                'prefix' => fn() => Path::abspath(),
                'description' => __('Directory path on the server containing the images.', 'framez'),
            ],
            'directory_url' => [
                'label' => __('Directory URL', 'framez'),
                'type' => 'text',
                'default' => '',
                'prefix' => fn() => site_url() . '/',
                'required' => 0,
                'description' => __('URL to the directory containing the images.', 'framez'),
            ],
            'image_choice' => [
                'type' => 'hidden',
                'default' => ''
            ]
        ];
    }

    /**
     * Save the gallery settings when the post is saved.
     *
     * @param int $postId The post ID.
     * @param WP_Post $post The post object.
     */
    public function saveGallerySettings($postId)
    {
        // Check if our nonce is set.
        if (!isset($_POST['framez_gallery_settings_nonce']) || !wp_verify_nonce($_POST['framez_gallery_settings_nonce'], 'framez_gallery_settings_nonce')) {
            return;
        }

        // Check if the user has permission to save the post.
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Check if the framez data is set
        if (isset($_POST['framez'])) {

            // Trim all values in the framez array
            $_POST['framez'] = array_map('trim', $_POST['framez']);

            // Validate the meta data before saving
            $validationRes = $this->validateMetaData($_POST['framez']);

            if (!$validationRes['success']) {
                // Store the errors in a transient for displaying later
                set_transient('framez_gallery_validation_errors', $validationRes['errors'], 30);
                // prevent infinite loop
                remove_action('save_post_framez-gallery', [$this, 'saveGallerySettings'], 10);
                // make the post a draft
                wp_update_post([
                    'ID' => $postId,
                    'post_status' => 'draft',
                ]);
                add_action('save_post_framez-gallery', [$this, 'saveGallerySettings'], 10, 2);
            }

            foreach ($_POST['framez'] as $key => $value) {
                // Sanitize the value before saving
                if (is_array($value)) {
                    $value = array_map('sanitize_text_field', $value);
                } else {
                    $value = sanitize_text_field($value);
                }

                update_post_meta($postId, 'framez[' . $key . ']', sanitize_text_field($value));
            }
        }
    }

    /**
     * Validate the meta data before saving.
     *
     * @param array $data The data to validate.
     * @throws \Exception If validation fails.
     */
    public function validateMetaData(array $data): array
    {
        $metaData = $this->metaData();
        $validationResult = [
            'success' => true,
            'errors' => [],
        ];

        foreach ($metaData as $key => $field) {
            if (isset($field['required']) && $field['required'] && empty($data[$key])) {
                $validationResult['success'] = false;
                $validationResult['errors'][$key] = sprintf(__('The field %s is required.', 'framez'), $field['label']);
            }

            if ($field['type'] === 'text' && isset($data[$key]) && !is_string($data[$key])) {
                $validationResult['success'] = false;
                $validationResult['errors'][$key] = sprintf(__('The field <b>%s</b> must be a string.', 'framez'), $field['label']);
            }

            if ($field['type'] === 'select' && isset($data[$key]) && !array_key_exists($data[$key], $field['options'])) {
                $validationResult['success'] = false;
                $validationResult['errors'][$key] = sprintf(__('The field %s must be one of the predefined options.', 'framez'), $field['label']);
            }
        }

        return $validationResult;
    }

    /**
     * Display validation errors on top of the post editor.
     */
    public function displayValidationErrors()
    {
        $errors = get_transient('framez_gallery_validation_errors');
        if ($errors) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<h3>' . __('Something needs your attention before publishing.', 'framez') . '</h3>';
            echo '<ul class="error-list">';
            foreach ($errors as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul>';
            echo '</div>';

            // Delete the transient after displaying the errors
            delete_transient('framez_gallery_validation_errors');
        }
    }

    /**
     * Render the meta box for gallery settings.
     *
     * @param WP_Post $post The post object.
     */
    public function renderMetaBoxSettings($post)
    {
        $output = '<div class="framez-settings">';

        $metaData = $this->metaData();

        foreach ($metaData as $key => $field) {
            $fieldName = 'framez[' . $key . ']';
            $value = get_post_meta($post->ID, $fieldName, true);
            if (empty($value) && isset($field['default'])) {
                $value = $field['default'];
            }
            $output .= "<div class='framez-field'>";
            $output .= $this->renderField($fieldName, $field, $value);
            $output .= "</div>";
        }

        // Save the post ID in a hidden field
        $output .= sprintf(
            '<input type="hidden" name="framez_gallery_id" value="%d" />',
            esc_attr($post->ID)
        );

        // Add nonce field for security
        $output .= wp_nonce_field('framez_gallery_settings_nonce', 'framez_gallery_settings_nonce', true, false);

        $output .= '</div>';

        echo $output;
    }

    /**
     * Render the meta box for gallery preview.
     *
     * @param WP_Post $post The post object.
     */
    public function renderMetaBoxPreview($post)
    {
        $type = get_post_meta($post->ID, 'framez[type]', true);
        $dir = get_post_meta($post->ID, 'framez[directory_path]', true);
        $dirUrl = get_post_meta($post->ID, 'framez[directory_url]', true);

        if ($type == 'directory' && !empty($dir) && !empty($dirUrl)) {
            echo do_shortcode("[framez gallery='" . esc_attr(get_post_meta($post->ID, 'framez[key]', true)) . "']");
            return;
        }

        // Render the preview content
        echo '<div class="gallery-preview">';
        echo '<p style="margin-bottom:0;" class="empty">' . __('No Preview available', 'framez') . '</p>';
        echo '</div>';
    }

    /**
     * Render Methods for the settings
     */
    public function renderField($fieldName, $field, $value = ''): string
    {
        $field['name'] = $fieldName;

        switch ($field['type']) {
            case 'text':
                return $this->renderTextField($fieldName, $field, $value);
            case 'select':
                return $this->renderSelectField($fieldName, $field, $value);
            case 'hidden':
                return $this->renderHiddenField($fieldName, $field, $value);
                // Add more field types as needed
        }

        throw new \Exception(__('Unsupported field type: ', 'framez') . $field['type']);
    }

    /**
     * Render a text field.
     *
     * @param string $fieldKey The field key.
     * @param array $field The field configuration.
     * @param string $value The current value of the field.
     * @return string The HTML for the text field.
     */
    public function renderTextField($fieldKey, $field, $value = ''): string
    {
        $value = $value ?: $field['default'];
        $required = isset($field['required']) && $field['required'] ? 'required' : '';
        $description = isset($field['description']) ? sprintf('<p class="description">%s</p>', esc_html($field['description'])) : '';

        $fieldId = str_replace('framez[', 'framez_', $fieldKey);
        $fieldId = str_replace(']', '', $fieldId);

        $fieldPrefix = '';
        if (array_key_exists('prefix', $field) && !empty($field['prefix'])) {
            $fieldPrefixValue = is_callable($field['prefix']) ? call_user_func($field['prefix']) : $field['prefix'];
            $fieldPrefix = '<span class="framez-prefix">' . esc_html($fieldPrefixValue) . '</span>';
        }

        return sprintf(
            '<label for="%s">%s</label>
            <div class="framez-input">
                %s <input type="text" id="%s" name="%s" value="%s" class="regular-text" />
            </div>',
            esc_attr($fieldId),
            esc_html($field['label']) . ($required ? ' <span class="required">*</span>' : ''),
            $fieldPrefix,
            esc_attr($fieldId),
            esc_attr($fieldKey),
            esc_attr($value),
            // $description
        );
    }

    /**
     * Render a select field.
     *
     * @param string $fieldKey The field key.
     * @param array $field The field configuration.
     * @param string $value The current value of the field.
     * @return string The HTML for the select field.
     */
    public function renderSelectField($fieldKey, $field, $value = ''): string
    {
        $value = $value ?: $field['default'];
        $required = isset($field['required']) && $field['required'] ? 'required' : '';
        $description = isset($field['description']) ? sprintf('<p class="description">%s</p>', esc_html($field['description'])) : '';

        $options = '';
        foreach ($field['options'] as $optionValue => $optionLabel) {
            $selected = selected($value, $optionValue, false);
            $options .= sprintf('<option value="%s" %s>%s</option>', esc_attr($optionValue), $selected, esc_html($optionLabel));
        }

        return sprintf(
            '<label for="%s">%s</label>
            <div class="framez-input">
            <select id="%s" name="%s" %s>
                %s
            </select>
            %s
            </div>',
            esc_attr($fieldKey),
            esc_html($field['label']) . ($required ? ' <span class="required">*</span>' : ''),
            esc_attr($fieldKey),
            esc_attr($fieldKey),
            $required,
            $options,
            $description
        );
    }

    /**
     * Render a hidden field.
     */
    public function renderHiddenField($fieldKey, $field, $value = ''): string
    {
        $value = $value ?: $field['default'];

        $fieldId = str_replace('framez[', 'framez_', $fieldKey);
        $fieldId = str_replace(']', '', $fieldId);

        return sprintf(
            '<input type="hidden" id="%s" name="%s" value="%s" />',
            esc_attr($fieldId),
            esc_attr($fieldKey),
            esc_attr($value)
        );
    }


    public function registerPostType()
    {
        $labels = array(
            'name' => _x('Galleries', 'Post Type General Name', 'framez'),
            'singular_name' => _x('Gallery', 'Post Type Singular Name', 'framez'),
            'menu_name' => _x('Galleries', 'Admin Menu text', 'framez'),
            'name_admin_bar' => _x('Gallery', 'Add New on Toolbar', 'framez'),
            'archives' => __('Gallery Archives', 'framez'),
            'attributes' => __('Gallery Attributes', 'framez'),
            'parent_item_colon' => __('Parent Gallery:', 'framez'),
            'all_items' => __('All Galleries', 'framez'),
            'add_new_item' => __('Add New Gallery', 'framez'),
            'add_new' => __('Add New', 'framez'),
            'new_item' => __('New Gallery', 'framez'),
            'edit_item' => __('Edit Gallery', 'framez'),
            'update_item' => __('Update Gallery', 'framez'),
            'view_item' => __('View Gallery', 'framez'),
            'view_items' => __('View Galleries', 'framez'),
            'search_items' => __('Search Gallery', 'framez'),
            'not_found' => __('Not found', 'framez'),
            'not_found_in_trash' => __('Not found in Trash', 'framez'),
            'featured_image' => __('Featured Image', 'framez'),
            'set_featured_image' => __('Set featured image', 'framez'),
            'remove_featured_image' => __('Remove featured image', 'framez'),
            'use_featured_image' => __('Use as featured image', 'framez'),
            'insert_into_item' => __('Insert into Gallery', 'framez'),
            'uploaded_to_this_item' => __('Uploaded to this Gallery', 'framez'),
            'items_list' => __('Galleries list', 'framez'),
            'items_list_navigation' => __('Galleries list navigation', 'framez'),
            'filter_items_list' => __('Filter Galleries list', 'framez'),
        );
        $args = array(
            'label' => __('Gallery', 'framez'),
            'description' => __('', 'framez'),
            'labels' => $labels,
            'menu_icon' => 'dashicons-images-alt2',
            'supports' => array('title', 'custom-fields'),
            'taxonomies' => array(),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 10,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'exclude_from_search' => true,
            'show_in_rest' => true,
            'publicly_queryable' => true,
            'capability_type' => 'post',
            'rewrite' => false,
        );
        register_post_type('framez-gallery', $args);
    }

    public static function getByKey(string $key): ?array
    {
        $post = get_posts([
            'post_type' => 'framez-gallery',
            'meta_key' => 'framez[key]',
            'meta_value' => $key,
            'numberposts' => 1,
        ]);
        if (!$post) {
            return null;
        } else {
            $post = reset($post);
        }

        return [
            'id' => $post->ID,
            'title' => $post->post_title,
            'key' => get_post_meta($post->ID, 'framez[key]', true),
            'type' => get_post_meta($post->ID, 'framez[type]', true),
            'directory_path' => get_post_meta($post->ID, 'framez[directory_path]', true),
            'directory_url' => get_post_meta($post->ID, 'framez[directory_url]', true),
        ];
    }
}
