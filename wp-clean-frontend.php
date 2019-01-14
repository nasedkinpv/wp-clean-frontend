<?
/**
 *  Remove jQuery
 *  Disable Embed js
 */

add_action('wp_enqueue_scripts', function () {
    wp_deregister_script('jquery'); // deregister jquery
    remove_action('wp_head', 'wp_oembed_add_host_js');
});

/**
 * Remove default fields in comment form
 * @link https://codex.wordpress.org/Function_Reference/comment_form
 */

function disable_comment_fields($fields)
{
    unset($fields['author']);
    unset($fields['email']);
    unset($fields['url']);
    return $fields;
}
add_filter('comment_form_default_fields', 'disable_comment_fields');

/**
 * Remove emoji support
 * @link https://codex.wordpress.org/Emoji
 */

add_action('init', function () {
    // Front-end
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    add_filter('emoji_svg_url', '__return_false');
    // Admin
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    add_filter('tiny_mce_plugins', function ($plugins) {
        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        }
        return array();
    });
    // Feeds
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    // Emails
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
});

/**
 * Remove feeds and wordpress-specific content that is generated on the wp_head hook.
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/wp_head
 */

add_action('init', function () {
    // Remove the Really Simple Discovery service link
    remove_action('wp_head', 'rsd_link');
    // Remove the link to the Windows Live Writer manifest
    remove_action('wp_head', 'wlwmanifest_link');
    // Remove the general feeds
    remove_action('wp_head', 'feed_links', 2);
    // Remove the extra feeds, such as category feeds
    remove_action('wp_head', 'feed_links_extra', 3);
    // Remove the displayed XHTML generator
    remove_action('wp_head', 'wp_generator');
    // Remove the REST API link tag
    remove_action('wp_head', 'rest_output_link_wp_head', 10);
    // Remove oEmbed discovery links.
    remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
    // Remove rel next/prev links
    remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
});

/**
 * Disable search query
 * @param  $query [description]
 * @param $error Set to false to redirect to same page
 */

add_action('parse_query', function ($query, $error = true) {
    if (is_search()) {
        $query->is_search = false;
        $query->query_vars['s'] = false;
        $query->query['s'] = false;
      // Send to 404
        $query->is_404 = true;
    }
});

/**
 * Disable search form
 */

add_filter('get_search_form', '__return_empty_string');

/**
 * Disable update notifcations.
 */

// Core update notifications
add_filter('pre_site_transient_update_core', 'last_checked_now');
// Plugin update notifications
add_filter('pre_site_transient_update_plugins', 'last_checked_now');
// Theme update notifications
add_filter('pre_site_transient_update_themes', 'last_checked_now');
// Core translation notifications
add_filter('site_transient_update_core', 'remove_translations');
// Plugin translation notifications
add_filter('site_transient_update_plugins', 'remove_translations');
// Theme translation notifications
add_filter('site_transient_update_themes', 'remove_translations');

function last_checked_now($transient)
{
    include ABSPATH . WPINC . '/version.php';
    $current = new stdClass;
    $current->updates = array();
    $current->version_checked = $wp_version;
    $current->last_checked = time();
    return $current;
}

function remove_translations($transient)
{
    if (is_object($transient) && isset($transient->translations)) {
        $transient->translations = array();
    }
    return $transient;
}

/**
 * Remove actions that checks for updates
 */

add_action('admin_init', function () {
    remove_action('wp_maybe_auto_update', 'wp_maybe_auto_update');
    remove_action('admin_init', 'wp_maybe_auto_update');
    remove_action('admin_init', 'wp_auto_update_core');
    wp_clear_scheduled_hook('wp_maybe_auto_update');
});

/**
 * Disable automatic core updates
 */

add_filter('automatic_updater_disabled', '__return_true');
add_filter('allow_minor_auto_core_updates', '__return_false');
add_filter('allow_major_auto_core_updates', '__return_false');
add_filter('allow_dev_auto_core_updates', '__return_false');
add_filter('auto_update_core', '__return_false');
add_filter('wp_auto_update_core', '__return_false');
add_filter('auto_core_update_send_email', '__return_false');
add_filter('send_core_update_notification_email', '__return_false');
add_filter('automatic_updates_send_debug_email', '__return_false');
add_filter('automatic_updates_is_vcs_checkout', '__return_true');

/**
 * Disable automatic plugin updates
 */

add_filter('auto_update_plugin', '__return_false');

/**
 * Disable automatic theme updates
 */

add_filter('auto_update_theme', '__return_false');

/**
 * Disable automatic translation updates
 */

add_filter('auto_update_translation', '__return_false');