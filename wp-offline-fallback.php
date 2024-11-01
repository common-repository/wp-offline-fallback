<?php

/*
Plugin Name: WP Offline Fallback
Plugin URI: http://laptrinh.senviet.org
Description: Help you to show a message to the visitor when they visit your website without the internet. Yes, it's possible.
Version: 1.0.4
Author: nguyenvanduocit
Author URI: http://senviet.org
License: GPL2
*/

/**
 * Show admin notice
 */
function wpof_admin_notices(){
	$scheme = parse_url(get_option( 'siteurl' ), PHP_URL_SCHEME);
	if($scheme == 'http'){
		wpof_render_notice(__( 'WP Offline Fallback: Site of you need to have SSL, otherwise the plugin will not able to work.', 'wpof' ));
		return;
	}

	$page = get_page_by_path('/offline-fallback', OBJECT, 'page');
	if(!$page){
        echo '<div class="notice notice-error"><p>'.sprintf(__( 'WP Offline Fallback: You need to create a public page with the following url: <a href="%1$s/offline-fallback">%1$s/offline-fallback</a>', 'wpof' ),get_option( 'siteurl' )).'</p></div>';
		return;
	}
	$screen = get_current_screen();
	if(($screen->parent_file == 'edit.php?post_type=page') && isset($_GET['post'])){
		$page = get_post($_GET['post']);
		if($page && ($page->post_name == 'offline-fallback')){
            printf( '<div class="notice notice-warning"><p>%1$s</p></div>', __( 'WP Offline Fallback: To see the changes, you must test in incognito mode, or close all the tab then reopen.', 'wpof' ) );
            $wsContent = file_get_contents(plugin_dir_path( __FILE__ ).'sw.js');
            $wsContent =  str_replace('__VERSION__', md5($page->post_modified), $wsContent);
            printf( '<div class="notice notice-warning"><p>%1$s</p><p><pre>%2$s</pre></p></div>', sprintf(__('Create/update file sw.js at your WordPress\'s root directory with below content, you have to update sw.js once you update this page, make sure that <a target="_blank" href="%1$s/sw.js">%1$s/sw.js</a> accessable','wpof'), get_home_url()), $wsContent );
			return;
		}
	}
}
add_action( 'admin_notices', 'wpof_admin_notices' );
/**
 * Render sw file, replace cache version with page modified
 */
function wpof_render_sw_file(){
	$wsContent = file_get_contents(plugin_dir_path( __FILE__ ).'sw.js');
	$page = get_page_by_path('/offline-fallback', OBJECT, 'page');
	if($page){
		return str_replace('__VERSION__', md5($page->post_modified), $wsContent);
	}
}

function wpof_enqueue_scripts(){
	wp_enqueue_script('wpof-sw-register', plugin_dir_url(__FILE__).'sw-register.js', [], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'wpof_enqueue_scripts');

/**
 * Change page template for offline-fallback page.
 *
 * @param $template
 *
 * @return string
 */
function wpof_template_include($template){

	if(!is_page('offline-fallback')){
		return $template;
	}

	$file = plugin_dir_path(__FILE__).'page-templates/offline-fallback.php';
    $file = apply_filters('wpof_template_path', $file);
	if ( file_exists( $file ) ) {
		return $file;
	}

	return $template;
}
add_filter( 'template_include','wpof_template_include' );
