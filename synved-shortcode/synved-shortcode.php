<?php
/*
Module Name: Synved Shortcode
Description: A complete set of WordPress shortcodes to add beautiful and useful elements that will spice up your site
Author: Synved
Version: 1.5.6
Author URI: http://synved.com/
License: GPLv2

LEGAL STATEMENTS

NO WARRANTY
All products, support, services, information and software are provided "as is" without warranty of any kind, express or implied, including, but not limited to, the implied warranties of fitness for a particular purpose, and non-infringement.

NO LIABILITY
In no event shall Synved Ltd. be liable to you or any third party for any direct or indirect, special, incidental, or consequential damages in connection with or arising from errors, omissions, delays or other cause of action that may be attributed to your use of any product, support, services, information or software provided, including, but not limited to, lost profits or lost data, even if Synved Ltd. had been advised of the possibility of such damages.
*/


define('SYNVED_SHORTCODE_LOADED', true);
define('SYNVED_SHORTCODE_VERSION', 100050006);
define('SYNVED_SHORTCODE_VERSION_STRING', '1.5.6');

define('SYNVED_SHORTCODE_ADDON_PATH', str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, dirname(__FILE__) . '/addons'));

include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'synved-shortcode-data.php');
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'synved-shortcode-item.php');
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'synved-shortcode-setup.php');
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'synved-shortcode-presets.php');


$synved_shortcode = array();
$synved_shortcode_recurse = array();


function synved_shortcode_version()
{
	return SYNVED_SHORTCODE_VERSION;
}

function synved_shortcode_version_string()
{
	return SYNVED_SHORTCODE_VERSION_STRING;
}

function synved_shortcode_recurse($code, $name, $id = null)
{
}

function synved_shortcode_do_shortcode($code, $name, $id = null)
{
	global $synved_shortcode_recurse;

	$recurse_global = isset($synved_shortcode_recurse['global']) ? $synved_shortcode_recurse['global'] : null;
	$recurse_count = isset($synved_shortcode_recurse[$name][$id]) ? $synved_shortcode_recurse[$name][$id] : null;

	if ($recurse_global > 0)
	{
		$synved_shortcode_recurse['global'] += 1;
	}
	else
	{
		$synved_shortcode_recurse['global'] = 1;
	}

	if ($recurse_global < 10 && $recurse_count < 1)
	{
		if ($recurse_count > 0)
		{
			$synved_shortcode_recurse[$name][$id] += 1;
		}
		else
		{
			$synved_shortcode_recurse[$name][$id] = 1;
		}
	
		$code = do_shortcode($code);
		
		$synved_shortcode_recurse[$name][$id] -= 1;
	}
	
	$synved_shortcode_recurse['global'] -= 1;
	
	return $code;
}

function synved_shortcode_do_tabs($atts, $content = null, $code = '')
{
	global $synved_shortcode;
	
	$atts_def = array('imitate' => null, 'dynamic' => false, 'scroll' => true, 'class' => '');
	$atts = shortcode_atts($atts_def, $atts);
	$is_imitate = $atts['imitate'];
	$is_dynamic = $atts['dynamic'];
	$is_scroll = $atts['scroll'];
	$att_class = $atts['class'];
	$is_dynamic_load = isset($_GET['synved_dynamic_load']);
	$tab_selected = isset($_GET['snvdstt']) ? $_GET['snvdstt'] : null;
	
	if ($is_imitate === null)
	{
		$is_imitate = $is_dynamic;
	}
	
	$is_imitate = in_array($is_imitate, array('yes', 'true'));
	$is_dynamic = in_array($is_dynamic, array('yes', 'true'));
	$is_scroll = in_array($is_scroll, array('yes', 'true'));
	
	$pattern = get_shortcode_regex();
	$matches = array();
	
	if (preg_match_all('/' . $pattern . '/ms', $content, $matches, PREG_SET_ORDER) > 0)
	{
		$tabs = array();
		
		foreach ($matches as $match)
		{
			if (isset($match[2]) && in_array($match[2], array('tab', 'synved-tab', 'synved_tab')))
			{
				$tabs[] = $match;
			}
		}
		
		if (isset($tabs[0]))
		{
			if (!isset($synved_shortcode['instance']['tabs']))
			{
				$synved_shortcode['instance']['tabs'] = array('count' => 1);
			}
			else
			{
				$synved_shortcode['instance']['tabs']['count'] += 1;
			}
			
			$id = 'synved-tabs-' . $synved_shortcode['instance']['tabs']['count'];
			$class = null;
			$heads = null;
			$bodies = null;
			
			if ($is_dynamic)
			{
				$class .= ' synved-content-dynamic';
			}
			
			if ($is_scroll)
			{
				$class .= ' synved-content-scrollable';
			}
			
			if ($att_class != null)
			{
				$class .= ' ' . $att_class;
			}
		
			$tab_def = array('title' => '', 'tip' => '', 'active' => '');
			$count = count($tabs);
			
			if ($tab_selected == null)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$tab = $tabs[$i];
					$tab_atts = shortcode_parse_atts($tab[3]);
					$tab_atts = shortcode_atts($tab_def, $tab_atts);
				
					$tab_id = $id . '-' . $i;
					
					if ($tab_atts['active'] == 1 || $tab_atts['active'] == 'yes' || $tab_atts['active'] == 'true' || $tab_atts['active'] === true)
					{
						$tab_selected = $tab_id;
						
						break;
					}
				}
			}
			
			for ($i = 0; $i < $count; $i++)
			{
				$tab = $tabs[$i];
				$tab_atts = shortcode_parse_atts($tab[3]);
				$tab_atts = shortcode_atts($tab_def, $tab_atts);
				
				$tab_id = $id . '-' . $i;
				$tab_href = null;
				$tab_head = null;
				$tab_head_class = null;
				$tab_body = null;
				$tab_body_class = null;
				$tab_body_style = null;
				$tab_return = false;
				
				if ($tab_selected == null && $i == 0)
				{
					$tab_selected = $tab_id;
				}
				
				if ($is_dynamic)
				{
					$tab_href = get_permalink() . '?snvdstt=' . $tab_id . '#' . $tab_id;
					
					if ($is_dynamic_load && strtolower($tab_selected) == strtolower($tab_id))
					{
						$tab_return = true;
					}
				}
				else
				{
					if ($is_imitate)
					{
						$tab_href = get_permalink() . '?snvdstt=' . $tab_id;
					}
					
					$tab_href .= '#' . $tab_id;
				}
				
				if (!$is_dynamic || strtolower($tab_selected) == strtolower($tab_id))
				{
					$tab_body = isset($tab[5]) ? $tab[5] : null;
					$tab_body = synved_shortcode_do_shortcode($tab_body, 'tabs');
				}
				
				if (strtolower($tab_selected) == strtolower($tab_id))
				{
					$tab_head_class .= ' ui-tabs-selected ui-tabs-active ui-state-active';
					$tab_body_class .= ' ui-tabs-panel ui-widget-content ui-corner-bottom';
				}
				else
				{
					$tab_head_class .= ' ui-state-default';
					$tab_body_class .= ' ui-tabs-panel ui-widget-content ui-corner-bottom';
					
					if ($is_imitate)
					{
						$tab_body_class .= ' ui-tabs-hide';
					}
				}
			
				$sanitized_title = sanitize_title($tab_atts['title']);
				$tab_head_class .= ' synved-tab-title-' . $sanitized_title;
				$tab_body_class .= ' synved-tab-title-' . $sanitized_title;
				
				if ($tab_return)
				{
					while (ob_get_level() > 0) 
					{
						ob_end_clean();
					}
					
					echo $tab_body;
					
					exit();
				}
				
				$tab_head = '<li class="tab-title ui-corner-top' . $tab_head_class . '"><a title="' . $tab_atts['tip'] . '" href="' . $tab_href . '">' . $tab_atts['title'] . '</a></li>';
				
				$tab_body_css = null;
				
				if ($tab_body_style != null)
				{
					foreach ($tab_body_style as $style_name => $style_value)
					{
						$tab_body_css .= $style_name . ':' . $style_value . ';';
					}
				}
				
				if ($tab_body_css != null)
				{
					$tab_body_css = ' style="' . esc_attr($tab_body_css) . '"';
				}
				
				$tab_body = '<div class="tab-body' . $tab_body_class . '" id="' . $tab_id . '"' . $tab_body_css . '>' . $tab_body . '</div>';
				
				$heads .= $tab_head;
				
				$bodies .= $tab_body;
			}
			
			return '<div class="snvdshc"><div class="synved-tab-list synved-tab-list-nojs ui-tabs ui-widget ui-widget-content ui-corner-all' . $class . '" id="' . $id . '"><ul class="ui-tabs-nav ui-helper-clearfix ui-helper-reset ui-widget-header ui-corner-all">' . $heads . '</ul>' . $bodies . '</div></div>';
		}
	}
	
	return null;
}

function synved_shortcode_do_tab($atts, $content = null, $code = '')
{
	// Just a placeholder, never called
	
	return null;
}

function synved_shortcode_do_sections($atts, $content = null, $code = '')
{
	global $synved_shortcode;
	
	$atts_def = array('imitate' => null, 'dynamic' => false, 'scroll' => true, 'class' => '', 'collapse' => false);
	$atts = shortcode_atts($atts_def, $atts);
	$is_imitate = $atts['imitate'];
	$is_dynamic = $atts['dynamic'];
	$is_scroll = $atts['scroll'];
	$att_class = $atts['class'];
	$collapse = $atts['collapse'];
	$is_dynamic_load = isset($_GET['synved_dynamic_load']);
	$section_selected = isset($_GET['snvdsts']) ? $_GET['snvdsts'] : null;
	
	if ($is_imitate === null)
	{
		$is_imitate = $is_dynamic;
	}
	
	$is_imitate = in_array($is_imitate, array('yes', 'true'));
	$is_dynamic = in_array($is_dynamic, array('yes', 'true'));
	$is_scroll = in_array($is_scroll, array('yes', 'true'));
	
	$pattern = get_shortcode_regex();
	$matches = array();
	
	if (preg_match_all('/' . $pattern . '/ms', $content, $matches, PREG_SET_ORDER) > 0)
	{
		$sections = array();
		
		foreach ($matches as $match)
		{
			if (isset($match[2]) && in_array($match[2], array('section', 'synved-section', 'synved_section')))
			{
				$sections[] = $match;
			}
		}
	
		if (isset($sections[0]))
		{
			if (!isset($synved_shortcode['instance']['sections']))
			{
				$synved_shortcode['instance']['sections'] = array('count' => 1);
			}
			else
			{
				$synved_shortcode['instance']['sections']['count'] += 1;
			}

			$id = 'synved-sections-' . $synved_shortcode['instance']['sections']['count'];
			$class = null;
			$sections_out = null;
			
			if ($is_dynamic)
			{
				$class .= ' synved-content-dynamic';
			}
			
			if ($is_scroll)
			{
				$class .= ' synved-content-scrollable';
			}
			
			if ($att_class != null)
			{
				$class .= ' ' . $att_class;
			}
			
			if (in_array($collapse, array('allow', 'always')))
			{
				$class .= ' synved-sections-collapse';
			}
			
			if ($collapse == 'always')
			{
				$class .= ' synved-sections-collapse-always';
			}
			
			$section_def = array('title' => null, 'tip' => null, 'active' => null);
			$count = count($sections);
			
			for ($i = 0; $i < $count; $i++)
			{
				$section = $sections[$i];
				$section_atts = shortcode_parse_atts($section[3]);
				$section_atts = shortcode_atts($section_def, $section_atts);
				
				$section_id = $id . '-' . $i;
				$section_href = null;
				$section_body = isset($section[5]) ? $section[5] : null;
				$section_body = synved_shortcode_do_shortcode($section_body, 'sections');
				$section_class = null;
				$section_head_class = null;
				$section_tip = $section_atts['tip'];
				
				// XXX incomplete, this will cause SEO problems, need to provide server side selection first
				// $section_href = get_permalink() . '?snvdsts=' . $section_id . '#' . $section_id;
				$section_href = '#' . $section_id;
				
				if ($i % 2 == 0)
				{
					$section_class .= ' synved-item-odd';
				}
				else
				{
					$section_class .= ' synved-item-even';
				}
				
				$section_head_class = $section_class;
				
				if ($section_class != null)
				{
					$section_class = ' class="' . trim($section_class) . '"';
				}
				
				//if ($section_head_class != null)
				{
					$section_head_class = ' class="section-title ' . trim($section_head_class) . '"';
				}
				
				if ($section_tip != null)
				{
					$section_tip = ' title="' . esc_attr($section_tip) . '"';
				}
	
				$sections_out .= '<h4' . $section_head_class . '><a href="' . $section_href . '"' . $section_tip . '>' . $section_atts['title'] . '</a></h4><div' . $section_class . '>' . $section_body . '</div>';
			}
			
			return '<div class="snvdshc"><div class="synved-section-list synved-section-list-nojs' .  esc_attr($class) . '" id="' . esc_attr($id) . '">' . $sections_out . '</div></div>';
		}
	}
	
	return null;
}

function synved_shortcode_do_section($atts, $content = null, $code = '')
{
	// Just a placeholder, never called
	
	return null;
}

function synved_shortcode_do_button($atts, $content = null, $code = '')
{
	$atts_def = array('tip' => null, 'type' => 'normal', 'link' => null, 'icon' => null, 'icon2' => null, 'tag' => null);
	$atts = shortcode_atts($atts_def, $atts);
	
	$type = $atts['type'];
	$link = $atts['link'];
	$icon = $atts['icon'];
	$icon2 = $atts['icon2'];
	$tag = trim($atts['tag']);
	$class = null;
	$click = null;
	
	switch ($type)
	{
		case 'download':
		{
			if ($icon == null)
			{
				$icon = 'arrowthickstop-1-s';
			}
			
			break;
		}
		case 'purchase':
		{
			if ($icon == null)
			{
				$icon = 'cart';
			}
			
			break;
		}
	}
	
	if ($type != 'normal')
	{
		$class .= ' synved-button-type-' . $type;
	}
	
	if ($link != null)
	{
		$click = ' onclick="window.location = \'' . esc_attr($link) . '\'"';
	}
	
	if ($tag != null)
	{
		$class .= ' synved-button-tagged';
		
		$tag = '<span class="synved-button-tag">' . $tag . '</span>';
	}
	
	return '<span class="snvdshc"><button class="synved-button' . $class . '" title="' . esc_attr($atts['tip']) . '"' . $click . '>' . $content . $tag . '</button><div style="display:none" class="button-info"><span class="icon">' . $icon . '</span><span class="icon2">' . $icon2 . '</span></div></span>';
}

function synved_shortcode_do_list($atts, $content = null, $code = '')
{
	$atts_def = array('type' => null, 'icon' => null);
	$atts = shortcode_atts($atts_def, $atts);
	
	$pattern = get_shortcode_regex();
	$matches = array();
	
	if (preg_match_all('/' . $pattern . '/ms', $content, $matches, PREG_SET_ORDER) > 0)
	{
		$items = array();
		
		foreach ($matches as $match)
		{
			if (isset($match[2]) && in_array($match[2], array('item', 'synved-item', 'synved_item')))
			{
				$items[] = $match;
			}
		}
	
		if (isset($items[0]))
		{
			if (!isset($synved_shortcode['instance']['list']))
			{
				$synved_shortcode['instance']['list'] = array('count' => 1);
			}
			else
			{
				$synved_shortcode['instance']['list']['count'] += 1;
			}
		
			$styles = array('roman' => 'upper-roman', 'alpha' => 'lower-alpha', 'latin' => 'lower-latin', 'icon' => 'none');
			$type = $atts['type'];
			$icon = $atts['icon'];
			
			if ($icon != null)
			{
				$type = 'icon';
			}
			
			$tag = in_array($type, array('decimal', 'roman', 'lower-roman', 'upper-roman', 'alpha', 'lower-alpha', 'upper-alpha')) ? 'ol' : 'ul';
			$id = 'synved-list-' . $synved_shortcode['instance']['list']['count'];
			$class = ' synved-item-list-' . $type;
			$style_type = isset($styles[$type]) ? $styles[$type] : $type;
			
			$items_out = null;
			$count = count($items);
			
			for ($i = 0; $i < $count; $i++)
			{
				$item = $items[$i];
				$item_def = array('tip' => null, 'icon' => null);
				$item_atts = shortcode_parse_atts($item[3]);
				$item_atts = shortcode_atts($item_def, $item_atts);
				$item_body = isset($item[5]) ? $item[5] : null;
				$item_body = synved_shortcode_do_shortcode($item_body, 'list');
				
				$item_tip = $item_atts['tip'] ? (' title="' . $item_atts['tip'] . '"') : null;
				$item_icon = $item_atts['icon'];
				$item_class = null;
				
				if ($item_icon == null)
				{
					$item_icon = $icon;
				}
	
				if ($item_icon != null)
				{
					$item_icon = '<span class="ui-icon ui-icon-' . $item_icon . '"></span>';
				}
				
				if ($i % 2 == 0)
				{
					$item_class .= ' synved-item-odd';
				}
				else
				{
					$item_class .= ' synved-item-even';
				}
				
				if ($item_class != null)
				{
					$item_class = ' class="' . trim($item_class) . '"';
				}
	
				$items_out .= '<li' . $item_tip . $item_class . '>' . $item_icon . $item_body . '</li>';
			}
			
			return '<div class="snvdshc"><' . $tag . ' class="synved-item-list synved-item-list-nojs' . $class . '" id="' . $id . '" style="list-style-type:' . $style_type . ';">' . $items_out . '</' . $tag . '></div>';
		}
	}
	
	return null;
}

function synved_shortcode_do_item($atts, $content = null, $code = '')
{
	// Just a placeholder, never called
	
	return null;
}

function synved_shortcode_do_column($atts, $content = null, $code = '', $type = null)
{
	$flows = array('start', 'end', 'hold', 'break', 'none');
	$atts_def = array('extend' => 'no', 'flow' => 'none', 'width' => null, 'style' => null, 'class' => null, 'css' => null, 'cssContent' => null);
	$atts = shortcode_atts($atts_def, $atts);
	
	$content = trim($content);
	$typeclass = 'synved-column-' . $type;
	$style = $atts['style'];
	$class = $atts['class'];
	$flow = $atts['flow'];
	$flow = in_array($flow, $flows) ? $flow : 'none';
	$width = $atts['width'];
	$css = $atts['css'];
	$css_content = $atts['cssContent'];
	
	if ($class != null)
	{
		$class .= ' ';
	}
	
	if ($style != null)
	{
		$style = explode(',', $style);
	}
	
	$class .= str_replace(array(' ', '_'), '-', $typeclass);
	
	if ($atts['extend'] == 'yes')
	{
		$class .= ' synved-column-extend';
	}
	
	if ($flow != 'none')
	{
		$class .= ' synved-column-flow-' . $flow;
	}
	
	if ($style != null)
	{
		foreach ($style as $style_item)
		{
			$style_item = sanitize_title($style_item);
			
			if ($style_item != null)
			{
				$class .= ' synved-column-style-' . $style_item;
			}
		}
	}
	
	if ($width != null)
	{
		if (is_numeric($with))
		{
			$width = ((int) $width) . 'px';
		}
		
		$css .= 'width:' . $width . ';';
	}
	
	if ($css != null)
	{
		$css = ' style="' . esc_attr($css) . '"';
	}
	
	if ($css_content != null)
	{
		$css_content = ' style="' . esc_attr($css_content) . '"';
	}
	
	return '<div class="snvdshc"><div class="synved-content-column' . ($class ? (' ' . esc_attr($class)) : null) . '"' . $css . '><div class="synved-column-content"' . $css_content . '>' . synved_shortcode_do_shortcode($content, 'column_' . $type) . '</div></div></div>';
}

function synved_shortcode_column_register($type, $default = null)
{
	$name = $type;
	$cb = create_function('$atts, $content = null, $code = \'\'', 'return synved_shortcode_do_column($atts, $content, $code, \'' . $type . '\');');
	
	synved_shortcode_add($name, $cb, false, synved_shortcode_item_label_create('column_' . $type));
	
	if ($default == null)
	{
		$default = '[%%_synved_name%%]' . __('Your Content Here', 'synved-shortcode') . '[/%%_synved_name%%]';
	}
	
	synved_shortcode_item_group_set($name, 'layout-column');
	
	if ($default != null)
	{
		synved_shortcode_item_default_set($name, $default);
	}
	
	$type_label = str_replace(array('-', '_'), ' ', $type);
	$desc = null;
	
	switch ($type)
	{
		case 'full':
		{
			$desc = 'the full width';
			
			break;
		}
		case 'third':
		case 'fourth':
		{
			$desc = 'a ' . $type_label . ' of the width';
			
			break;
		}
		default:
		{
			$desc = $type_label . ' the width';
			
			break;
		}
	}
	
	$help = array(
		'tip' => __(sprintf('Creates a layout element that forces its contents to be contained in %1$s of the post', $desc), 'synved-shortcode'),
		'parameters' => array(
			'extend' => __('Forces some contents (like tables) inside of the layout element to extend to the full width of the element itself', 'synved-shortcode'), 
			'flow' => __('Determines how the layout element "flows" with other surrounding elements, possible values are start,hold,end,break. For examples on how this works you can <a href="http://wpdemo.synved.com/stripefolio/shortcodes/layout/">look here</a>', 'synved-shortcode'),
			'width' => __('Specify an explicit width to use instead of the default', 'synved-shortcode'),
			'style' => __('A comma separated list of named styles for the item, the only supported value right now is "flat" which removes default margins/alignments from the column', 'synved-shortcode'),
			'class' => __('Specify additional classes to use for the column shortcode', 'synved-shortcode'),
			'css' => __('Specify custom CSS properties to use on the column shortcode', 'synved-shortcode'),
			'cssContent' => __('Specify custom CSS properties to use on the column content', 'synved-shortcode'),
		)
	);
	
	synved_shortcode_item_help_set($name, $help);
}

function synved_shortcode_do_box($atts, $content = null, $code = '', $type = null)
{
	$atts_def = array();
	$atts = shortcode_atts($atts_def, $atts);
	
	$typeclass = 'synved-box-' . $type;
	$class = $typeclass;
	
	return '<div class="snvdshc"><div class="synved-box-message' . ($class ? (' ' . $class) : null) . '">' . synved_shortcode_do_shortcode($content, 'box_' . $type) . '</div></div>';
}

function synved_shortcode_box_register($type, $default = null)
{
	$name = $type;
	$type_label = synved_shortcode_item_label_create($type);
	$cb = create_function('$atts, $content = null, $code = \'\'', 'return synved_shortcode_do_box($atts, $content, $code, \'' . $type . '\');');
	
	synved_shortcode_add($type, $cb, false, __('Box', 'synved-shortcode') . ' ' . $type_label);
	
	if ($default == null)
	{
		$default = '[%%_synved_name%%]' . $type_label . ' ' . __('Message', 'synved-shortcode') . '[/%%_synved_name%%]';
	}
	
	synved_shortcode_item_group_set($name, 'message-box');
	
	if ($default != null)
	{
		synved_shortcode_item_default_set($name, $default);
	}
	
	$type_label = str_replace(array('-', '_'), ' ', $type);
	$desc = null;
	
	switch ($type)
	{
		case 'error':
		case 'info':
		{
			$desc = __('an', 'synved-shortcode') . ' ' . $type_label . ' ' . __('message', 'synved-shortcode');
			
			break;
		}
		default:
		{
			$desc = __('a', 'synved-shortcode') . ' ' . $type_label . ' ' . __('message', 'synved-shortcode');
			
			break;
		}
	}
	
	$help = array(
		'tip' => __('Creates a message box displaying', 'synved-shortcode') . ' ' . $desc
	);
	
	synved_shortcode_item_help_set($name, $help);
}

function synved_shortcode_do_link($atts, $content = null, $code = '', $type = null)
{
	$atts_def = array('template' => null, 'display' => null);
	$link_atts = shortcode_atts($atts_def, $atts);
	$template = $link_atts['template'];
	$display = $link_atts['display'];
	
	$item = synved_shortcode_data_get_display_item($atts, $type);
	$link = $item['link'];
	$tip = $item['tip'];
	$class = $item['class'];
	$object = $item['object'];
	$body = trim($content);
	
	if ($link != null || $body != null)
	{
		if ($link == null)
		{
			$link = '#';
		}
		
		if ($class != null)
		{
			$class = ' ' . $class;
		}
		
		$class = 'synved-link synved-link-type-' . $type . $class;
		
		if ($template != null)
		{
			$class .= ' synved-link-template-' . $template;
		}
		
		if ($body == null)
		{
			if (isset($item['title']))
			{
				$body = $item['title'];
			}
			else
			{
				$body = 'Link';
			}
		}
		
		if ($tip == $body)
		{
			$tip = null;
		}
		
		$template_markup = null;
		
		switch ($template)
		{
			case null:
			case 'default':
			{
				$template_markup = '<a class="synved-link-anchor %%class%%" href="%%link%%"%%tip_attribute%%>%%body%%</a>';
				
				break;
			}
			case 'url':
			{
				$template_markup = '%%link%%';
				
				break;
			}
			case 'card':
			case 'card-full':
			{
				$template_abstract = null;
				
				if ($template == 'card-full')
				{
					$class .= ' synved-link-template-card';
					$template_abstract = '%%abstract_markup%%';
				}
				
				$template_markup = '<a class="synved-link-anchor %%class%%" href="%%link%%"%%tip_attribute%%>%%item_thumbnail%%<div class="synved-link-body" style="height:%%item_thumbnail_height%%px;overflow:hidden;">%%body%%' . $template_abstract . '</div></a>';
				
				break;
			}
			case 'custom':
			{
				$template_markup = $body;
				$body = null;
				
				break;
			}
		}
		
		$item['link'] = $link;
		$item['tip'] = $tip;
		$item['class'] = $class;
		$item['body'] = $body;
		// XXX backward compatibility
		$item['item'] = $item;
		
		$template_markup = synved_shortcode_item_template_expand($template_markup, $item);
		$template_markup = synved_shortcode_do_shortcode($template_markup, 'link_' . $type, $object->ID);
		
		$out = $template_markup;
		
		return '<span class="snvdshc">' . $out . '</span>';
	}
	
	return null;
}

function synved_shortcode_link_register($type, $default = null)
{
	$name = 'link_' . $type;
	$cb = create_function('$atts, $content = null, $code = \'\'', 'return synved_shortcode_do_link($atts, $content, $code, \'' . $type . '\');');
	
	synved_shortcode_add($name, $cb, false, __('Link', 'synved-shortcode') . ' ' . synved_shortcode_item_label_create($type));
	
	if ($default == null)
	{
		if ($type == 'post')
		{
			$default = '[%%_synved_name%% id="1"]';
		}
		else if ($type == 'common')
		{
			$default = '[%%_synved_name%% name="home"]' . __('Home Page', 'synved-shortcode') . '[/%%_synved_name%%]';
		}
		else
		{
			$default = '[%%_synved_name%% name="unique-name"]' . __('Link Text', 'synved-shortcode') . '[/%%_synved_name%%]';
		}
	}
	
	synved_shortcode_item_group_set($name, 'link-content');
	
	if ($default != null)
	{
		synved_shortcode_item_default_set($name, $default);
	}
	
	$type_label = str_replace(array('-', '_'), ' ', $type);
	$desc = $type_label;
	$params = array(
		'id' => __('Specify the %1$s by its unique numeric ID, has priority over name/slug/title', 'synved-shortcode'),
		'name' => __('Specify the %1$s by its unique name, has priority over title', 'synved-shortcode'),
		'slug' => __('Specify the %1$s by its unique slug, has priority over title', 'synved-shortcode'),
		'title' => __('Specify the %1$s by its title', 'synved-shortcode'),
	);
	
	switch ($type)
	{
		case 'post':
		{
			$desc .= ' or custom post type';
			$params['post_type'] = __('Specify the post\'s custom post type or comma-separated list of post types', 'synved-shortcode');
			
			break;
		}
		case 'term':
		{
			$params['taxonomy'] = __('Specify the custom taxonomy name to link to', 'synved-shortcode');
			
			break;
		}
		case 'user':
		{
			$params['email'] = __('Specify the user by his e-mail, has lower priority over the other parameters', 'synved-shortcode');
			
			break;
		}
		case 'common':
		{
			$type_label = 'common built-in page';
			$desc = 'common built-in page';
			
			if (substr($params['name'], -1) != '.')
			{
				$params['name'] .= '.';
			}
			
			if (substr($params['slug'], -1) != '.')
			{
				$params['slug'] .= '.';
			}
			
			$params['name'] .= ' ' . __('Can be one of home, site, admin, network_home, network_site, network_admin, content, plugins, upload, upload_base, template_base, stylesheet, stylesheet_base', 'synved-shortcode');
			$params['slug'] .= ' ' . __('This is the same as the "name" parameter.', 'synved-shortcode');
			
			$params['path'] = __('Specify what path to append to the built-in link URL, works for home, site, admin, network_home, network_site, network_admin, content, plugins', 'synved-shortcode');
			$params['scheme'] = __('Specify what scheme to use for the built-in link URL', 'synved-shortcode');
			
			break;
		}
	}
	
	$params['size'] = __('Specify what size to select for the thumbnail, can be named size or numeric, e.g. "thumbnail" or "100x60"', 'synved-shortcode');
	$params['template'] = esc_html(__('Specify what template to use to display the link, possible values are default,url,card,card-full,custom. You can use template %%%%tags%%%%, where "tags" can be link, tip, abstract, class, body, item_thumbnail_src, item_thumbnail_width, item_thumbnail_height, item_image_link, query_id, query_name and much more', 'synved-shortcode'));
	$params['edit'] = __('Specify how to edit the URL for the link, you can add parameters in the form of name=value,name2=value2 or remove them using -name,-name2', 'synved-shortcode');
	
	//$desc = __('a', 'synved-shortcode') . ' ' . $desc;
	
	foreach ($params as $param_name => $param_tip)
	{
		$params[$param_name] = sprintf($param_tip, $type_label);
	}
	
	$help = array(
		'tip' => sprintf(__('Creates a link to a %1$s on your WordPress site, optionally automatically displaying the title of the %1$s (just remove the "Link Text")', 'synved-shortcode'), $desc),
		'parameters' => $params
	);
	
	synved_shortcode_item_help_set($name, $help);
}

function synved_shortcode_add($name, $cb, $internal = false, $label = null, $default = null)
{
	global $synved_shortcode;
	
	$name_alt = synved_shortcode_item_name_sanitize($name);
	$full_name_alt = 'synved_' . $name_alt;
	
	if (!isset($synved_shortcode['list'][$name]))
	{
		add_shortcode($full_name_alt, $cb);
		add_shortcode($name_alt, $cb);
	
		if ($label == null)
		{
			$label = synved_shortcode_item_label_create($name);
		}
	
		if ($default == null)
		{
			$default = '[' . $name_alt . ']';
		}
	
		$synved_shortcode['list'][$name] = array('callback' => $cb, 'name_alt' => $name_alt, 'label' => $label, 'group' => null, 'internal' => $internal, 'default' => $default, 'help' => null, 'preset_list' => array());
	}
}

function synved_shortcode_list()
{
	global $synved_shortcode;
	
	return $synved_shortcode['list'];
}

function synved_shortcode_register_list()
{
	synved_shortcode_add('tabs', 'synved_shortcode_do_tabs');
	synved_shortcode_add('tab', 'synved_shortcode_do_tab', true);
	synved_shortcode_item_default_set('tabs', 
'[%%_synved_name%%]
[%%_synved_name_tab%% title="Tab 1"]
Tab Content 1.
[/%%_synved_name_tab%%]
[%%_synved_name_tab%% title="Tab 2"]
Tab Content 2.
[/%%_synved_name_tab%%]
[/%%_synved_name%%]');
	synved_shortcode_item_help_set('tabs', array(
		'tip' => __('Creates a list of SEO-friendly tabs that work with or without JavaScript', 'synved-shortcode'),
		'parameters' => array(
			'class' => __('Only for [tabs] element, specify a custom CSS class for the main tabs container', 'synved-shortcode'),
			'imitate' => __('Only for [tabs] element, imitate usual tab behaviour when JavaScript is disabled, i.e. hiding inactive tabs and creating actual links that will open those tabs by reloading the page, use imitate="yes" (Note: if set to "yes" this could damage SEO for the page)', 'synved-shortcode'),
			'title' => __('Only for [tab] element, specify the title of the tab', 'synved-shortcode'),
			'tip' => __('Only for [tab] element, specify a tooltip to show when hovering the tab with the mouse', 'synved-shortcode'),
			'active' => __('Only for [tab] element, specify whether the tab is the active tab, use active="yes"', 'synved-shortcode')
		)
	));

	synved_shortcode_add('sections', 'synved_shortcode_do_sections');
	synved_shortcode_add('section', 'synved_shortcode_do_section', true);
	synved_shortcode_item_default_set('sections', 
'[%%_synved_name%%]
[%%_synved_name_section%% title="Section 1"]
<p style="margin:5px 0;padding:0;">
Section Content 1.
</p>
[/%%_synved_name_section%%]
[%%_synved_name_section%% title="Section 2"]
<p style="margin:5px 0;padding:0;">
Section Content 2.
</p>
[/%%_synved_name_section%%]
[/%%_synved_name%%]');
	synved_shortcode_item_help_set('sections', array(
		'tip' => __('Creates a list of exclusive sections, also called accordions', 'synved-shortcode'),
		'parameters' => array(
			'class' => __('Only for [sections] element, specify a custom CSS class for the main sections container', 'synved-shortcode'),
			'collapse' => __('Only for [sections] element, determines if all sections can be collapsed at the same time, possible values are "allow" or "always" where "always" will collapse them all by default', 'synved-shortcode'),
			'title' => __('Only for [section] element, specify the title of the section', 'synved-shortcode'),
			'tip' => __('Only for [section] element, specify a tooltip to show when hovering the section with the mouse', 'synved-shortcode'),
			//'active' => __('Only for [section] element, specify whether the tab is the active section, use active="yes"', 'synved-shortcode')
		)
	));

	synved_shortcode_add('button', 'synved_shortcode_do_button');
	synved_shortcode_item_default_set('button',
'[%%_synved_name%% icon=heart]My Button[/%%_synved_name%%]');
	synved_shortcode_item_help_set('button', array(
		'tip' => __('Creates a nice-looking button', 'synved-shortcode'),
		'parameters' => array(
			//'type' => __('Specify the type of button being created', 'synved-shortcode'),
			'type' => __('Specify a custom type of default button, possible values are download,purchase', 'synved-shortcode'),
			'link' => __('Specify a link to open when clicking on the button with the mouse', 'synved-shortcode'),
			'icon' => __('Specify an icon to display on the left of the button, check the <a target="_blank" href="http://synved.com/blog/help/tutorial/wordpress-shortcodes-icons/">list of icons</a>', 'synved-shortcode'),
			'icon2' => __('Specify an icon to display on the right of the button, check the <a target="_blank" href="http://synved.com/blog/help/tutorial/wordpress-shortcodes-icons/">list of icons</a>', 'synved-shortcode'),
			'tag' => __('Specify a tag to display on the top-right corner of the button', 'synved-shortcode'),
			'tip' => __('Specify a tooltip to show when hovering the button with the mouse', 'synved-shortcode'),
		)
	));

	synved_shortcode_add('list', 'synved_shortcode_do_list');
	synved_shortcode_add('item', 'synved_shortcode_do_item', true);
	synved_shortcode_item_default_set('list', 
'[%%_synved_name%% type=roman]
[%%_synved_name_item%%]Item 1[/%%_synved_name_item%%]
[%%_synved_name_item%%]Item 2[/%%_synved_name_item%%]
[%%_synved_name_item%%]Item 3[/%%_synved_name_item%%]
[/%%_synved_name%%]');
	synved_shortcode_item_help_set('list', array(
		'tip' => __('Creates a list of items in a layout', 'synved-shortcode'),
		'parameters' => array(
			'type' => __('Only for [list] element, specify a custom type, possible values are decimal,alpha,roman,latin,upper-alpha,lower-roman,upper-latin', 'synved-shortcode'),
			'icon' => __('Specify the default icon for [list], which overwrites <b>type</b>, or the individual icon for each [item], check the <a target="_blank" href="http://synved.com/blog/help/tutorial/wordpress-shortcodes-icons/">list of icons</a>', 'synved-shortcode'),
			'tip' => __('Only for [item] element, specify a tooltip to show when hovering the item with the mouse', 'synved-shortcode'),
			//'active' => __('Only for [section] element, specify whether the tab is the active tab (use active=yes)', 'synved-shortcode')
		)
	));

	synved_shortcode_column_register('full');
	synved_shortcode_column_register('three_quarters');
	synved_shortcode_column_register('two_thirds');
	synved_shortcode_column_register('half');
	synved_shortcode_column_register('third');
	synved_shortcode_column_register('quarter');

	synved_shortcode_box_register('success');
	synved_shortcode_box_register('info');
	synved_shortcode_box_register('warning');
	synved_shortcode_box_register('error');

	synved_shortcode_link_register('post');
	synved_shortcode_link_register('page');
	synved_shortcode_link_register('media');
	synved_shortcode_link_register('category');
	synved_shortcode_link_register('tag');
	synved_shortcode_link_register('term');
	synved_shortcode_link_register('user');
	synved_shortcode_link_register('common');
}

?>
