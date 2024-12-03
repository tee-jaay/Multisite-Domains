<?php
/**
 * Plugin Name: Multisite Domains
 * Description: Retrieves a cleaned list of domains in a multisite installation.
 * Version: 1.0.2
 * Author: Tamjid Bhuiyan
 * Author URI: https://devapps.uk
 * License: GPLv2 or later
 * Text Domain: multisite-domains
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register the REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('multisite/v1', '/domains', array(
        'methods' => 'GET',
        'callback' => 'get_clean_multisite_domains',
        'permission_callback' => '__return_true',
    ));
});

/**
 * Retrieves a cleaned list of domains in a multisite installation.
 *
 * @return WP_Error|WP_REST_Response
 */
function get_clean_multisite_domains() {
    if (!is_multisite()) {
        return new WP_Error('not_multisite', 'This is not a multisite installation', array('status' => 400));
    }

    $sites = get_sites();
    $cleaned_domains = [];

    foreach ($sites as $site) {
        // Skip the root site (ID 1)
        if ($site->blog_id == 1) {
            continue;
        }

        // Extract subsite path and clean it
        $path = trim($site->path, '/');
        $path_parts = explode('-', $path);

        // Construct the domain dynamically from the path
        $cleaned_domain = isset($path_parts[1]) 
            ? $path_parts[0] . '.' . $path_parts[1] 
            : $site->domain; // Fallback to domain if TLD not found

        // Build the cleaned domain array
        $cleaned_domains[] = [
            'id' => (string) $site->blog_id,
            'domain' => $cleaned_domain,
        ];
    }

    return rest_ensure_response($cleaned_domains);
}
