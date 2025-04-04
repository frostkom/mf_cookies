<?php
/*
Plugin Name: MF Cookies
Plugin URI: https://github.com/frostkom/mf_cookies
Description:
Version: 1.0.7
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://martinfors.se
Text Domain: lang_cookies
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: frostkom/mf_cookies
*/

if(!function_exists('is_plugin_active') || function_exists('is_plugin_active') && is_plugin_active("mf_base/index.php"))
{
	include_once("include/classes.php");

	$obj_cookies = new mf_cookies();

	if(is_admin())
	{
		register_uninstall_hook(__FILE__, 'uninstall_cookies');

		add_action('admin_init', array($obj_cookies, 'settings_cookies'));
		add_action('admin_init', array($obj_cookies, 'admin_init'), 0);

		add_filter('filter_sites_table_settings', array($obj_cookies, 'filter_sites_table_settings'));
	}

	else
	{
		add_action('wp_head', array($obj_cookies, 'wp_head'), 0);
		add_action('wp_footer', array($obj_cookies, 'wp_footer'));
	}

	load_plugin_textdomain('lang_cookies', false, dirname(plugin_basename(__FILE__))."/lang/");

	function uninstall_cookies()
	{
		mf_uninstall_plugin(array(
			'options' => array('setting_cookie_exists', 'setting_cookie_info', 'setting_cookie_deactivate_until_allowed', 'setting_cookie_cookiebot'),
		));
	}
}