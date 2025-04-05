<?php

class mf_cookies
{
	var $footer_output = "";
	var $arr_sensitive_data_types = array();

	function __construct(){}

	function settings_cookies()
	{
		$options_area_orig = $options_area = __FUNCTION__;

		add_settings_section($options_area, "", array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

		$arr_settings = array();
		$arr_settings['setting_cookie_exists'] = __("Sensitive Data on This Site", 'lang_cookies');
		$arr_settings['setting_cookie_info'] = __("Information Page", 'lang_cookies');

		if(get_option('setting_cookie_info') > 0)
		{
			$arr_settings['setting_cookie_deactivate_until_allowed'] = __("Deactivate Until Allowed", 'lang_cookies');
		}

		$arr_settings['setting_cookie_cookiebot'] = __("Cookiebot", 'lang_cookies');

		show_settings_fields(array('area' => $options_area, 'object' => $this, 'settings' => $arr_settings));
	}

	function settings_cookies_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);

		echo settings_header($setting_key, __("Cookies", 'lang_cookies'));
	}

		function get_post_password_amount()
		{
			global $wpdb;

			$arr_include = get_post_types(array('public' => true, 'exclude_from_search' => false), 'names');

			$wpdb->get_results($wpdb->prepare("SELECT ID FROM ".$wpdb->posts." WHERE post_status = %s AND post_type IN('".implode("','", $arr_include)."') AND post_password != ''", 'public'));

			return $wpdb->num_rows;
		}

		function get_cookie_types()
		{
			global $wpdb;

			if(!isset($this->arr_sensitive_data_types))
			{
				$arr_sensitive_data_types = array(
					'login' => array(),
					'public' => array(),
				);

				$arr_sensitive_data_types['login']['wordpress_sec_'] = array('label' => __("Account details", 'lang_cookies'), 'used' => false, 'lifetime' => "2 day");
				$arr_sensitive_data_types['login']['wordpress_logged_in_'] = array('label' => __("Indicates whether you are logged in", 'lang_cookies'), 'used' => false, 'lifetime' => "2 day");

				$arr_sensitive_data_types['login']['wordpress_test_cookie'] = array('label' => __("Test if it is possible to set cookies", 'lang_cookies'), 'used' => false, 'lifetime' => "2 day", 'personal_data' => false);
				$arr_sensitive_data_types['login']['wordpress_'] = array('label' => __("Authentication details", 'lang_cookies'), 'used' => false, 'lifetime' => "2 day");

				$arr_sensitive_data_types['login']['wp-settings-time-'] = array('label' => __("Time when user settings was last saved", 'lang_cookies'), 'used' => false, 'lifetime' => "", 'personal_data' => false);
				$arr_sensitive_data_types['login']['wp-settings-'] = array('label' => __("Customization for admin interface", 'lang_cookies'), 'used' => false, 'lifetime' => "2 day", 'personal_data' => false);

				if($this->get_post_password_amount() > 0)
				{
					$arr_sensitive_data_types['public']['wp-postpass_'] = array('label' => __("Maintain session if a post is password protected", 'lang_cookies'), 'used' => false, 'lifetime' => "2 day");
				}

				if(get_option('default_comment_status') == 'open')
				{
					$arr_sensitive_data_types['public']['comment_author_'] = array('label' => __("Remember comment author details", 'lang_cookies'), 'used' => false, 'lifetime' => "1 year");
				}

				if(get_option('setting_cookie_info') > 0)
				{
					$arr_sensitive_data_types['public']['cookie_accepted'] = array('label' => __("Remember if visitor accepts sensitive data on the site", 'lang_cookies'), 'used' => false, 'lifetime' => "1 year", 'personal_data' => false);
				}

				if((int)apply_filters('get_widget_search', 'theme-news-widget') > 0)
				{
					$wpdb->get_results($wpdb->prepare("SELECT option_id FROM ".$wpdb->options." WHERE option_name = %s AND option_value LIKE %s", 'widget_theme-news-widget', "\"news_hide_button\";s:3:\"yes\""));

					if($wpdb->num_rows > 0)
					{
						$arr_sensitive_data_types['public']['hide_news_'] = array('label' => __("Remember if a visitor has hidden the header news post", 'lang_cookies'), 'used' => false, 'lifetime' => "1 year", 'personal_data' => false);
					}
				}

				if((int)apply_filters('get_widget_search', 'theme-info-widget') > 0)
				{
					$wpdb->get_results($wpdb->prepare("SELECT option_id FROM ".$wpdb->options." WHERE option_name = %s AND option_value NOT LIKE %s", 'widget_theme-info-widget', "\"info_time_limit\";s:3:\"0\""));

					if($wpdb->num_rows > 0)
					{
						$arr_sensitive_data_types['public']['cookie_theme_core_info_time_limit'] = array('label' => __("Remember if the visitor has seen the info", 'lang_cookies')." (".__("Time Limit", 'lang_cookies').")", 'used' => false, 'lifetime' => "1 year", 'personal_data' => false);
					}

					$wpdb->get_results($wpdb->prepare("SELECT option_id FROM ".$wpdb->options." WHERE option_name = %s AND option_value NOT LIKE %s", 'widget_theme-info-widget', "\"info_visit_limit\";s:3:\"0\""));

					if($wpdb->num_rows > 0)
					{
						$arr_sensitive_data_types['public']['cookie_theme_core_info_visit_limit'] = array('label' => __("Remember if the visitor has seen the info", 'lang_cookies')." (".__("Visit Limit", 'lang_cookies').")", 'used' => false, 'lifetime' => "1 year", 'personal_data' => false);
					}
				}

				$this->arr_sensitive_data_types = apply_filters('filter_cookie_types', $arr_sensitive_data_types);
			}
		}

		function get_cookie_list($data = array())
		{
			$out = "";

			if(isset($_COOKIE) && count($_COOKIE) > 0)
			{
				$this->get_cookie_types();

				$out .= "<ul>";

					foreach($_COOKIE as $cookie_key => $cookie_value)
					{
						$out .= "<li>";

							$cookie_icon = $cookie_explanation = "";

							foreach($this->arr_sensitive_data_types as $type => $arr_sensitive_data_types)
							{
								foreach($arr_sensitive_data_types as $key => $arr_value)
								{
									if(substr($cookie_key, 0, strlen($key)) == $key)
									{
										switch($type)
										{
											default:
											case 'public':
												$cookie_icon = "fas fa-users";
												$type_title = __("Public", 'lang_cookies');
											break;

											case 'login':
												$cookie_icon = "fas fa-lock";
												$type_title = __("Login", 'lang_cookies');
											break;
										}

										if(!isset($arr_value['personal_data']) || $arr_value['personal_data'] == true)
										{
											$cookie_icon .= " green";
										}

										else
										{
											$cookie_icon .= " grey";
											$type_title .= " (".__("without personal data", 'lang_cookies').")";
										}

										$cookie_explanation = "<span title='".$type_title."'>".$arr_value['label']."</span>";

										$this->arr_sensitive_data_types[$type][$key]['used'] = true;

										break;
									}
								}
							}

							if($cookie_explanation != '')
							{
								$out .= "<i class='".$cookie_icon."' title='".shorten_text(array('string' => $cookie_key, 'limit' => 30, 'add_title' => false)).": ".shorten_text(array('string' => $cookie_value, 'limit' => 20, 'add_title' => false))."'></i> ".$cookie_explanation;
							}

							else
							{
								$out .= "<i class='fa fa-question-circle blue'></i> <strong>".shorten_text(array('string' => $cookie_key, 'limit' => 30, 'add_title' => true))."</strong>: ";

								if(is_array($cookie_value))
								{
									$cookie_value = var_export($cookie_value, true);
								}

								$out .= shorten_text(array('string' => $cookie_value, 'limit' => 10, 'add_title' => true));
							}

						$out .= "</li>";
					}

					foreach($this->arr_sensitive_data_types as $type => $arr_sensitive_data_types)
					{
						foreach($arr_sensitive_data_types as $key => $arr_value)
						{
							if($arr_value['used'] == false)
							{
								$cookie_title = sprintf(__("%s was not saved in your browser but can be saved by the site", 'lang_cookies'), $key);

								switch($type)
								{
									default:
									case 'public':
										$cookie_icon = "fas fa-users red";
										$type_title = __("Public", 'lang_cookies');
									break;

									case 'login':
										$cookie_icon = "fas fa-lock red";
										$type_title = __("Login", 'lang_cookies');
									break;

									case 'font':
										$cookie_icon = "fas fa-font green";
										$cookie_title = __("The font is loaded somewhere on the site", 'lang_cookies');
										$type_title = __("Font", 'lang_cookies');
									break;

									case 'ip':
										$cookie_icon = "fas fa-receipt green";
										$cookie_title = __("The IP address is collected somewhere on the site", 'lang_cookies');
										$type_title = __("IP", 'lang_cookies');
									break;
								}

								$cookie_explanation = "<span title='".$type_title."'>".$arr_value['label']."</span>";

								$out .= "<li>
									<i class='".$cookie_icon."' title='".$cookie_title."'></i> ".$cookie_explanation
								."</li>";
							}
						}
					}

				$out .= "</ul>";
			}

			// Like this would ever happen...
			else
			{
				$out .= "<p>".__("There is no sensitive data saved on this site", 'lang_cookies')."</p>";
			}

			if($data['return'] == 'html')
			{
				return $out;
			}
		}

		function setting_cookie_exists_callback()
		{
			echo $this->get_cookie_list(array('return' => 'html'));
		}

		function setting_cookie_info_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key);

			$arr_data = array();
			get_post_children(array('add_choose_here' => true, 'where' => "(post_excerpt != '' || post_content != '')"), $arr_data);

			$description = "";

			// Not needed right now but just in case we switch this function and setting_cookie_exists_callback() around...
			$this->get_cookie_types();

			if(count($this->arr_sensitive_data_types['public']) > 0)
			{
				if(!($option > 0) && get_option('setting_cookie_cookiebot') == '')
				{
					$description .= "<i class='fa fa-exclamation-triangle yellow display_warning'></i> ";
				}

				$description .= __("There is sensitive information on the public site that is saved for visitors.", 'lang_cookies')." ";
			}

			else if(count($this->arr_sensitive_data_types['login']) > 0)
			{
				$description .= __("There is only sensitive information on this site that is saved when logging in so it is not necessary to add a page for this.", 'lang_cookies')." ";
			}

			$description .= __("The content from this page will be displayed on the site until the visitor clicks to accept the collection of sensitive data.", 'lang_cookies');

			echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => get_option_page_suffix(array('value' => $option)), 'description' => $description));
		}

		function setting_cookie_deactivate_until_allowed_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key, 'no');

			echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option, 'description' => __("This will try to prevent sensitive information to be saved in the visitor's browser until the visitor have accepted your information from the page above", 'lang_cookies')));
		}

		function setting_cookie_cookiebot_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key);

			echo show_textfield(array('name' => $setting_key, 'value' => $option));
		}

	function admin_init()
	{
		global $pagenow;

		if($pagenow == 'options-general.php' && check_var('page') == 'settings_mf_base')
		{
			$plugin_include_url = plugin_dir_url(__FILE__);

			mf_enqueue_script('script_cookies', $plugin_include_url."script_wp.js", array('plugin_url' => $plugin_include_url, 'ajax_url' => admin_url('admin-ajax.php')));
		}

		if(function_exists('wp_add_privacy_policy_content'))
		{
			if(get_option('setting_cookie_info') > 0)
			{
				$content = __("A cookie is saved when the visitor accepts the collection of sensitive data on the site, to make sure that the message asking for permission does not appear again.", 'lang_cookies');

				wp_add_privacy_policy_content(__("Theme", 'lang_cookies'), $content);
			}
		}
	}

	function filter_sites_table_settings($arr_settings)
	{
		$arr_settings['settings_cookies'] = array(
			'setting_cookie_info' => array(
				'type' => 'post',
				'global' => false,
				'icon' => "fas fa-cookie",
				'name' => __("Cookies", 'lang_cookies')." - ".__("Information Page", 'lang_cookies'),
			),
			'setting_cookie_deactivate_until_allowed' => array(
				'type' => 'bool',
				'global' => false,
				'icon' => "fas fa-cookie-bite",
				'name' => __("Cookies", 'lang_cookies')." - ".__("Deactivate Until Allowed", 'lang_cookies'),
			),
		);

		return $arr_settings;
	}

	function wp_head()
	{
		global $wpdb, $post, $obj_base;

		$plugin_include_url = plugin_dir_url(__FILE__);

		$setting_cookie_info = get_option('setting_cookie_info');
		$setting_cookie_cookiebot = get_option('setting_cookie_cookiebot');

		if($setting_cookie_info > 0)
		{
			mf_enqueue_style('style_cookies', $plugin_include_url."style.css");
			mf_enqueue_script('script_cookies', $plugin_include_url."script.js", array('plugin_url' => $plugin_include_url));

			$button_classes = (wp_is_block_theme() ? "wp-block-button__link has-background wp-element-button" : "button color_button");

			$result = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_excerpt, post_content FROM ".$wpdb->posts." WHERE ID = '%d' AND post_type = %s AND post_status = %s", $setting_cookie_info, 'page', 'publish'));

			foreach($result as $r)
			{
				$post_id = $r->ID;
				$post_title = $r->post_title;
				$post_excerpt = apply_filters('the_content', $r->post_excerpt);
				$post_content = apply_filters('the_content', $r->post_content);

				$this->footer_output .= "<div id='accept_cookies'>
					<div>";

						$buttons = "<a href='#accept_cookie' class='".$button_classes."'><i class='fa fa-check green'></i> ".__("Accept", 'lang_cookies')."</a>";

						if($post_excerpt != '')
						{
							$this->footer_output .= $post_excerpt;

							if($post_content != '' && $post_content != $post_excerpt)
							{
								$buttons .= " <a href='".get_permalink($post_id)."' class='".$button_classes."' rel='external'>".__("Read More", 'lang_cookies')."</a>";
							}
						}

						else
						{
							$this->footer_output .= $post_content;
						}

						$this->footer_output .= "<div".get_form_button_classes().">".$buttons."</div>
					</div>
				</div>
				<div id='accepted_cookies'>
					<span class='fa-stack fa-2x' title='".__("You have accepted that we collect sensitive data. Do you wish to remove this acceptance?", 'lang_cookies')."'>
						<i class='fas fa-cookie-bite fa-stack-1x'></i>
						<i class='fas fa-ban fa-stack-2x red'></i>
					</span>
				</div>";
			}
		}

		if($setting_cookie_cookiebot != '')
		{
			$this->footer_output .= "<script data-blockingmode='auto' id='Cookiebot' src='https://consent.cookiebot.com/uc.js' data-cbid='".$setting_cookie_cookiebot."' type='text/javascript'></script>";
		}
	}

	function wp_footer()
	{
		if(isset($this->footer_output) && $this->footer_output != '')
		{
			echo $this->footer_output;
		}
	}

	function get_allow_cookies()
	{
		return (get_option('setting_cookie_deactivate_until_allowed') != 'yes');
	}
}