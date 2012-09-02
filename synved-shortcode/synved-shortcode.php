<?php
/*
Module Name: Synved Shortcode
Description: A complete set of WordPress shortcodes to add beautiful and useful elements that will spice up your site
Author: Synved
Version: 1.4.7
Author URI: http://synved.com/

LEGAL STATEMENTS

COPYRIGHT
All documents, text, questions, references, images, audio, programs, source code or other materials whatsoever contained in, or supplied are protected by copyright of the respective copyright holders.

Except as explicitly allowed under each specific copyright or license, these materials may not be reproduced in whole or in part, in any form or by any means, including photocopy, electronic storage and retrieval, or translation into any other language without the express written consent of the copyright holder.

NO WARRANTY
All products, support, services, information and software are provided "as is" without warranty of any kind, express or implied, including, but not limited to, the implied warranties of fitness for a particular purpose, and non-infringement.

NO LIABILITY
In no event shall Synved Ltd. be liable to you or any third party for any direct or indirect, special, incidental, or consequential damages in connection with or arising from errors, omissions, delays or other cause of action that may be attributed to your use of any product, support, services, information or software provided, including, but not limited to, lost profits or lost data, even if Synved Ltd. had been advised of the possibility of such damages.
*/


define('SYNVED_SHORTCODE_LOADED', true);
define('SYNVED_SHORTCODE_VERSION', 100040007);
define('SYNVED_SHORTCODE_VERSION_STRING', '1.4.7');

define('SYNVED_SHORTCODE_ADDON_PATH', str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, dirname(__FILE__) . '/addons'));

include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'synved-shortcode-setup.php');


$synved_shortcode = array();


function synved_shortcode_version()
{
	return SYNVED_SHORTCODE_VERSION;
}

function synved_shortcode_version_string()
{
	return SYNVED_SHORTCODE_VERSION_STRING;
}

function synved_shortcode_data_get_display_item($atts, $type = null)
{
	$atts_def = array('id' => null, 'name' => null, 'slug' => null, 'title' => null, 'size' => null, 'email' => null, 'post_type' => null, 'taxonomy' => null, 'edit' => null, 'tip' => null, 'abstract' => null, 'class' => null);
	$atts = shortcode_atts($atts_def, $atts);
	
	if ($type == null)
	{
		$type = 'post';
	}
	
	$id = $atts['id'];
	$name = $atts['name'];
	$slug = $atts['slug'];
	$title = $atts['title'];
	$size = $atts['size'];
	$email = $atts['email'];
	$post_type = $atts['post_type'];
	$taxonomy = $atts['taxonomy'];
	$edit = $atts['edit'];
	$tip = $atts['tip'];
	$abstract = $atts['abstract'];
	$class = $atts['class'];

	if ($size != null)
	{
		$size_parts = explode(',', $size);
		
		if (count($size_parts) > 1)
		{
			$size = $size_parts;
		}
	}
	
	$object = null;
	$item = array();
	
	switch ($type)
	{
		case 'post':
		case 'page':
		case 'media':
		{
			if ($post_type == null)
			{
				if ($type == 'media')
				{
					$post_type = 'attachment';
				}
			}
			else
			{
				$post_type = explode(',', $post_type);
				
				if (count($post_type) == 1)
				{
					$post_type = $post_type[0];
				}
			}
			
			if ($object == null && $id != null)
			{
				$object = get_post($id);
			}
		
			if ($name == null && $slug != null)
			{
				$name = $slug;
			}
			
			if ($object == null && $name != null)
			{
				if ($post_type == null)
				{
					$post_type = get_post_types();
					
					unset($post_type['revision']);
					unset($post_type['nav_menu_item']);
				}
				
				$name_key = $type == 'page' ? 'pagename' : 'name';
				$posts = get_posts(array($name_key => $name, 'numberposts' => 1, 'post_type' => $post_type));
			
				if ($posts != null)
				{
					$object = $posts[0];
				}
			}
			
			if ($object == null && $title != null)
			{
				if ($post_type == null)
				{
					$post_type = $type;
				}
				
				if (is_array($post_type))
				{
					global $wpdb;
					
					$post_type = array_values($post_type);
					$count = count($post_type);
					$params = array($title);
					$params = array_merge($params, $post_type);
					$db_query = 'SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type IN (' . str_repeat('%s,', $count - 1) . '%s)';
					$page = $wpdb->get_var($wpdb->prepare($db_query, $params));
					
					if ($page)
					{
						$object = get_page($page, OBJECT);
					}
				}
				else
				{
					$object = get_page_by_title($title, OBJECT, $post_type);
				}
			}
			
			if ($object != null)
			{
				$item['id'] = $object->ID;
				$item['title'] = apply_filters('the_title', $object->post_title, $object->ID);
				$item['link'] = apply_filters('the_permalink', get_permalink($object->ID), $object->ID);
				$item['tip'] = $item['title'];
				$item['abstract'] = apply_filters('the_excerpt', $object->post_excerpt, $object->ID);
				
				$thumb_id = $type == 'media' ? $object->ID : get_post_thumbnail_id($object->ID);
				
				if ($thumb_id != null)
				{
					if ($size == null)
					{
						$size = 'thumbnail';
					}
					
					$thumb = wp_get_attachment_image_src($thumb_id, $size);
					
					if ($thumb != null)
					{
						$item['thumbnail_src'] = $thumb[0];
						$item['thumbnail_width'] = $thumb[1];
						$item['thumbnail_height'] = $thumb[2];
						$item['thumbnail'] = '<img class="synved-shortcode-thumbnail" src="' . esc_url($item['thumbnail_src']) . '" width="' . $item['thumbnail_width'] . '" height="' . $item['thumbnail_height'] . '" />';
					}
				}
			}
			
			break;
		}
		case 'category':
		case 'tag':
		case 'term':
		{
			if ($taxonomy == null)
			{
				$taxonomy = $type == 'tag' ? 'post_tag' : $type;
			}
			
			if ($object == null && $id != null)
			{
				$object = get_term_by('id', $id, $taxonomy);
			}
		
			if ($object == null && $slug != null)
			{
				$object = get_term_by('slug', $slug, $taxonomy);
			}
		
			if ($name == null && $title != null)
			{
				$name = $title;
			}
		
			if ($object == null && $name != null)
			{
				$object = get_term_by('name', $name, $taxonomy);
			}
			
			if ($object != null)
			{
				$object = sanitize_term($object, $taxonomy);
				
				$item['id'] = $object->term_id;
				$item['title'] = $object->name;
				$item['link'] = get_term_link($object);
				$item['tip'] = $object->description;
				$item['abstract'] = $object->description;
			}
			
			break;
		}
		case 'user':
		{
			if ($object == null && $id != null)
			{
				$object = get_user_by('id', $id);
			}
		
			if ($object == null && $slug != null)
			{
				$object = get_user_by('slug', $slug);
			}
		
			if ($name == null && $title != null)
			{
				$name = $title;
			}
		
			if ($object == null && $name != null)
			{
				$object = get_user_by('login', $name);
			}
		
			if ($object == null && $email != null)
			{
				$object = get_user_by('email', $email);
			}
			
			if ($object != null)
			{
				$item['id'] = $object->ID;
				$item['title'] = $object->display_name;
				$item['link'] = get_author_posts_url($object->ID);
				$item['tip'] = null;
				$item['abstract'] = $object->user_description;
				
				if (is_array($size))
				{
					$size = (int) $size[0];
				}
				
				if ($size == null)
				{
					$size = (int) intval(get_option('thumbnail_size_w'));
				}
				
				$thumb = get_avatar($object->ID, $size);
				
				if ($thumb != null)
				{
					$match = null;
					preg_match('/src=("|\')(([^"\']|(?!\\1))+)\\1/i', $thumb, $match);
					
					$item['thumbnail_src'] = $match[2];
					$item['thumbnail_width'] = $size;
					$item['thumbnail_height'] = $size;
					$item['thumbnail'] = '<img class="synved-shortcode-thumbnail" src="' . esc_url($item['thumbnail_src']) . '" width="' . $item['thumbnail_width'] . '" height="' . $item['thumbnail_height'] . '" />';
				}
			}
			
			break;
		}
	}
	
	if ($item != null)
	{
		if ($edit != null)
		{
			$link = $item['link'];
			$edit_list = explode(',', $edit);
			
			if ($edit_list != null)
			{
				foreach ($edit_list as $edit_item)
				{
					$edit_item = trim($edit_item);
					$edit_parts = explode('=', $edit_item);
					$edit_name = $edit_parts[0];
					$edit_value = isset($edit_parts[1]) ? $edit_parts[1] : null;
				
					if ($edit_name[0] == '-')
					{
						$edit_name = substr($edit_name, 1);
						$link = remove_query_arg($edit_name, $link);
					}
					else
					{
						if ($edit_name[0] == '+')
						{
							$edit_name = substr($edit_name, 1);
						}
					
						$link = add_query_arg($edit_name, $edit_value, $link);
					}
				}
			}
			
			$item['link'] = $link;
		}
		
		if ($tip !== null)
		{
			$item['tip'] = $tip;
		}
		
		if ($abstract !== null)
		{
			$item['abstract'] = $abstract;
		}
		
		$item['class'] = $class;
		
		if ($object != null)
		{
			$item['object'] = $object;
		}
		
		$item['query'] = $atts;
		
		return apply_filters('synved_shortcode_data_get_display_item', $item);
	}
	
	return null;
}

function synved_shortcode_do_tabs($atts, $content = null, $code = '')
{
	global $synved_shortcode;
	
	$atts_def = array('dynamic' => false, 'scroll' => true, 'class' => '');
	$atts = shortcode_atts($atts_def, $atts);
	$is_dynamic = $atts['dynamic'];
	$is_scroll = $atts['scroll'];
	$att_class = $atts['class'];
	$is_dynamic_load = isset($_GET['synved_dynamic_load']);
	$tab_selected = isset($_GET['snvdstt']) ? $_GET['snvdstt'] : null;
	
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
					
					if ($tab_atts['active'] == 1 || $tab_atts['active'] == "true" || $tab_atts['active'] === true)
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
					$tab_href = get_permalink() . '?snvdstt=' . $tab_id . '#' . $tab_id;
				}
				
				if (!$is_dynamic || strtolower($tab_selected) == strtolower($tab_id))
				{
					$tab_body = isset($tab[5]) ? $tab[5] : null;
					$tab_body = do_shortcode($tab_body);
				}
				
				if (strtolower($tab_selected) == strtolower($tab_id))
				{
					$tab_head_class .= ' ui-tabs-selected ui-state-active';
					$tab_body_class .= ' ui-tabs-panel ui-widget-content ui-corner-bottom';
				}
				else
				{
					$tab_head_class .= '';
					$tab_body_class .= ' ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide';
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
				
				$tab_head = '<li class="tab-title ui-state-default ui-corner-top' . $tab_head_class . '"><a title="' . $tab_atts['tip'] . '" href="' . $tab_href . '">' . $tab_atts['title'] . '</a></li>';
				
				$tab_body = '<div class="tab-body' . $tab_body_class . '" id="' . $tab_id . '">' . $tab_body . '</div>';
				
				$heads .= $tab_head;
				
				$bodies .= $tab_body;
			}
			
			return '<div class="synved-tab-list synved-tab-list-nojs ui-tabs ui-widget ui-widget-content ui-corner-all' . $class . '" id="' . $id . '"><ul class="ui-tabs-nav ui-helper-clearfix ui-helper-reset ui-widget-header ui-corner-all">' . $heads . '</ul>' . $bodies . '</div>';
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
	
	$atts_def = array('dynamic' => false, 'scroll' => true, 'class' => '');
	$atts = shortcode_atts($atts_def, $atts);
	$is_dynamic = $atts['dynamic'];
	$is_scroll = $atts['scroll'];
	$att_class = $atts['class'];
	
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
			
			$sections_out = null;
			$count = count($sections);
			
			for ($i = 0; $i < $count; $i++)
			{
				$section = $sections[$i];
				$section_def = array('title' => null, 'tip' => null);
				$section_atts = shortcode_parse_atts($section[3]);
				$section_atts = shortcode_atts($section_def, $section_atts);
				$section_body = isset($section[5]) ? $section[5] : null;
				$section_body = do_shortcode($section_body);
				$section_class = null;
				$section_tip = $section_atts['tip'];
				
				if ($i % 2 == 0)
				{
					$section_class .= ' synved-item-odd';
				}
				else
				{
					$section_class .= ' synved-item-even';
				}
				
				if ($section_class != null)
				{
					$section_class = ' class="' . trim($section_class) . '"';
				}
				
				if ($section_tip != null)
				{
					$section_tip = ' title="' . esc_attr($section_tip) . '"';
				}
	
				$sections_out .= '<h4' . $section_class . '><a href="#"' . $section_tip . '>' . $section_atts['title'] . '</a></h4><div' . $section_class . '>' . $section_body . '</div>';
			}
			
			return '<div class="synved-section-list synved-section-list-nojs' .  esc_attr($class) . '" id="' . esc_attr($id) . '">' . $sections_out . '</div>';
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
	$tag = $atts['tag'];
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
	
	return '<button class="synved-button' . $class . '" title="' . esc_attr($atts['tip']) . '"' . $click . '>' . $content . $tag . '</button><div style="display:none" class="button-info"><span class="icon">' . $icon . '</span><span class="icon2">' . $icon2 . '</span></div>';
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
				$item_body = do_shortcode($item_body);
				
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
			
			return '<' . $tag . ' class="synved-item-list synved-item-list-nojs' . $class . '" id="' . $id . '" style="list-style-type:' . $style_type . ';">' . $items_out . '</' . $tag . '>';
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
	$atts_def = array('extend' => 'no', 'flow' => 'none', 'width' => null);
	$atts = shortcode_atts($atts_def, $atts);
	
	$content = trim($content);
	$typeclass = 'synved-column-' . $type;
	$class = $typeclass;
	$flow = $atts['flow'];
	$flow = in_array($flow, $flows) ? $flow : 'none';
	$width = $atts['width'];
	$css = null;
	
	if ($atts['extend'] == 'yes')
	{
		$class .= ' column-extend';
	}
	
	if ($flow != 'none')
	{
		$class .= ' synved-column-flow-' . $flow;
	}
	
	if ($width != null)
	{
		if (is_numeric($with))
		{
			$width = ((int) $width) . 'px';
		}
		
		$css .= ' style="width:' . $width . ';"';
	}
	
	return '<div class="synved-content-column' . ($class ? (' ' . $class) : null) . '"' . $css . '><div class="synved-column-content">' . do_shortcode($content) . '</div></div>';
}

function synved_shortcode_column_register($type, $default = null)
{
	$name = $type;
	$cb = create_function('$atts, $content = null, $code = \'\'', 'return synved_shortcode_do_column($atts, $content, $code, \'' . $type . '\');');
	
	synved_shortcode_add($name, $cb, false, 'Column ' . ucwords(str_replace('-', ' ', $type)));
	
	if ($default == null)
	{
		$alt_name = str_replace('-', '_', $name);
		$default = '[' . $alt_name . ']' . __('Your Content Here', 'synved-shortcode') . '[/' . $alt_name . ']';
	}
	
	if ($default != null)
	{
		synved_shortcode_item_default_set($name, $default);
	}
	
	$type_label = str_replace('-', ' ', $type);
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
		'tip' => sprintf(__('Creates a layout element that forces its contents to be contained in %1$s of the post', 'synved-shortcode'), $desc),
		'parameters' => array(
			'extend' => __('Forces some contents (like tables) inside of the layout element to extend to the full width of the element itself', 'synved-shortcode'), 
			'flow' => __('Determines how the layout element "flows" with other surrounding elements, possible values are start,hold,end,break. For examples on how this works you can <a href="http://wpdemo.synved.com/stripefolio/shortcodes/layout/">look here</a>', 'synved-shortcode'),
			'width' => __('Specify an explicit width to use instead of the default', 'synved-shortcode'),
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
	
	return '<div class="synved-box-message' . ($class ? (' ' . $class) : null) . '">' . do_shortcode($content) . '</div>';
}

function synved_shortcode_box_register($type, $default = null)
{
	$name = $type;
	$type_label = ucwords(str_replace('-', ' ', $type));
	$cb = create_function('$atts, $content = null, $code = \'\'', 'return synved_shortcode_do_box($atts, $content, $code, \'' . $type . '\');');
	
	synved_shortcode_add($type, $cb, false, __('Box', 'synved-shortcode') . ' ' . $type_label);
	
	if ($default == null)
	{
		$alt_name = str_replace('-', '_', $name);
		$default = '[' . $alt_name . ']' . $type_label . ' ' . __('Message', 'synved-shortcode') . '[/' . $alt_name . ']';
	}
	
	if ($default != null)
	{
		synved_shortcode_item_default_set($name, $default);
	}
	
	$type_label = str_replace('-', ' ', $type);
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
	$abstract = $item['abstract'];
	$class = $item['class'];
	$object = $item['object'];
	$body = do_shortcode($content);
	
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
		
		$matches = null;
		$max_nesting = 5;

		while (preg_match_all('/%%(\\w+)%%/', $template_markup, $matches, PREG_SET_ORDER) > 0 && $max_nesting > 0)
		{
			$max_nesting--;
			
			foreach ($matches as $match)
			{
				$var = $match[1];
				$parts = explode('_', $var);
				$root = array_shift($parts);
				$name = implode('_', $parts);
				$replace = null;
				
				switch ($root)
				{
					case 'link':
					case 'tip':
					case 'abstract':
					case 'class':
					case 'body':
					{
						switch ($name)
						{
							case null:
							{
								$replace = ${$root};
								
								break;
							}
							case 'attribute':
							{
								$value = ${$root};
								
								if ($value != null)
								{
									$attr_map = array('link' => 'href', 'tip' => 'title', 'class' => 'class');
									$attr_name = isset($attr_map[$root]) ? $attr_map[$root] : null;
									
									if ($attr_name != null)
									{
										$replace = ' ' . $attr_name . '="' . esc_attr($value) . '"';
									}
								}
								
								break;
							}
							case 'markup':
							{
								$value = ${$root};
								
								if ($value != null)
								{
									$replace = '<div class="synved-content-' . $root . '"><p>' . str_replace(array("\r\n", "\n"), '</p><p>', $value) . '</p></div>';
								}
								
								break;
							}
						}
						
						break;
					}
					case 'item':
					{
						if (isset($item[$name]))
						{
							$replace = $item[$name];
						}
			
						break;
					}
					case 'object':
					{
						if (isset($object->$name))
						{
							$replace = $object->$name;
						}
			
						break;
					}
				}
	
				$template_markup = str_replace($match[0], $replace, $template_markup);
			}
		}
		
		$out = $template_markup;
		
		return $out;
	}
	
	return null;
}

function synved_shortcode_link_register($type, $default = null)
{
	$name = 'link-' . $type;
	$cb = create_function('$atts, $content = null, $code = \'\'', 'return synved_shortcode_do_link($atts, $content, $code, \'' . $type . '\');');
	
	synved_shortcode_add($name, $cb, false, __('Link', 'synved-shortcode') . ' ' . ucwords(str_replace('-', ' ', $type)));
	
	if ($default == null)
	{
		$alt_name = str_replace('-', '_', $name);
		
		if ($type == 'post')
		{
			$default = '[' . $alt_name . ' id="1"]';
		}
		else
		{
			$default = '[' . $alt_name . ' name="unique-name"]' . __('Link Text', 'synved-shortcode') . '[/' . $alt_name . ']';
		}
	}
	
	if ($default != null)
	{
		synved_shortcode_item_default_set($name, $default);
	}
	
	$type_label = str_replace('-', ' ', $type);
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
	}
	
	$params['template'] = __('Specify what template to use to display the link, possible values are default,url,card,custom. You can use template %%tags%%, where "tags" can be link,tip,abstract,class,body,item_PROPERTY and much more', 'synved-shortcode');
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
	
	$name_alt = str_replace('-', '_', $name);
	$full_name = 'synved-' . $name;
	$full_name_alt = 'synved_' . $name_alt;
	
	if (!isset($synved_shortcode['list'][$name]))
	{
		add_shortcode($full_name_alt, $cb);
		add_shortcode($full_name, $cb);
		add_shortcode($name_alt, $cb);
		add_shortcode($name, $cb);
	
		if ($label == null)
		{
			$label = ucwords(str_replace('-', ' ', $name));
		}
	
		if ($default == null)
		{
			$default = '[' . $name_alt . ']';
		}
	
		$synved_shortcode['list'][$name] = array('callback' => $cb, 'name_alt' => $name_alt, 'label' => $label, 'internal' => $internal, 'default' => $default);
	}
}

function synved_shortcode_item_default_set($name, $default)
{
	global $synved_shortcode;
	
	if (isset($synved_shortcode['list'][$name]))
	{
		$synved_shortcode['list'][$name]['default'] = $default;
	}
}

function synved_shortcode_item_help_set($name, $help)
{
	global $synved_shortcode;
	
	if (isset($synved_shortcode['list'][$name]))
	{
		$synved_shortcode['list'][$name]['help'] = $help;
	}
}

function synved_shortcode_list()
{
	global $synved_shortcode;
	
	return $synved_shortcode['list'];
}

synved_shortcode_add('tabs', 'synved_shortcode_do_tabs');
synved_shortcode_add('tab', 'synved_shortcode_do_tab', true);
synved_shortcode_item_default_set('tabs', 
'[tabs]
[tab title="Tab 1"]
Tab Content 1.
[/tab]
[tab title="Tab 2"]
Tab Content 2.
[/tab]
[/tabs]');
synved_shortcode_item_help_set('tabs', array(
	'tip' => __('Creates a list of SEO-friendly tabs that work with or without JavaScript', 'synved-shortcode'),
	'parameters' => array(
		'class' => __('Only for [tabs] element, specify a custom CSS class for the main tabs container', 'synved-shortcode'),
		'title' => __('Only for [tab] element, specify the title of the tab', 'synved-shortcode'),
		'tip' => __('Only for [tab] element, specify a tooltip to show when hovering the tab with the mouse', 'synved-shortcode'),
		'active' => __('Only for [tab] element, specify whether the tab is the active tab (use active=true)', 'synved-shortcode')
	)
));

synved_shortcode_add('sections', 'synved_shortcode_do_sections');
synved_shortcode_add('section', 'synved_shortcode_do_section', true);
synved_shortcode_item_default_set('sections', 
'[sections]
[section title="Section 1"]
<p style="margin:5px 0;padding:0;">
Section Content 1.
</p>
[/section]
[section title="Section 2"]
<p style="margin:5px 0;padding:0;">
Section Content 2.
</p>
[/section]
[/sections]');
synved_shortcode_item_help_set('sections', array(
	'tip' => __('Creates a list exclusive sections, also called accordions', 'synved-shortcode'),
	'parameters' => array(
		'class' => __('Only for [sections] element, specify a custom CSS class for the main sections container', 'synved-shortcode'),
		'title' => __('Only for [section] element, specify the title of the section', 'synved-shortcode'),
		'tip' => __('Only for [section] element, specify a tooltip to show when hovering the section with the mouse', 'synved-shortcode'),
		//'active' => __('Only for [section] element, specify whether the tab is the active tab (use active=true)', 'synved-shortcode')
	)
));

synved_shortcode_add('button', 'synved_shortcode_do_button');
synved_shortcode_item_default_set('button',
'[button icon=heart]My Button[/button]');
synved_shortcode_item_help_set('button', array(
	'tip' => __('Creates a nice-looking button', 'synved-shortcode'),
	'parameters' => array(
		//'type' => __('Specify the type of button being created', 'synved-shortcode'),
		'type' => __('Specify a custom type of default button, possible values are download,purchase', 'synved-shortcode'),
		'link' => __('Specify a link to open when clicking on the button with the mouse', 'synved-shortcode'),
		'icon' => __('Specify an icon to display on the left of the button, check the <a href="http://synved.com/blog/help/tutorial/wordpress-shortcodes-icons/">list of icons</a>', 'synved-shortcode'),
		'icon2' => __('Specify an icon to display on the right of the button, check the <a href="http://synved.com/blog/help/tutorial/wordpress-shortcodes-icons/">list of icons</a>', 'synved-shortcode'),
		'tag' => __('Specify a tag to display on the top-right corner of the button', 'synved-shortcode'),
		'tip' => __('Specify a tooltip to show when hovering the button with the mouse', 'synved-shortcode'),
	)
));

synved_shortcode_add('list', 'synved_shortcode_do_list');
synved_shortcode_add('item', 'synved_shortcode_do_item', true);
synved_shortcode_item_default_set('list', 
'[list icon=link]
[item]Item 1[/item]
[item]Item 2[/item]
[/list]');
synved_shortcode_item_help_set('list', array(
	'tip' => __('Creates a list exclusive sections, also called accordions', 'synved-shortcode'),
	'parameters' => array(
		'type' => __('Only for [list] element, specify a custom type, possible values are decimal,alpha,roman,latin,upper-alpha,lower-roman,upper-latin', 'synved-shortcode'),
		'icon' => __('Specify the default icon for [list], which overwrites <b>type</b>, or the individual icon for each [item]', 'synved-shortcode'),
		'tip' => __('Only for [item] element, specify a tooltip to show when hovering the item with the mouse', 'synved-shortcode'),
		//'active' => __('Only for [section] element, specify whether the tab is the active tab (use active=true)', 'synved-shortcode')
	)
));

synved_shortcode_column_register('full');
synved_shortcode_column_register('three-quarters');
synved_shortcode_column_register('two-thirds');
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

?>
