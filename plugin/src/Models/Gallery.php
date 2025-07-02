<?php

namespace FrameZ\Models;

class Gallery
{
    /**
     * Register the custom post type for galleries.
     */
    public function register()
    {
        add_action('init', [$this, 'registerPostType']);
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
}
