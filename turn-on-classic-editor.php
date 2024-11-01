<?php
/*
Plugin Name: Turn-On Classic Editor
Description: This plugin disables the block editor by default and enables the classic editor, enhancing your writing experience. It also supports Classic Widgets, eliminating the need for an additional plugin. You can still use the block editor for specific pages or posts if you wish. Just install it and forget about it.
Version: 1.1.2
Author: Ardetahost Media Group
Author URI: https://ardetahost.com/
Plugin URI: https://ardetahost.com/turn-on-classic-editor/
Text Domain: turn-on-classic-editor
License: GPLv2 or later
*/



// Function to disable block editor and enable classic editor
function tocce_enable_classic_editor() {
    // Disable block editor for all posts
    add_filter('use_block_editor_for_post', '__return_false', 10);

    // Disable block editor for all custom post types
    add_filter('use_block_editor_for_post_type', '__return_false', 10);
}
add_action('init', 'tocce_enable_classic_editor');

// Add "Edit (Gutenberg)" link to post/page actions
function tocce_add_gutenberg_edit_link($actions, $post) {
    if (post_type_supports($post->post_type, 'editor')) {
        // Create a nonce for security
        $nonce = wp_create_nonce('tocce_edit_gutenberg_nonce');
        
        // Construct the URL to force Gutenberg editor
        $gutenberg_edit_link = admin_url('post.php?post=' . intval($post->ID) . '&action=edit&classic-editor=false&_wpnonce=' . $nonce);
        $actions['edit_gutenberg'] = '<a href="' . esc_url($gutenberg_edit_link) . '" title="' . esc_attr__('Edit in Gutenberg', 'turn-on-classic-editor') . '">Edit (Gutenberg)</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'tocce_add_gutenberg_edit_link', 10, 2);
add_filter('page_row_actions', 'tocce_add_gutenberg_edit_link', 10, 2);

// Force the block editor if "classic-editor=false" is in the URL
function tocce_force_block_editor($use_block_editor, $post) {
    // Check for query parameters
    if (isset($_GET['classic-editor']) && isset($_GET['_wpnonce'])) {
        // Sanitize and unslash the nonce value
        $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
        
        // Verify the nonce
        if (wp_verify_nonce($nonce, 'tocce_edit_gutenberg_nonce')) {
            // If 'classic-editor' is 'false', enable block editor
            if (sanitize_text_field(wp_unslash($_GET['classic-editor'])) === 'false') {
                return true;
            }
        }
    }
    return $use_block_editor;
}
add_filter('use_block_editor_for_post', 'tocce_force_block_editor', 11, 2);
add_filter('use_block_editor_for_post_type', 'tocce_force_block_editor', 11, 2);

?>
