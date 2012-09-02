<?php

$synved_shortcode_options = array(
'synved_shortcode' => array(
	'label' => 'Shortcodes',
	'title' => 'WordPress Shortcodes',
	'sections' => array(
		'customize_look' => array(
			'label' => __('Customize Look', 'synved-shortcode'), 
			'tip' => synved_option_callback('synved_shortcode_section_customize_look_tip', __('Customize the look & feel of WordPress Shortcodes', 'synved-shortcode')),
			'settings' => array(
				'shortcode_widgets' => array(
					'default' => true, 'label' => __('Shortcodes In Widgets', 'synved-shortcode'), 
					'tip' => __('Allow shortcodes in Text widgets', 'synved-shortcode')
				),
				'shortcode_feed' => array(
					'default' => true, 'label' => __('Shortcodes In Feeds', 'synved-shortcode'), 
					'tip' => __('Allow shortcodes in Feeds (RSS, Atom, etc.)', 'synved-shortcode')
				),
				'custom_skin' => array(
					'default' => 'basic',
					'set' => synved_option_callback('synved_shortcode_custom_skin_set', 'basic=Basic'),
					'label' => __('Select Skin', 'synved-shortcode'), 
					'tip' => __('Select the skin to use for WordPress Shortcodes', 'synved-shortcode')
				),
				'skin_slickpanel' => array(
					'type' => 'addon',
					'target' => SYNVED_SHORTCODE_ADDON_PATH,
					'folder' => 'skin-slickpanel',
					'style' => 'important',
					'label' => __('SlickPanel Skin', 'synved-shortcode'), 
					'tip' => __('Click the button to install the SlickPanel skin, get it <a target="_blank" href="http://synved.com/wordpress-beatiful-shortcodes/">here</a>.', 'synved-shortcode')
				),
				'custom_style' => array(
					'type' => 'style',
					'label' => __('Extra Styles', 'synved-shortcode'), 
					'tip' => __('Any CSS styling code you type in here will be loaded after all of the WordPress Shortcodes styles.', 'synved-shortcode')
				),
			)
		)
	)
)
);

synved_option_register('synved_shortcode', $synved_shortcode_options);

synved_option_include_module_addon_list('synved-shortcode');


function synved_shortcode_section_customize_look_tip($tip, $item)
{
	if (!synved_option_addon_installed('synved_shortcode', 'skin_slickpanel'))
	{
		$tip .= '<p style="font-size:120%;"><b>Want a slicker, more professional look for your shortcodes? Get the <a target="_blank" href="http://synved.com/wordpress-beatiful-shortcodes/">SlickPanel skin</a></b>!</p> <a target="_blank" href="http://synved.com/wordpress-beatiful-shortcodes/"><img src="' . synved_shortcode_path_uri() . '/image/skin-slickpanel.png" /></a>';
	}
	
	return $tip;
}

function synved_shortcode_custom_skin_set($set, $item) 
{
	if ($set != null && !is_array($set))
	{
		$set = synved_option_item_set_parse($item, $set);
	}
	
	if (synved_option_addon_installed('synved_shortcode', 'skin_slickpanel'))
	{
		$set[]['slickpanel'] = 'SlickPanel';
	}
	
	return $set;
}

function synved_shortcode_path_uri($path = null)
{
	$uri = plugins_url('/synved-shortcodes') . '/synved-shortcode';
	
	if (function_exists('synved_plugout_module_uri_get'))
	{
		$mod_uri = synved_plugout_module_uri_get('synved-shortcode');
		
		if ($mod_uri != null)
		{
			$uri = $mod_uri;
		}
	}
	
	if ($path != null)
	{
		if (substr($uri, -1) != '/' && $path[0] != '/')
		{
			$uri .= '/';
		}
		
		$uri .= $path;
	}
	
	return $uri;
}

function synved_shortcode_wp_register_common_scripts()
{
	$uri = synved_shortcode_path_uri();
	
	wp_register_style('jquery-ui', $uri . '/jqueryUI/css/custom/jquery-ui-1.8.11.custom.css', false, '1.8.11');
	wp_register_style('synved-shortcode-layout', $uri . '/style/layout.css', false, '1.0');
	wp_register_style('synved-shortcode-jquery-ui', $uri . '/style/jquery-ui.css', array('jquery-ui'), '1.0');
	
	wp_register_script('jquery-unselectable', $uri . '/script/jquery-unselectable.js', array('jquery'), '1.0.0');
	wp_register_script('jquery-babbq', $uri . '/script/jquery.ba-bbq.min.js', array('jquery'), '1.2.1');
	wp_register_script('jquery-scrolltab', $uri . '/script/jquery.scrolltab.js', array('jquery'), '1.0');
	wp_register_script('jquery-ui-accordion', $uri . '/script/ui/jquery.ui.accordion.min.js', array('jquery-ui-widget'), '1.8.16');
	wp_register_script('jquery-ui-button', $uri . '/script/ui/jquery.ui.button.min.js', array('jquery-ui-widget'), '1.8.16');
	wp_register_script('jquery-ui-slider', $uri . '/script/ui/jquery.ui.slider.min.js', array('jquery-ui-widget'), '1.8.16');
	wp_register_script('synved-shortcode-base', $uri . '/script/base.js', array('jquery-babbq', 'jquery-scrolltab', 'jquery-ui-tabs', 'jquery-ui-accordion', 'jquery-ui-button', 'jquery-unselectable', 'jquery-ui-slider'), '1.0');
}

function synved_shortcode_enqueue_scripts()
{
	$uri = synved_shortcode_path_uri();
	
	synved_shortcode_wp_register_common_scripts();
	
	wp_register_script('synved-shortcode-custom', $uri . '/script/custom.js', array('synved-shortcode-base'), '1.0');
	
	wp_enqueue_style('jquery-ui');
	wp_enqueue_style('synved-shortcode-layout');
	wp_enqueue_style('synved-shortcode-jquery-ui');
	
	wp_enqueue_script('synved-shortcode-custom');
}

function synved_shortcode_print_styles()
{
}

function synved_shortcode_admin_enqueue_scripts()
{
	$uri = synved_shortcode_path_uri();
	
	synved_shortcode_wp_register_common_scripts();
	
	wp_register_style('synved-shortcode-admin', $uri . '/style/admin.css', array('jquery-ui', 'thickbox', 'wp-pointer', 'wp-jquery-ui-dialog'), '1.0');
	
	wp_register_script('synved-shortcode-script-admin', $uri . '/script/admin.js', array('synved-shortcode-base', 'jquery', 'suggest', 'media-upload', 'thickbox', 'jquery-ui-core', 'jquery-ui-progressbar', 'jquery-ui-dialog'), '1.0.0');
	wp_localize_script('synved-shortcode-script-admin', 'SynvedShortcodeVars', array('flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'), 'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'), 'ajaxurl' => admin_url('admin-ajax.php'), 'synvedSecurity' => wp_create_nonce('synved-shortcode-submit-nonce'), 'mainUri' => $uri));
	
	wp_enqueue_style('jquery-ui');
	wp_enqueue_style('farbtastic');
	wp_enqueue_style('synved-shortcode-layout');
	wp_enqueue_style('synved-shortcode-jquery-ui');
	wp_enqueue_style('synved-shortcode-admin');
	
	wp_enqueue_script('plupload-all');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('suggest');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('farbtastic');
	wp_enqueue_script('synved-shortcode-script-admin');
}

function synved_shortcode_admin_print_styles()
{
	// Tries to fix WordPress SEO generic selector conflicts
	global $wp_scripts;
	global $wp_styles;
	
	if (isset($wp_scripts))
	{
		if ($wp_scripts->query('jigoshop_script', 'queue'))
		{
			$wp_scripts->dequeue('jigoshop_script');
			$wp_scripts->enqueue('jigoshop_script');
			
#			$wp_scripts->dequeue('synved-shortcode-custom');
#			$wp_scripts->enqueue('synved-shortcode-custom');
		}
	}
	
	if (isset($wp_styles))
	{
		$color = get_user_meta(get_current_user_id(), 'admin_color', true);
		
		if ($wp_styles->query('metabox-tabs', 'queue'))
		{
			$wp_styles->dequeue(array('metabox-tabs', 'metabox-' . $color));
			$wp_styles->enqueue(array('metabox-tabs', 'metabox-' . $color));
			
			$wp_styles->dequeue('synved-shortcode-jquery-ui');
			$wp_styles->enqueue('synved-shortcode-jquery-ui');
			
			if ($wp_styles->query('synved-shortcode-skin-slickpanel-layout', 'queue'))
			{
				$wp_styles->dequeue('synved-shortcode-skin-slickpanel-layout');
				$wp_styles->enqueue('synved-shortcode-skin-slickpanel-layout');
			}
			
			if ($wp_styles->query('synved-shortcode-skin-slickpanel-jquery-ui', 'queue'))
			{
				$wp_styles->dequeue('synved-shortcode-skin-slickpanel-jquery-ui');
				$wp_styles->enqueue('synved-shortcode-skin-slickpanel-jquery-ui');
			}
		}
	}
}

function synved_shortcode_wp_tinymce_plugin($plugin_array)
{
	$plugin_array['synved_shortcode'] = synved_shortcode_path_uri() . '/script/tinymce_plugin.js';

	return $plugin_array;
}

function synved_shortcode_wp_tinymce_button($buttons) 
{
	array_push($buttons, '|', 'synved_shortcode');
	
	return $buttons;
}

function synved_shortcode_ajax_callback()
{
	check_ajax_referer('synved-shortcode-submit-nonce', 'synvedSecurity');

	if (!isset($_POST['synvedAction']) || $_POST['synvedAction'] == null) 
	{
		return;
	}

	$action = $_POST['synvedAction'];
	$params = isset($_POST['synvedParams']) ? $_POST['synvedParams'] : null;
	$response = null;
	$response_html = null;
	
	if (is_string($params))
	{
		$parms = json_decode($params, true);
		
		if ($parms == null)
		{
			$parms = json_decode(stripslashes($params), true);
		}
		
		$params = $parms;
	}
	
	switch ($action)
	{
		case 'load-ui':
		{
			$uri = synved_shortcode_path_uri();
#			
#			$response_html .= '
#<script type="text/javascript" src="' . '' . '" />';

#			if (synved_option_addon_installed('synved_shortcode', 'skin_slickpanel'))
#			{
#				$set[]['slickpanel'] = 'SlickPanel';
#			}
			
			if (current_user_can('edit_posts') || current_user_can('edit_pages'))
			{
				$response_html .= '
<div class="synved-shortcode-edit-popup">';

				$response_html .= '<h3 class="popup-title">' . __('Select your shortcode, edit it, preview it and confirm when you\'re done!', 'synved-shortcode') . '</h3>';
				$response_html .= '
<form action="" method="post">
<div class="synved-shortcode-edit-ui">';

				$list = synved_shortcode_list();
				$extra_fields = null;
				$help_html = null;
				
				$response_html .= '
<div class="synved-shortcode-edit-ui-selector">
<select name="synved_shortcode_list">';

				foreach ($list as $shortcode_name => $shortcode_item)
				{
					$name_alt = $shortcode_item['name_alt'];
					$label = $shortcode_item['label'];
					$callback = $shortcode_item['callback'];
					$internal = $shortcode_item['internal'];
					$default = $shortcode_item['default'];
					$help = $shortcode_item['help'];
					
					if ($internal == false)
					{
						$tip = null;
						$args = null;
							
						if ($help != null)
						{
							if (is_string($help))
							{
								$tip = $help;
							}
							else if (is_array($help))
							{
								$tip = isset($help['tip']) ? $help['tip'] : null;
								$args = isset($help['parameters']) ? $help['parameters'] : null;
							}
							
							$help_html .= '
<div class="synved-shortcode-help-item" id="' . esc_attr('synved-shortcode-help-item-' . $shortcode_name) . '">';
				
							$help_html .= '
<div class="help-tip">
<b>[' . $name_alt . ']</b> --> ' . $tip . '
</div>';

							if ($args != null)
							{
								$help_html .= '
<div class="help-parameter-list-wrap">';

								$help_html .= '
<h4 class="ui-title">' . __('Parameters', 'synved-shortcode') . ':</h4>';

								$help_html .= '
<ul class="help-parameter-list">';
								
								foreach ($args as $arg_name => $arg_tip)
								{
									$help_html .= '
<li class="help-parameter-tip">
<b>' . $arg_name . '</b>: <i>'  . $arg_tip . '</i>
</li>';
								}
								
								$help_html .= '
</ul>';
								$help_html .= '
</div>';
							}

							$help_html .= '
</div>';
						}
						
						$title = $tip != null ? (' title="' . esc_attr($tip) . '"') : null;
						
						$response_html .= '
<option value="' . esc_attr($shortcode_name) . '"' . $title . '>' . $label . '</option>';
						
						$extra_fields .= '
<input type="hidden" name="shortcode_content[' . esc_attr($shortcode_name) . ']" value="' . esc_attr($default) . '" />';
						
					}
				}

				$response_html .= '
</select> &lt;-- <span class="ui-message">' . __('Select the type of shortcode on the left', 'synved-shortcode') . '</span>' . $extra_fields . '
</div>';

				$response_html .= '
<div class="synved-shortcode-edit-ui-viewer">';

				$response_html .= '
<div class="ui-code-wrap">
<div class="ui-wrap ui-wrap-left">';

				$response_html .= '
<h4 class="ui-title">' . __('Code', 'synved-shortcode') . ':</h4>
<textarea name="synved_shortcode_code" class="ui-code"></textarea>';

				$response_html .= '
</div>
</div>';

				$response_html .= '
<div class="ui-preview-wrap">
<div class="ui-wrap ui-wrap-right">';

				$response_html .= '
<h4 class="ui-title">' . __('Preview', 'synved-shortcode') . ': <img class="preview-loader" style="visibility:hidden" src="' . $uri . '/image/ajax-loader.gif" /></h4>
<div class="ui-preview"></div>';

				$response_html .= '
</div>
</div>';

				$response_html .= '
</div>';

				$response_html .= '<div style="clear:both"></div>';
				
				$response_html .= '
<div class="synved-shortcode-edit-ui-help">
<div class="ui-help-wrap">';

#				$response_html .= '
#<h3 class="">' . __('Help', 'synved-shortcode') . ':</h3>';
				$response_html .= '
<div class="ui-help"></div>';

				$response_html .= '
</div>';

				if ($help_html != null)
				{
					$help_html = '
<div class="synved-shortcode-help" style="display:none;">
' . $help_html . '
</div>';

					$response_html .= $help_html;
				}

				$response_html .= '
</div>';

				$response_html .= '
</div>';

				$response_html .= '<div style="clear:both"></div>';

				$response_html .= '
<div class="synved-shortcode-edit-actions">';

				$response_html .= '<button class="action-confirm button-primary">' . __('Confirm and add shortcode', 'synved-shortcode') . '</button>';

				$response_html .= '
</div>';
				
				$response_html .= '
</form>
</div>';
			}
			
			break;
		}
		case 'preview-code':
		{
			if (current_user_can('edit_posts') || current_user_can('edit_pages'))
			{
				$code = isset($params['code']) ? $params['code'] : null;
				
				if (get_magic_quotes_gpc() || get_magic_quotes_runtime() || true) {
						$code = stripslashes($code);
				}
				
				$response_html = do_shortcode($code);
			}
			
			break;
		}
	}

	while (ob_get_level() > 0) 
	{
		ob_end_clean();
	}

	if ($response != null) 
	{
		$response = json_encode($response);

		header('Content-Type: application/json');

		echo $response;
	}
	else if ($response_html != null) 
	{
		header('Content-Type: text/html');

		echo $response_html;
	}
	else 
	{
		header('HTTP/1.1 403 Forbidden');
	}

	exit();
}

function synved_shortcode_init()
{
	if (current_user_can('edit_posts') || current_user_can('edit_pages'))
	{
		if (get_user_option('rich_editing') == 'true')
		{
			add_filter('mce_external_plugins', 'synved_shortcode_wp_tinymce_plugin');
			add_filter('mce_buttons', 'synved_shortcode_wp_tinymce_button');
		}
	}

	$priority = defined('SHORTCODE_PRIORITY') ? SHORTCODE_PRIORITY : 11;
	
	if (synved_option_get('synved_shortcode', 'shortcode_widgets'))
	{
		add_filter('widget_text', 'do_shortcode', $priority);
	}
	
	if (synved_option_get('synved_shortcode', 'shortcode_feed'))
	{
		add_filter('the_content_feed', 'do_shortcode', $priority);
	}
	
  add_action('wp_ajax_synved_shortcode', 'synved_shortcode_ajax_callback');
  add_action('wp_ajax_nopriv_synved_shortcode', 'synved_shortcode_ajax_callback');

	if (!is_admin())
	{
		if (isset($_GET['synved_dynamic_tab']))
		{
			ob_start();
		}
		
		add_action('wp_enqueue_scripts', 'synved_shortcode_enqueue_scripts');
		//add_action('wp_print_styles', 'synved_shortcode_print_styles');
	}
}

add_action('init', 'synved_shortcode_init');
add_action('admin_enqueue_scripts', 'synved_shortcode_admin_enqueue_scripts');
add_action('admin_print_styles', 'synved_shortcode_admin_print_styles', 1);

?>
