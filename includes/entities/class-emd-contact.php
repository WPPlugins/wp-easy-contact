<?php
/**
 * Entity Class
 *
 * @package WP_EASY_CONTACT
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Contact Class
 * @since WPAS 4.0
 */
class Emd_Contact extends Emd_Entity {
	protected $post_type = 'emd_contact';
	protected $textdomain = 'wp-easy-contact';
	protected $sing_label;
	protected $plural_label;
	protected $menu_entity;
	protected $id;
	/**
	 * Initialize entity class
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function __construct() {
		add_action('init', array(
			$this,
			'set_filters'
		) , 1);
		add_action('admin_init', array(
			$this,
			'set_metabox'
		));
		add_filter('post_updated_messages', array(
			$this,
			'updated_messages'
		));
		$is_adv_filt_ext = apply_filters('emd_adv_filter_on', 0);
		if ($is_adv_filt_ext === 0) {
			add_action('manage_emd_contact_posts_custom_column', array(
				$this,
				'custom_columns'
			) , 10, 2);
			add_filter('manage_emd_contact_posts_columns', array(
				$this,
				'column_headers'
			));
		}
		add_filter('is_protected_meta', array(
			$this,
			'hide_attrs'
		) , 10, 2);
		add_filter('postmeta_form_keys', array(
			$this,
			'cust_keys'
		) , 10, 2);
		add_filter('emd_get_cust_fields', array(
			$this,
			'get_cust_fields'
		) , 10, 2);
		add_filter('enter_title_here', array(
			$this,
			'change_title_text'
		));
	}
	public function change_title_disable_emd_temp($title, $id) {
		$post = get_post($id);
		if ($this->post_type == $post->post_type && (!empty($this->id) && $this->id == $id)) {
			return '';
		}
		return $title;
	}
	/**
	 * Get custom attribute list
	 * @since WPAS 4.9
	 *
	 * @param array $cust_fields
	 * @param string $post_type
	 *
	 * @return array $new_keys
	 */
	public function get_cust_fields($cust_fields, $post_type) {
		global $wpdb;
		if ($post_type == $this->post_type) {
			$sql = "SELECT DISTINCT meta_key
               FROM $wpdb->postmeta a
               WHERE a.post_id IN (SELECT id FROM $wpdb->posts b WHERE b.post_type='" . $this->post_type . "')";
			$keys = $wpdb->get_col($sql);
			if (!empty($keys)) {
				foreach ($keys as $i => $mkey) {
					if (!preg_match('/^(_|wpas_|emd_)/', $mkey)) {
						$ckey = str_replace('-', '_', sanitize_title($mkey));
						$cust_fields[$ckey] = $mkey;
					}
				}
			}
		}
		return $cust_fields;
	}
	/**
	 * Set new custom attributes dropdown in admin edit entity
	 * @since WPAS 4.9
	 *
	 * @param array $keys
	 * @param object $post
	 *
	 * @return array $keys
	 */
	public function cust_keys($keys, $post) {
		global $post_type, $wpdb;
		if ($post_type == $this->post_type) {
			$sql = "SELECT DISTINCT meta_key
                FROM $wpdb->postmeta a
                WHERE a.post_id IN (SELECT id FROM $wpdb->posts b WHERE b.post_type='" . $this->post_type . "')";
			$keys = $wpdb->get_col($sql);
		}
		return $keys;
	}
	/**
	 * Hide all emd attributes
	 * @since WPAS 4.9
	 *
	 * @param bool $protected
	 * @param string $meta_key
	 *
	 * @return bool $protected
	 */
	public function hide_attrs($protected, $meta_key) {
		if (preg_match('/^(emd_|wpas_)/', $meta_key)) return true;
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if ($meta_key == $fkey) return true;
			}
		}
		return $protected;
	}
	/**
	 * Get column header list in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param array $columns
	 *
	 * @return array $columns
	 */
	public function column_headers($columns) {
		$ent_list = get_option(str_replace("-", "_", $this->textdomain) . '_ent_list');
		if (!empty($ent_list[$this->post_type]['featured_img'])) {
			$columns['featured_img'] = __('Featured Image', $this->textdomain);
		}
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if (!in_array($fkey, Array(
					'wpas_form_name',
					'wpas_form_submitted_by',
					'wpas_form_submitted_ip'
				)) && !in_array($mybox_field['type'], Array(
					'textarea',
					'wysiwyg'
				)) && $mybox_field['list_visible'] == 1) {
					$columns[$fkey] = $mybox_field['name'];
				}
			}
		}
		$taxonomies = get_object_taxonomies($this->post_type, 'objects');
		if (!empty($taxonomies)) {
			$tax_list = get_option(str_replace("-", "_", $this->textdomain) . '_tax_list');
			foreach ($taxonomies as $taxonomy) {
				if (!empty($tax_list[$this->post_type][$taxonomy->name]) && $tax_list[$this->post_type][$taxonomy->name]['list_visible'] == 1) {
					$columns[$taxonomy->name] = $taxonomy->label;
				}
			}
		}
		$rel_list = get_option(str_replace("-", "_", $this->textdomain) . '_rel_list');
		if (!empty($rel_list)) {
			foreach ($rel_list as $krel => $rel) {
				if ($rel['from'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'from'
				))) {
					$columns[$krel] = $rel['from_title'];
				} elseif ($rel['to'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'to'
				))) {
					$columns[$krel] = $rel['to_title'];
				}
			}
		}
		return $columns;
	}
	/**
	 * Get custom column values in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param int $column_id
	 * @param int $post_id
	 *
	 * @return string $value
	 */
	public function custom_columns($column_id, $post_id) {
		if (taxonomy_exists($column_id) == true) {
			$terms = get_the_terms($post_id, $column_id);
			$ret = array();
			if (!empty($terms)) {
				foreach ($terms as $term) {
					$url = add_query_arg(array(
						'post_type' => $this->post_type,
						'term' => $term->slug,
						'taxonomy' => $column_id
					) , admin_url('edit.php'));
					$a_class = preg_replace('/^emd_/', '', $this->post_type);
					$ret[] = sprintf('<a href="%s"  class="' . $a_class . '-tax ' . $term->slug . '">%s</a>', $url, $term->name);
				}
			}
			echo implode(', ', $ret);
			return;
		}
		$rel_list = get_option(str_replace("-", "_", $this->textdomain) . '_rel_list');
		if (!empty($rel_list) && !empty($rel_list[$column_id])) {
			$rel_arr = $rel_list[$column_id];
			if ($rel_arr['from'] == $this->post_type) {
				$other_ptype = $rel_arr['to'];
			} elseif ($rel_arr['to'] == $this->post_type) {
				$other_ptype = $rel_arr['from'];
			}
			$column_id = str_replace('rel_', '', $column_id);
			if (function_exists('p2p_type') && p2p_type($column_id)) {
				$rel_args = apply_filters('emd_ext_p2p_add_query_vars', array(
					'posts_per_page' => - 1
				) , Array(
					$other_ptype
				));
				$connected = p2p_type($column_id)->get_connected($post_id, $rel_args);
				$ptype_obj = get_post_type_object($this->post_type);
				$edit_cap = $ptype_obj->cap->edit_posts;
				$ret = array();
				if (empty($connected->posts)) return '&ndash;';
				foreach ($connected->posts as $myrelpost) {
					$rel_title = get_the_title($myrelpost->ID);
					$rel_title = apply_filters('emd_ext_p2p_connect_title', $rel_title, $myrelpost, '');
					$url = get_permalink($myrelpost->ID);
					$url = apply_filters('emd_ext_connected_ptype_url', $url, $myrelpost, $edit_cap);
					$ret[] = sprintf('<a href="%s" title="%s" target="_blank">%s</a>', $url, $rel_title, $rel_title);
				}
				echo implode(', ', $ret);
				return;
			}
		}
		$value = get_post_meta($post_id, $column_id, true);
		$type = "";
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if ($fkey == $column_id) {
					$type = $mybox_field['type'];
					break;
				}
			}
		}
		if ($column_id == 'featured_img') {
			$type = 'featured_img';
		}
		switch ($type) {
			case 'featured_img':
				$thumb_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id) , 'thumbnail');
				if (!empty($thumb_url)) {
					$value = "<img style='max-width:100%;height:auto;' src='" . $thumb_url[0] . "' >";
				}
			break;
			case 'plupload_image':
			case 'image':
			case 'thickbox_image':
				$image_list = emd_mb_meta($column_id, 'type=image');
				$value = "";
				if (!empty($image_list)) {
					$myimage = current($image_list);
					$value = "<img style='max-width:100%;height:auto;' src='" . $myimage['url'] . "' >";
				}
			break;
			case 'user':
			case 'user-adv':
				$user_id = emd_mb_meta($column_id);
				if (!empty($user_id)) {
					$user_info = get_userdata($user_id);
					$value = $user_info->display_name;
				}
			break;
			case 'file':
				$file_list = emd_mb_meta($column_id, 'type=file');
				if (!empty($file_list)) {
					$value = "";
					foreach ($file_list as $myfile) {
						$fsrc = wp_mime_type_icon($myfile['ID']);
						$value.= "<a href='" . $myfile['url'] . "' target='_blank'><img src='" . $fsrc . "' title='" . $myfile['name'] . "' width='20' /></a>";
					}
				}
			break;
			case 'radio':
			case 'checkbox_list':
			case 'select':
			case 'select_advanced':
				$value = emd_get_attr_val(str_replace("-", "_", $this->textdomain) , $post_id, $this->post_type, $column_id);
			break;
			case 'checkbox':
				if ($value == 1) {
					$value = '<span class="dashicons dashicons-yes"></span>';
				} elseif ($value == 0) {
					$value = '<span class="dashicons dashicons-no-alt"></span>';
				}
			break;
			case 'rating':
				$value = apply_filters('emd_get_rating_value', $value, Array(
					'meta' => $column_id
				) , $post_id);
			break;
		}
		if (is_array($value)) {
			$value = "<div class='clonelink'>" . implode("</div><div class='clonelink'>", $value) . "</div>";
		}
		echo $value;
	}
	/**
	 * Register post type and taxonomies and set initial values for taxs
	 *
	 * @since WPAS 4.0
	 *
	 */
	public static function register() {
		$labels = array(
			'name' => __('Contacts', 'wp-easy-contact') ,
			'singular_name' => __('Contact', 'wp-easy-contact') ,
			'add_new' => __('Add New', 'wp-easy-contact') ,
			'add_new_item' => __('Add New Contact', 'wp-easy-contact') ,
			'edit_item' => __('Edit Contact', 'wp-easy-contact') ,
			'new_item' => __('New Contact', 'wp-easy-contact') ,
			'all_items' => __('All Contacts', 'wp-easy-contact') ,
			'view_item' => __('View Contact', 'wp-easy-contact') ,
			'search_items' => __('Search Contacts', 'wp-easy-contact') ,
			'not_found' => __('No Contacts Found', 'wp-easy-contact') ,
			'not_found_in_trash' => __('No Contacts Found In Trash', 'wp-easy-contact') ,
			'menu_name' => __('Contacts', 'wp-easy-contact') ,
		);
		$ent_map_list = get_option('wp_easy_contact_ent_map_list', Array());
		if (!empty($ent_map_list['emd_contact']['rewrite'])) {
			$rewrite = $ent_map_list['emd_contact']['rewrite'];
		} else {
			$rewrite = 'contacts';
		}
		$supports = Array(
			'custom-fields',
		);
		if (empty($ent_map_list['emd_contact']['attrs']['blt_title']) || $ent_map_list['emd_contact']['attrs']['blt_title'] != 'hide') {
			$supports[] = 'title';
		}
		if (empty($ent_map_list['emd_contact']['attrs']['blt_content']) || $ent_map_list['emd_contact']['attrs']['blt_content'] != 'hide') {
			$supports[] = 'editor';
		}
		register_post_type('emd_contact', array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'description' => __('', 'wp-easy-contact') ,
			'show_in_menu' => true,
			'menu_position' => 6,
			'has_archive' => true,
			'exclude_from_search' => false,
			'rewrite' => array(
				'slug' => $rewrite
			) ,
			'can_export' => true,
			'show_in_rest' => false,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-groups',
			'map_meta_cap' => 'true',
			'taxonomies' => array() ,
			'capability_type' => 'emd_contact',
			'supports' => $supports,
		));
		$contact_topic_nohr_labels = array(
			'name' => __('Topics', 'wp-easy-contact') ,
			'singular_name' => __('Topic', 'wp-easy-contact') ,
			'search_items' => __('Search Topics', 'wp-easy-contact') ,
			'popular_items' => __('Popular Topics', 'wp-easy-contact') ,
			'all_items' => __('All', 'wp-easy-contact') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Topic', 'wp-easy-contact') ,
			'update_item' => __('Update Topic', 'wp-easy-contact') ,
			'add_new_item' => __('Add New Topic', 'wp-easy-contact') ,
			'new_item_name' => __('Add New Topic Name', 'wp-easy-contact') ,
			'separate_items_with_commas' => __('Seperate Topics with commas', 'wp-easy-contact') ,
			'add_or_remove_items' => __('Add or Remove Topics', 'wp-easy-contact') ,
			'choose_from_most_used' => __('Choose from the most used Topics', 'wp-easy-contact') ,
			'menu_name' => __('Topics', 'wp-easy-contact') ,
		);
		$tax_settings = get_option('wp_easy_contact_tax_settings', Array());
		if (empty($tax_settings['contact_topic']['hide']) || (!empty($tax_settings['contact_topic']['hide']) && $tax_settings['contact_topic']['hide'] != 'hide')) {
			if (!empty($tax_settings['contact_topic']['rewrite'])) {
				$rewrite = $tax_settings['contact_topic']['rewrite'];
			} else {
				$rewrite = 'contact_topic';
			}
			register_taxonomy('contact_topic', array(
				'emd_contact'
			) , array(
				'hierarchical' => false,
				'labels' => $contact_topic_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'capabilities' => array(
					'manage_terms' => 'manage_contact_topic',
					'edit_terms' => 'edit_contact_topic',
					'delete_terms' => 'delete_contact_topic',
					'assign_terms' => 'assign_contact_topic'
				) ,
			));
		}
		$contact_country_nohr_labels = array(
			'name' => __('Countries', 'wp-easy-contact') ,
			'singular_name' => __('Country', 'wp-easy-contact') ,
			'search_items' => __('Search Countries', 'wp-easy-contact') ,
			'popular_items' => __('Popular Countries', 'wp-easy-contact') ,
			'all_items' => __('All', 'wp-easy-contact') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Country', 'wp-easy-contact') ,
			'update_item' => __('Update Country', 'wp-easy-contact') ,
			'add_new_item' => __('Add New Country', 'wp-easy-contact') ,
			'new_item_name' => __('Add New Country Name', 'wp-easy-contact') ,
			'separate_items_with_commas' => __('Seperate Countries with commas', 'wp-easy-contact') ,
			'add_or_remove_items' => __('Add or Remove Countries', 'wp-easy-contact') ,
			'choose_from_most_used' => __('Choose from the most used Countries', 'wp-easy-contact') ,
			'menu_name' => __('Countries', 'wp-easy-contact') ,
		);
		$tax_settings = get_option('wp_easy_contact_tax_settings', Array());
		if (empty($tax_settings['contact_country']['hide']) || (!empty($tax_settings['contact_country']['hide']) && $tax_settings['contact_country']['hide'] != 'hide')) {
			if (!empty($tax_settings['contact_country']['rewrite'])) {
				$rewrite = $tax_settings['contact_country']['rewrite'];
			} else {
				$rewrite = 'contact_country';
			}
			register_taxonomy('contact_country', array(
				'emd_contact'
			) , array(
				'hierarchical' => false,
				'labels' => $contact_country_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'capabilities' => array(
					'manage_terms' => 'manage_contact_country',
					'edit_terms' => 'edit_contact_country',
					'delete_terms' => 'delete_contact_country',
					'assign_terms' => 'assign_contact_country'
				) ,
			));
		}
		$contact_state_nohr_labels = array(
			'name' => __('States', 'wp-easy-contact') ,
			'singular_name' => __('State', 'wp-easy-contact') ,
			'search_items' => __('Search States', 'wp-easy-contact') ,
			'popular_items' => __('Popular States', 'wp-easy-contact') ,
			'all_items' => __('All', 'wp-easy-contact') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit State', 'wp-easy-contact') ,
			'update_item' => __('Update State', 'wp-easy-contact') ,
			'add_new_item' => __('Add New State', 'wp-easy-contact') ,
			'new_item_name' => __('Add New State Name', 'wp-easy-contact') ,
			'separate_items_with_commas' => __('Seperate States with commas', 'wp-easy-contact') ,
			'add_or_remove_items' => __('Add or Remove States', 'wp-easy-contact') ,
			'choose_from_most_used' => __('Choose from the most used States', 'wp-easy-contact') ,
			'menu_name' => __('States', 'wp-easy-contact') ,
		);
		$tax_settings = get_option('wp_easy_contact_tax_settings', Array());
		if (empty($tax_settings['contact_state']['hide']) || (!empty($tax_settings['contact_state']['hide']) && $tax_settings['contact_state']['hide'] != 'hide')) {
			if (!empty($tax_settings['contact_state']['rewrite'])) {
				$rewrite = $tax_settings['contact_state']['rewrite'];
			} else {
				$rewrite = 'contact_state';
			}
			register_taxonomy('contact_state', array(
				'emd_contact'
			) , array(
				'hierarchical' => false,
				'labels' => $contact_state_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'capabilities' => array(
					'manage_terms' => 'manage_contact_state',
					'edit_terms' => 'edit_contact_state',
					'delete_terms' => 'delete_contact_state',
					'assign_terms' => 'assign_contact_state'
				) ,
			));
		}
		$contact_tag_nohr_labels = array(
			'name' => __('Contact Tags', 'wp-easy-contact') ,
			'singular_name' => __('Contact Tag', 'wp-easy-contact') ,
			'search_items' => __('Search Contact Tags', 'wp-easy-contact') ,
			'popular_items' => __('Popular Contact Tags', 'wp-easy-contact') ,
			'all_items' => __('All', 'wp-easy-contact') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Contact Tag', 'wp-easy-contact') ,
			'update_item' => __('Update Contact Tag', 'wp-easy-contact') ,
			'add_new_item' => __('Add New Contact Tag', 'wp-easy-contact') ,
			'new_item_name' => __('Add New Contact Tag Name', 'wp-easy-contact') ,
			'separate_items_with_commas' => __('Seperate Contact Tags with commas', 'wp-easy-contact') ,
			'add_or_remove_items' => __('Add or Remove Contact Tags', 'wp-easy-contact') ,
			'choose_from_most_used' => __('Choose from the most used Contact Tags', 'wp-easy-contact') ,
			'menu_name' => __('Contact Tags', 'wp-easy-contact') ,
		);
		$tax_settings = get_option('wp_easy_contact_tax_settings', Array());
		if (empty($tax_settings['contact_tag']['hide']) || (!empty($tax_settings['contact_tag']['hide']) && $tax_settings['contact_tag']['hide'] != 'hide')) {
			if (!empty($tax_settings['contact_tag']['rewrite'])) {
				$rewrite = $tax_settings['contact_tag']['rewrite'];
			} else {
				$rewrite = 'contact_tag';
			}
			register_taxonomy('contact_tag', array(
				'emd_contact'
			) , array(
				'hierarchical' => false,
				'labels' => $contact_tag_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'capabilities' => array(
					'manage_terms' => 'manage_contact_tag',
					'edit_terms' => 'edit_contact_tag',
					'delete_terms' => 'delete_contact_tag',
					'assign_terms' => 'assign_contact_tag'
				) ,
			));
		}
		if (!get_option('wp_easy_contact_emd_contact_terms_init')) {
			$set_tax_terms = Array(
				Array(
					'name' => __('Customer Service', 'wp-easy-contact') ,
					'slug' => sanitize_title('Customer Service')
				) ,
				Array(
					'name' => __('Product Information', 'wp-easy-contact') ,
					'slug' => sanitize_title('Product Information')
				) ,
				Array(
					'name' => __('My Account', 'wp-easy-contact') ,
					'slug' => sanitize_title('My Account')
				) ,
				Array(
					'name' => __('Customer Feedback', 'wp-easy-contact') ,
					'slug' => sanitize_title('Customer Feedback')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'contact_topic');
			$set_tax_terms = Array(
				Array(
					'name' => __('Afghanistan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Afghanistan')
				) ,
				Array(
					'name' => __('Åland Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Åland Islands')
				) ,
				Array(
					'name' => __('Albania', 'wp-easy-contact') ,
					'slug' => sanitize_title('Albania')
				) ,
				Array(
					'name' => __('Algeria', 'wp-easy-contact') ,
					'slug' => sanitize_title('Algeria')
				) ,
				Array(
					'name' => __('American Samoa', 'wp-easy-contact') ,
					'slug' => sanitize_title('American Samoa')
				) ,
				Array(
					'name' => __('Andorra', 'wp-easy-contact') ,
					'slug' => sanitize_title('Andorra')
				) ,
				Array(
					'name' => __('Angola', 'wp-easy-contact') ,
					'slug' => sanitize_title('Angola')
				) ,
				Array(
					'name' => __('Anguilla', 'wp-easy-contact') ,
					'slug' => sanitize_title('Anguilla')
				) ,
				Array(
					'name' => __('Antarctica', 'wp-easy-contact') ,
					'slug' => sanitize_title('Antarctica')
				) ,
				Array(
					'name' => __('Antigua And Barbuda', 'wp-easy-contact') ,
					'slug' => sanitize_title('Antigua And Barbuda')
				) ,
				Array(
					'name' => __('Argentina', 'wp-easy-contact') ,
					'slug' => sanitize_title('Argentina')
				) ,
				Array(
					'name' => __('Armenia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Armenia')
				) ,
				Array(
					'name' => __('Aruba', 'wp-easy-contact') ,
					'slug' => sanitize_title('Aruba')
				) ,
				Array(
					'name' => __('Australia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Australia')
				) ,
				Array(
					'name' => __('Austria', 'wp-easy-contact') ,
					'slug' => sanitize_title('Austria')
				) ,
				Array(
					'name' => __('Azerbaijan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Azerbaijan')
				) ,
				Array(
					'name' => __('Bahamas', 'wp-easy-contact') ,
					'slug' => sanitize_title('Bahamas')
				) ,
				Array(
					'name' => __('Bahrain', 'wp-easy-contact') ,
					'slug' => sanitize_title('Bahrain')
				) ,
				Array(
					'name' => __('Bangladesh', 'wp-easy-contact') ,
					'slug' => sanitize_title('Bangladesh')
				) ,
				Array(
					'name' => __('Barbados', 'wp-easy-contact') ,
					'slug' => sanitize_title('Barbados')
				) ,
				Array(
					'name' => __('Belarus', 'wp-easy-contact') ,
					'slug' => sanitize_title('Belarus')
				) ,
				Array(
					'name' => __('Belgium', 'wp-easy-contact') ,
					'slug' => sanitize_title('Belgium')
				) ,
				Array(
					'name' => __('Belize', 'wp-easy-contact') ,
					'slug' => sanitize_title('Belize')
				) ,
				Array(
					'name' => __('Benin', 'wp-easy-contact') ,
					'slug' => sanitize_title('Benin')
				) ,
				Array(
					'name' => __('Bermuda', 'wp-easy-contact') ,
					'slug' => sanitize_title('Bermuda')
				) ,
				Array(
					'name' => __('Bhutan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Bhutan')
				) ,
				Array(
					'name' => __('Bolivia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Bolivia')
				) ,
				Array(
					'name' => __('Bosnia And Herzegovina', 'wp-easy-contact') ,
					'slug' => sanitize_title('Bosnia And Herzegovina')
				) ,
				Array(
					'name' => __('Botswana', 'wp-easy-contact') ,
					'slug' => sanitize_title('Botswana')
				) ,
				Array(
					'name' => __('Bouvet Island', 'wp-easy-contact') ,
					'slug' => sanitize_title('Bouvet Island')
				) ,
				Array(
					'name' => __('Brazil', 'wp-easy-contact') ,
					'slug' => sanitize_title('Brazil')
				) ,
				Array(
					'name' => __('British Indian Ocean Territory', 'wp-easy-contact') ,
					'slug' => sanitize_title('British Indian Ocean Territory')
				) ,
				Array(
					'name' => __('Brunei Darussalam', 'wp-easy-contact') ,
					'slug' => sanitize_title('Brunei Darussalam')
				) ,
				Array(
					'name' => __('Bulgaria', 'wp-easy-contact') ,
					'slug' => sanitize_title('Bulgaria')
				) ,
				Array(
					'name' => __('Burkina Faso', 'wp-easy-contact') ,
					'slug' => sanitize_title('Burkina Faso')
				) ,
				Array(
					'name' => __('Burundi', 'wp-easy-contact') ,
					'slug' => sanitize_title('Burundi')
				) ,
				Array(
					'name' => __('Cambodia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Cambodia')
				) ,
				Array(
					'name' => __('Cameroon', 'wp-easy-contact') ,
					'slug' => sanitize_title('Cameroon')
				) ,
				Array(
					'name' => __('Canada', 'wp-easy-contact') ,
					'slug' => sanitize_title('Canada')
				) ,
				Array(
					'name' => __('Cape Verde', 'wp-easy-contact') ,
					'slug' => sanitize_title('Cape Verde')
				) ,
				Array(
					'name' => __('Cayman Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Cayman Islands')
				) ,
				Array(
					'name' => __('Central African Republic', 'wp-easy-contact') ,
					'slug' => sanitize_title('Central African Republic')
				) ,
				Array(
					'name' => __('Chad', 'wp-easy-contact') ,
					'slug' => sanitize_title('Chad')
				) ,
				Array(
					'name' => __('Chile', 'wp-easy-contact') ,
					'slug' => sanitize_title('Chile')
				) ,
				Array(
					'name' => __('China', 'wp-easy-contact') ,
					'slug' => sanitize_title('China')
				) ,
				Array(
					'name' => __('Christmas Island', 'wp-easy-contact') ,
					'slug' => sanitize_title('Christmas Island')
				) ,
				Array(
					'name' => __('Cocos (Keeling) Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Cocos (Keeling) Islands')
				) ,
				Array(
					'name' => __('Colombia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Colombia')
				) ,
				Array(
					'name' => __('Comoros', 'wp-easy-contact') ,
					'slug' => sanitize_title('Comoros')
				) ,
				Array(
					'name' => __('Congo', 'wp-easy-contact') ,
					'slug' => sanitize_title('Congo')
				) ,
				Array(
					'name' => __('Congo, The Democratic Republic Of The', 'wp-easy-contact') ,
					'slug' => sanitize_title('Congo, The Democratic Republic Of The')
				) ,
				Array(
					'name' => __('Cook Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Cook Islands')
				) ,
				Array(
					'name' => __('Costa Rica', 'wp-easy-contact') ,
					'slug' => sanitize_title('Costa Rica')
				) ,
				Array(
					'name' => __('Cote D\'ivoire', 'wp-easy-contact') ,
					'slug' => sanitize_title('Cote D\'ivoire')
				) ,
				Array(
					'name' => __('Croatia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Croatia')
				) ,
				Array(
					'name' => __('Cuba', 'wp-easy-contact') ,
					'slug' => sanitize_title('Cuba')
				) ,
				Array(
					'name' => __('Cyprus', 'wp-easy-contact') ,
					'slug' => sanitize_title('Cyprus')
				) ,
				Array(
					'name' => __('Czech Republic', 'wp-easy-contact') ,
					'slug' => sanitize_title('Czech Republic')
				) ,
				Array(
					'name' => __('Denmark', 'wp-easy-contact') ,
					'slug' => sanitize_title('Denmark')
				) ,
				Array(
					'name' => __('Djibouti', 'wp-easy-contact') ,
					'slug' => sanitize_title('Djibouti')
				) ,
				Array(
					'name' => __('Dominica', 'wp-easy-contact') ,
					'slug' => sanitize_title('Dominica')
				) ,
				Array(
					'name' => __('Dominican Republic', 'wp-easy-contact') ,
					'slug' => sanitize_title('Dominican Republic')
				) ,
				Array(
					'name' => __('Ecuador', 'wp-easy-contact') ,
					'slug' => sanitize_title('Ecuador')
				) ,
				Array(
					'name' => __('Egypt', 'wp-easy-contact') ,
					'slug' => sanitize_title('Egypt')
				) ,
				Array(
					'name' => __('El Salvador', 'wp-easy-contact') ,
					'slug' => sanitize_title('El Salvador')
				) ,
				Array(
					'name' => __('Equatorial Guinea', 'wp-easy-contact') ,
					'slug' => sanitize_title('Equatorial Guinea')
				) ,
				Array(
					'name' => __('Eritrea', 'wp-easy-contact') ,
					'slug' => sanitize_title('Eritrea')
				) ,
				Array(
					'name' => __('Estonia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Estonia')
				) ,
				Array(
					'name' => __('Ethiopia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Ethiopia')
				) ,
				Array(
					'name' => __('Falkland Islands (Malvinas)', 'wp-easy-contact') ,
					'slug' => sanitize_title('Falkland Islands (Malvinas)')
				) ,
				Array(
					'name' => __('Faroe Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Faroe Islands')
				) ,
				Array(
					'name' => __('Fiji', 'wp-easy-contact') ,
					'slug' => sanitize_title('Fiji')
				) ,
				Array(
					'name' => __('Finland', 'wp-easy-contact') ,
					'slug' => sanitize_title('Finland')
				) ,
				Array(
					'name' => __('France', 'wp-easy-contact') ,
					'slug' => sanitize_title('France')
				) ,
				Array(
					'name' => __('French Guiana', 'wp-easy-contact') ,
					'slug' => sanitize_title('French Guiana')
				) ,
				Array(
					'name' => __('French Polynesia', 'wp-easy-contact') ,
					'slug' => sanitize_title('French Polynesia')
				) ,
				Array(
					'name' => __('French Southern Territories', 'wp-easy-contact') ,
					'slug' => sanitize_title('French Southern Territories')
				) ,
				Array(
					'name' => __('Gabon', 'wp-easy-contact') ,
					'slug' => sanitize_title('Gabon')
				) ,
				Array(
					'name' => __('Gambia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Gambia')
				) ,
				Array(
					'name' => __('Georgia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Georgia')
				) ,
				Array(
					'name' => __('Germany', 'wp-easy-contact') ,
					'slug' => sanitize_title('Germany')
				) ,
				Array(
					'name' => __('Ghana', 'wp-easy-contact') ,
					'slug' => sanitize_title('Ghana')
				) ,
				Array(
					'name' => __('Gibraltar', 'wp-easy-contact') ,
					'slug' => sanitize_title('Gibraltar')
				) ,
				Array(
					'name' => __('Greece', 'wp-easy-contact') ,
					'slug' => sanitize_title('Greece')
				) ,
				Array(
					'name' => __('Greenland', 'wp-easy-contact') ,
					'slug' => sanitize_title('Greenland')
				) ,
				Array(
					'name' => __('Grenada', 'wp-easy-contact') ,
					'slug' => sanitize_title('Grenada')
				) ,
				Array(
					'name' => __('Guadeloupe', 'wp-easy-contact') ,
					'slug' => sanitize_title('Guadeloupe')
				) ,
				Array(
					'name' => __('Guam', 'wp-easy-contact') ,
					'slug' => sanitize_title('Guam')
				) ,
				Array(
					'name' => __('Guatemala', 'wp-easy-contact') ,
					'slug' => sanitize_title('Guatemala')
				) ,
				Array(
					'name' => __('Guernsey', 'wp-easy-contact') ,
					'slug' => sanitize_title('Guernsey')
				) ,
				Array(
					'name' => __('Guinea', 'wp-easy-contact') ,
					'slug' => sanitize_title('Guinea')
				) ,
				Array(
					'name' => __('Guinea-bissau', 'wp-easy-contact') ,
					'slug' => sanitize_title('Guinea-bissau')
				) ,
				Array(
					'name' => __('Guyana', 'wp-easy-contact') ,
					'slug' => sanitize_title('Guyana')
				) ,
				Array(
					'name' => __('Haiti', 'wp-easy-contact') ,
					'slug' => sanitize_title('Haiti')
				) ,
				Array(
					'name' => __('Heard Island And Mcdonald Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Heard Island And Mcdonald Islands')
				) ,
				Array(
					'name' => __('Holy See (Vatican City State)', 'wp-easy-contact') ,
					'slug' => sanitize_title('Holy See (Vatican City State)')
				) ,
				Array(
					'name' => __('Honduras', 'wp-easy-contact') ,
					'slug' => sanitize_title('Honduras')
				) ,
				Array(
					'name' => __('Hong Kong', 'wp-easy-contact') ,
					'slug' => sanitize_title('Hong Kong')
				) ,
				Array(
					'name' => __('Hungary', 'wp-easy-contact') ,
					'slug' => sanitize_title('Hungary')
				) ,
				Array(
					'name' => __('Iceland', 'wp-easy-contact') ,
					'slug' => sanitize_title('Iceland')
				) ,
				Array(
					'name' => __('India', 'wp-easy-contact') ,
					'slug' => sanitize_title('India')
				) ,
				Array(
					'name' => __('Indonesia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Indonesia')
				) ,
				Array(
					'name' => __('Iran, Islamic Republic Of', 'wp-easy-contact') ,
					'slug' => sanitize_title('Iran, Islamic Republic Of')
				) ,
				Array(
					'name' => __('Iraq', 'wp-easy-contact') ,
					'slug' => sanitize_title('Iraq')
				) ,
				Array(
					'name' => __('Ireland', 'wp-easy-contact') ,
					'slug' => sanitize_title('Ireland')
				) ,
				Array(
					'name' => __('Isle Of Man', 'wp-easy-contact') ,
					'slug' => sanitize_title('Isle Of Man')
				) ,
				Array(
					'name' => __('Israel', 'wp-easy-contact') ,
					'slug' => sanitize_title('Israel')
				) ,
				Array(
					'name' => __('Italy', 'wp-easy-contact') ,
					'slug' => sanitize_title('Italy')
				) ,
				Array(
					'name' => __('Jamaica', 'wp-easy-contact') ,
					'slug' => sanitize_title('Jamaica')
				) ,
				Array(
					'name' => __('Japan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Japan')
				) ,
				Array(
					'name' => __('Jersey', 'wp-easy-contact') ,
					'slug' => sanitize_title('Jersey')
				) ,
				Array(
					'name' => __('Jordan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Jordan')
				) ,
				Array(
					'name' => __('Kazakhstan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Kazakhstan')
				) ,
				Array(
					'name' => __('Kenya', 'wp-easy-contact') ,
					'slug' => sanitize_title('Kenya')
				) ,
				Array(
					'name' => __('Kiribati', 'wp-easy-contact') ,
					'slug' => sanitize_title('Kiribati')
				) ,
				Array(
					'name' => __('Korea, Democratic People\'s Republic Of', 'wp-easy-contact') ,
					'slug' => sanitize_title('Korea, Democratic People\'s Republic Of')
				) ,
				Array(
					'name' => __('Korea, Republic Of', 'wp-easy-contact') ,
					'slug' => sanitize_title('Korea, Republic Of')
				) ,
				Array(
					'name' => __('Kuwait', 'wp-easy-contact') ,
					'slug' => sanitize_title('Kuwait')
				) ,
				Array(
					'name' => __('Kyrgyzstan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Kyrgyzstan')
				) ,
				Array(
					'name' => __('Lao People\'s Democratic Republic', 'wp-easy-contact') ,
					'slug' => sanitize_title('Lao People\'s Democratic Republic')
				) ,
				Array(
					'name' => __('Latvia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Latvia')
				) ,
				Array(
					'name' => __('Lebanon', 'wp-easy-contact') ,
					'slug' => sanitize_title('Lebanon')
				) ,
				Array(
					'name' => __('Lesotho', 'wp-easy-contact') ,
					'slug' => sanitize_title('Lesotho')
				) ,
				Array(
					'name' => __('Liberia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Liberia')
				) ,
				Array(
					'name' => __('Libyan Arab Jamahiriya', 'wp-easy-contact') ,
					'slug' => sanitize_title('Libyan Arab Jamahiriya')
				) ,
				Array(
					'name' => __('Liechtenstein', 'wp-easy-contact') ,
					'slug' => sanitize_title('Liechtenstein')
				) ,
				Array(
					'name' => __('Lithuania', 'wp-easy-contact') ,
					'slug' => sanitize_title('Lithuania')
				) ,
				Array(
					'name' => __('Luxembourg', 'wp-easy-contact') ,
					'slug' => sanitize_title('Luxembourg')
				) ,
				Array(
					'name' => __('Macao', 'wp-easy-contact') ,
					'slug' => sanitize_title('Macao')
				) ,
				Array(
					'name' => __('Macedonia, The Former Yugoslav Republic Of', 'wp-easy-contact') ,
					'slug' => sanitize_title('Macedonia, The Former Yugoslav Republic Of')
				) ,
				Array(
					'name' => __('Madagascar', 'wp-easy-contact') ,
					'slug' => sanitize_title('Madagascar')
				) ,
				Array(
					'name' => __('Malawi', 'wp-easy-contact') ,
					'slug' => sanitize_title('Malawi')
				) ,
				Array(
					'name' => __('Malaysia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Malaysia')
				) ,
				Array(
					'name' => __('Maldives', 'wp-easy-contact') ,
					'slug' => sanitize_title('Maldives')
				) ,
				Array(
					'name' => __('Mali', 'wp-easy-contact') ,
					'slug' => sanitize_title('Mali')
				) ,
				Array(
					'name' => __('Malta', 'wp-easy-contact') ,
					'slug' => sanitize_title('Malta')
				) ,
				Array(
					'name' => __('Marshall Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Marshall Islands')
				) ,
				Array(
					'name' => __('Martinique', 'wp-easy-contact') ,
					'slug' => sanitize_title('Martinique')
				) ,
				Array(
					'name' => __('Mauritania', 'wp-easy-contact') ,
					'slug' => sanitize_title('Mauritania')
				) ,
				Array(
					'name' => __('Mauritius', 'wp-easy-contact') ,
					'slug' => sanitize_title('Mauritius')
				) ,
				Array(
					'name' => __('Mayotte', 'wp-easy-contact') ,
					'slug' => sanitize_title('Mayotte')
				) ,
				Array(
					'name' => __('Mexico', 'wp-easy-contact') ,
					'slug' => sanitize_title('Mexico')
				) ,
				Array(
					'name' => __('Micronesia, Federated States Of', 'wp-easy-contact') ,
					'slug' => sanitize_title('Micronesia, Federated States Of')
				) ,
				Array(
					'name' => __('Moldova, Republic Of', 'wp-easy-contact') ,
					'slug' => sanitize_title('Moldova, Republic Of')
				) ,
				Array(
					'name' => __('Monaco', 'wp-easy-contact') ,
					'slug' => sanitize_title('Monaco')
				) ,
				Array(
					'name' => __('Mongolia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Mongolia')
				) ,
				Array(
					'name' => __('Montenegro', 'wp-easy-contact') ,
					'slug' => sanitize_title('Montenegro')
				) ,
				Array(
					'name' => __('Montserrat', 'wp-easy-contact') ,
					'slug' => sanitize_title('Montserrat')
				) ,
				Array(
					'name' => __('Morocco', 'wp-easy-contact') ,
					'slug' => sanitize_title('Morocco')
				) ,
				Array(
					'name' => __('Mozambique', 'wp-easy-contact') ,
					'slug' => sanitize_title('Mozambique')
				) ,
				Array(
					'name' => __('Myanmar', 'wp-easy-contact') ,
					'slug' => sanitize_title('Myanmar')
				) ,
				Array(
					'name' => __('Namibia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Namibia')
				) ,
				Array(
					'name' => __('Nauru', 'wp-easy-contact') ,
					'slug' => sanitize_title('Nauru')
				) ,
				Array(
					'name' => __('Nepal', 'wp-easy-contact') ,
					'slug' => sanitize_title('Nepal')
				) ,
				Array(
					'name' => __('Netherlands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Netherlands')
				) ,
				Array(
					'name' => __('Netherlands Antilles', 'wp-easy-contact') ,
					'slug' => sanitize_title('Netherlands Antilles')
				) ,
				Array(
					'name' => __('New Caledonia', 'wp-easy-contact') ,
					'slug' => sanitize_title('New Caledonia')
				) ,
				Array(
					'name' => __('New Zealand', 'wp-easy-contact') ,
					'slug' => sanitize_title('New Zealand')
				) ,
				Array(
					'name' => __('Nicaragua', 'wp-easy-contact') ,
					'slug' => sanitize_title('Nicaragua')
				) ,
				Array(
					'name' => __('Niger', 'wp-easy-contact') ,
					'slug' => sanitize_title('Niger')
				) ,
				Array(
					'name' => __('Nigeria', 'wp-easy-contact') ,
					'slug' => sanitize_title('Nigeria')
				) ,
				Array(
					'name' => __('Niue', 'wp-easy-contact') ,
					'slug' => sanitize_title('Niue')
				) ,
				Array(
					'name' => __('Norfolk Island', 'wp-easy-contact') ,
					'slug' => sanitize_title('Norfolk Island')
				) ,
				Array(
					'name' => __('Northern Mariana Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Northern Mariana Islands')
				) ,
				Array(
					'name' => __('Norway', 'wp-easy-contact') ,
					'slug' => sanitize_title('Norway')
				) ,
				Array(
					'name' => __('Oman', 'wp-easy-contact') ,
					'slug' => sanitize_title('Oman')
				) ,
				Array(
					'name' => __('Pakistan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Pakistan')
				) ,
				Array(
					'name' => __('Palau', 'wp-easy-contact') ,
					'slug' => sanitize_title('Palau')
				) ,
				Array(
					'name' => __('Palestinian Territory, Occupied', 'wp-easy-contact') ,
					'slug' => sanitize_title('Palestinian Territory, Occupied')
				) ,
				Array(
					'name' => __('Panama', 'wp-easy-contact') ,
					'slug' => sanitize_title('Panama')
				) ,
				Array(
					'name' => __('Papua New Guinea', 'wp-easy-contact') ,
					'slug' => sanitize_title('Papua New Guinea')
				) ,
				Array(
					'name' => __('Paraguay', 'wp-easy-contact') ,
					'slug' => sanitize_title('Paraguay')
				) ,
				Array(
					'name' => __('Peru', 'wp-easy-contact') ,
					'slug' => sanitize_title('Peru')
				) ,
				Array(
					'name' => __('Philippines', 'wp-easy-contact') ,
					'slug' => sanitize_title('Philippines')
				) ,
				Array(
					'name' => __('Pitcairn', 'wp-easy-contact') ,
					'slug' => sanitize_title('Pitcairn')
				) ,
				Array(
					'name' => __('Poland', 'wp-easy-contact') ,
					'slug' => sanitize_title('Poland')
				) ,
				Array(
					'name' => __('Portugal', 'wp-easy-contact') ,
					'slug' => sanitize_title('Portugal')
				) ,
				Array(
					'name' => __('Puerto Rico', 'wp-easy-contact') ,
					'slug' => sanitize_title('Puerto Rico')
				) ,
				Array(
					'name' => __('Qatar', 'wp-easy-contact') ,
					'slug' => sanitize_title('Qatar')
				) ,
				Array(
					'name' => __('Reunion', 'wp-easy-contact') ,
					'slug' => sanitize_title('Reunion')
				) ,
				Array(
					'name' => __('Romania', 'wp-easy-contact') ,
					'slug' => sanitize_title('Romania')
				) ,
				Array(
					'name' => __('Russian Federation', 'wp-easy-contact') ,
					'slug' => sanitize_title('Russian Federation')
				) ,
				Array(
					'name' => __('Rwanda', 'wp-easy-contact') ,
					'slug' => sanitize_title('Rwanda')
				) ,
				Array(
					'name' => __('Saint Helena', 'wp-easy-contact') ,
					'slug' => sanitize_title('Saint Helena')
				) ,
				Array(
					'name' => __('Saint Kitts And Nevis', 'wp-easy-contact') ,
					'slug' => sanitize_title('Saint Kitts And Nevis')
				) ,
				Array(
					'name' => __('Saint Lucia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Saint Lucia')
				) ,
				Array(
					'name' => __('Saint Pierre And Miquelon', 'wp-easy-contact') ,
					'slug' => sanitize_title('Saint Pierre And Miquelon')
				) ,
				Array(
					'name' => __('Saint Vincent And The Grenadines', 'wp-easy-contact') ,
					'slug' => sanitize_title('Saint Vincent And The Grenadines')
				) ,
				Array(
					'name' => __('Samoa', 'wp-easy-contact') ,
					'slug' => sanitize_title('Samoa')
				) ,
				Array(
					'name' => __('San Marino', 'wp-easy-contact') ,
					'slug' => sanitize_title('San Marino')
				) ,
				Array(
					'name' => __('Sao Tome And Principe', 'wp-easy-contact') ,
					'slug' => sanitize_title('Sao Tome And Principe')
				) ,
				Array(
					'name' => __('Saudi Arabia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Saudi Arabia')
				) ,
				Array(
					'name' => __('Senegal', 'wp-easy-contact') ,
					'slug' => sanitize_title('Senegal')
				) ,
				Array(
					'name' => __('Serbia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Serbia')
				) ,
				Array(
					'name' => __('Seychelles', 'wp-easy-contact') ,
					'slug' => sanitize_title('Seychelles')
				) ,
				Array(
					'name' => __('Sierra Leone', 'wp-easy-contact') ,
					'slug' => sanitize_title('Sierra Leone')
				) ,
				Array(
					'name' => __('Singapore', 'wp-easy-contact') ,
					'slug' => sanitize_title('Singapore')
				) ,
				Array(
					'name' => __('Slovakia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Slovakia')
				) ,
				Array(
					'name' => __('Slovenia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Slovenia')
				) ,
				Array(
					'name' => __('Solomon Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Solomon Islands')
				) ,
				Array(
					'name' => __('Somalia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Somalia')
				) ,
				Array(
					'name' => __('South Africa', 'wp-easy-contact') ,
					'slug' => sanitize_title('South Africa')
				) ,
				Array(
					'name' => __('South Georgia And The South Sandwich Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('South Georgia And The South Sandwich Islands')
				) ,
				Array(
					'name' => __('Spain', 'wp-easy-contact') ,
					'slug' => sanitize_title('Spain')
				) ,
				Array(
					'name' => __('Sri Lanka', 'wp-easy-contact') ,
					'slug' => sanitize_title('Sri Lanka')
				) ,
				Array(
					'name' => __('Sudan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Sudan')
				) ,
				Array(
					'name' => __('Suriname', 'wp-easy-contact') ,
					'slug' => sanitize_title('Suriname')
				) ,
				Array(
					'name' => __('Svalbard And Jan Mayen', 'wp-easy-contact') ,
					'slug' => sanitize_title('Svalbard And Jan Mayen')
				) ,
				Array(
					'name' => __('Swaziland', 'wp-easy-contact') ,
					'slug' => sanitize_title('Swaziland')
				) ,
				Array(
					'name' => __('Sweden', 'wp-easy-contact') ,
					'slug' => sanitize_title('Sweden')
				) ,
				Array(
					'name' => __('Switzerland', 'wp-easy-contact') ,
					'slug' => sanitize_title('Switzerland')
				) ,
				Array(
					'name' => __('Syrian Arab Republic', 'wp-easy-contact') ,
					'slug' => sanitize_title('Syrian Arab Republic')
				) ,
				Array(
					'name' => __('Taiwan, Province Of China', 'wp-easy-contact') ,
					'slug' => sanitize_title('Taiwan, Province Of China')
				) ,
				Array(
					'name' => __('Tajikistan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Tajikistan')
				) ,
				Array(
					'name' => __('Tanzania, United Republic Of', 'wp-easy-contact') ,
					'slug' => sanitize_title('Tanzania, United Republic Of')
				) ,
				Array(
					'name' => __('Thailand', 'wp-easy-contact') ,
					'slug' => sanitize_title('Thailand')
				) ,
				Array(
					'name' => __('Timor-leste', 'wp-easy-contact') ,
					'slug' => sanitize_title('Timor-leste')
				) ,
				Array(
					'name' => __('Togo', 'wp-easy-contact') ,
					'slug' => sanitize_title('Togo')
				) ,
				Array(
					'name' => __('Tokelau', 'wp-easy-contact') ,
					'slug' => sanitize_title('Tokelau')
				) ,
				Array(
					'name' => __('Tonga', 'wp-easy-contact') ,
					'slug' => sanitize_title('Tonga')
				) ,
				Array(
					'name' => __('Trinidad And Tobago', 'wp-easy-contact') ,
					'slug' => sanitize_title('Trinidad And Tobago')
				) ,
				Array(
					'name' => __('Tunisia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Tunisia')
				) ,
				Array(
					'name' => __('Turkey', 'wp-easy-contact') ,
					'slug' => sanitize_title('Turkey')
				) ,
				Array(
					'name' => __('Turkmenistan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Turkmenistan')
				) ,
				Array(
					'name' => __('Turks And Caicos Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('Turks And Caicos Islands')
				) ,
				Array(
					'name' => __('Tuvalu', 'wp-easy-contact') ,
					'slug' => sanitize_title('Tuvalu')
				) ,
				Array(
					'name' => __('Uganda', 'wp-easy-contact') ,
					'slug' => sanitize_title('Uganda')
				) ,
				Array(
					'name' => __('Ukraine', 'wp-easy-contact') ,
					'slug' => sanitize_title('Ukraine')
				) ,
				Array(
					'name' => __('United Arab Emirates', 'wp-easy-contact') ,
					'slug' => sanitize_title('United Arab Emirates')
				) ,
				Array(
					'name' => __('United Kingdom', 'wp-easy-contact') ,
					'slug' => sanitize_title('United Kingdom')
				) ,
				Array(
					'name' => __('United States', 'wp-easy-contact') ,
					'slug' => sanitize_title('United States')
				) ,
				Array(
					'name' => __('United States Minor Outlying Islands', 'wp-easy-contact') ,
					'slug' => sanitize_title('United States Minor Outlying Islands')
				) ,
				Array(
					'name' => __('Uruguay', 'wp-easy-contact') ,
					'slug' => sanitize_title('Uruguay')
				) ,
				Array(
					'name' => __('Uzbekistan', 'wp-easy-contact') ,
					'slug' => sanitize_title('Uzbekistan')
				) ,
				Array(
					'name' => __('Vanuatu', 'wp-easy-contact') ,
					'slug' => sanitize_title('Vanuatu')
				) ,
				Array(
					'name' => __('Venezuela', 'wp-easy-contact') ,
					'slug' => sanitize_title('Venezuela')
				) ,
				Array(
					'name' => __('Viet Nam', 'wp-easy-contact') ,
					'slug' => sanitize_title('Viet Nam')
				) ,
				Array(
					'name' => __('Virgin Islands, British', 'wp-easy-contact') ,
					'slug' => sanitize_title('Virgin Islands, British')
				) ,
				Array(
					'name' => __('Virgin Islands, U.S.', 'wp-easy-contact') ,
					'slug' => sanitize_title('Virgin Islands, U.S.')
				) ,
				Array(
					'name' => __('Wallis And Futuna', 'wp-easy-contact') ,
					'slug' => sanitize_title('Wallis And Futuna')
				) ,
				Array(
					'name' => __('Western Sahara', 'wp-easy-contact') ,
					'slug' => sanitize_title('Western Sahara')
				) ,
				Array(
					'name' => __('Yemen', 'wp-easy-contact') ,
					'slug' => sanitize_title('Yemen')
				) ,
				Array(
					'name' => __('Zambia', 'wp-easy-contact') ,
					'slug' => sanitize_title('Zambia')
				) ,
				Array(
					'name' => __('Zimbabwe', 'wp-easy-contact') ,
					'slug' => sanitize_title('Zimbabwe')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'contact_country');
			$set_tax_terms = Array(
				Array(
					'name' => __('AL', 'wp-easy-contact') ,
					'slug' => sanitize_title('AL') ,
					'desc' => __('Alabama', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('AK', 'wp-easy-contact') ,
					'slug' => sanitize_title('AK') ,
					'desc' => __('Alaska', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('AZ', 'wp-easy-contact') ,
					'slug' => sanitize_title('AZ') ,
					'desc' => __('Arizona', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('AR', 'wp-easy-contact') ,
					'slug' => sanitize_title('AR') ,
					'desc' => __('Arkansas', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('CA', 'wp-easy-contact') ,
					'slug' => sanitize_title('CA') ,
					'desc' => __('California', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('CO', 'wp-easy-contact') ,
					'slug' => sanitize_title('CO') ,
					'desc' => __('Colorado', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('CT', 'wp-easy-contact') ,
					'slug' => sanitize_title('CT') ,
					'desc' => __('Connecticut', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('DE', 'wp-easy-contact') ,
					'slug' => sanitize_title('DE') ,
					'desc' => __('Delaware', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('DC', 'wp-easy-contact') ,
					'slug' => sanitize_title('DC') ,
					'desc' => __('District of Columbia', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('FL', 'wp-easy-contact') ,
					'slug' => sanitize_title('FL') ,
					'desc' => __('Florida', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('GA', 'wp-easy-contact') ,
					'slug' => sanitize_title('GA') ,
					'desc' => __('Georgia', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('HI', 'wp-easy-contact') ,
					'slug' => sanitize_title('HI') ,
					'desc' => __('Hawaii', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('ID', 'wp-easy-contact') ,
					'slug' => sanitize_title('ID') ,
					'desc' => __('Idaho', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('IL', 'wp-easy-contact') ,
					'slug' => sanitize_title('IL') ,
					'desc' => __('Illinois', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('IN', 'wp-easy-contact') ,
					'slug' => sanitize_title('IN') ,
					'desc' => __('Indiana', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('IA', 'wp-easy-contact') ,
					'slug' => sanitize_title('IA') ,
					'desc' => __('Iowa', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('KS', 'wp-easy-contact') ,
					'slug' => sanitize_title('KS') ,
					'desc' => __('Kansas', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('KY', 'wp-easy-contact') ,
					'slug' => sanitize_title('KY') ,
					'desc' => __('Kentucky', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('LA', 'wp-easy-contact') ,
					'slug' => sanitize_title('LA') ,
					'desc' => __('Louisiana', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('ME', 'wp-easy-contact') ,
					'slug' => sanitize_title('ME') ,
					'desc' => __('Maine', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('MD', 'wp-easy-contact') ,
					'slug' => sanitize_title('MD') ,
					'desc' => __('Maryland', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('MA', 'wp-easy-contact') ,
					'slug' => sanitize_title('MA') ,
					'desc' => __('Massachusetts', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('MI', 'wp-easy-contact') ,
					'slug' => sanitize_title('MI') ,
					'desc' => __('Michigan', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('MN', 'wp-easy-contact') ,
					'slug' => sanitize_title('MN') ,
					'desc' => __('Minnesota', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('MS', 'wp-easy-contact') ,
					'slug' => sanitize_title('MS') ,
					'desc' => __('Mississippi', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('MO', 'wp-easy-contact') ,
					'slug' => sanitize_title('MO') ,
					'desc' => __('Missouri', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('MT', 'wp-easy-contact') ,
					'slug' => sanitize_title('MT') ,
					'desc' => __('Montana', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('NE', 'wp-easy-contact') ,
					'slug' => sanitize_title('NE') ,
					'desc' => __('Nebraska', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('NV', 'wp-easy-contact') ,
					'slug' => sanitize_title('NV') ,
					'desc' => __('Nevada', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('NH', 'wp-easy-contact') ,
					'slug' => sanitize_title('NH') ,
					'desc' => __('New Hampshire', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('NJ', 'wp-easy-contact') ,
					'slug' => sanitize_title('NJ') ,
					'desc' => __('New Jersey', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('NM', 'wp-easy-contact') ,
					'slug' => sanitize_title('NM') ,
					'desc' => __('New Mexico', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('NY', 'wp-easy-contact') ,
					'slug' => sanitize_title('NY') ,
					'desc' => __('New York', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('NC', 'wp-easy-contact') ,
					'slug' => sanitize_title('NC') ,
					'desc' => __('North Carolina', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('ND', 'wp-easy-contact') ,
					'slug' => sanitize_title('ND') ,
					'desc' => __('North Dakota', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('OH', 'wp-easy-contact') ,
					'slug' => sanitize_title('OH') ,
					'desc' => __('Ohio', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('OK', 'wp-easy-contact') ,
					'slug' => sanitize_title('OK') ,
					'desc' => __('Oklahoma', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('OR', 'wp-easy-contact') ,
					'slug' => sanitize_title('OR') ,
					'desc' => __('Oregon', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('PA', 'wp-easy-contact') ,
					'slug' => sanitize_title('PA') ,
					'desc' => __('Pennsylvania', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('RI', 'wp-easy-contact') ,
					'slug' => sanitize_title('RI') ,
					'desc' => __('Rhode Island', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('SC', 'wp-easy-contact') ,
					'slug' => sanitize_title('SC') ,
					'desc' => __('South Carolina', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('SD', 'wp-easy-contact') ,
					'slug' => sanitize_title('SD') ,
					'desc' => __('South Dakota', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('TN', 'wp-easy-contact') ,
					'slug' => sanitize_title('TN') ,
					'desc' => __('Tennessee', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('TX', 'wp-easy-contact') ,
					'slug' => sanitize_title('TX') ,
					'desc' => __('Texas', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('UT', 'wp-easy-contact') ,
					'slug' => sanitize_title('UT') ,
					'desc' => __('Utah', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('VT', 'wp-easy-contact') ,
					'slug' => sanitize_title('VT') ,
					'desc' => __('Vermont', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('VA', 'wp-easy-contact') ,
					'slug' => sanitize_title('VA') ,
					'desc' => __('Virginia', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('WA', 'wp-easy-contact') ,
					'slug' => sanitize_title('WA') ,
					'desc' => __('Washington', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('WV', 'wp-easy-contact') ,
					'slug' => sanitize_title('WV') ,
					'desc' => __('West Virginia', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('WI', 'wp-easy-contact') ,
					'slug' => sanitize_title('WI') ,
					'desc' => __('Wisconsin', 'wp-easy-contact')
				) ,
				Array(
					'name' => __('WY', 'wp-easy-contact') ,
					'slug' => sanitize_title('WY') ,
					'desc' => __('Wyoming', 'wp-easy-contact')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'contact_state');
			update_option('wp_easy_contact_emd_contact_terms_init', true);
		}
	}
	/**
	 * Set metabox fields,labels,filters, comments, relationships if exists
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function set_filters() {
		do_action('emd_ext_class_init', $this);
		$search_args = Array();
		$filter_args = Array();
		$this->sing_label = __('Contact', 'wp-easy-contact');
		$this->plural_label = __('Contacts', 'wp-easy-contact');
		$this->menu_entity = 'emd_contact';
		$this->boxes['emd_contact_info_emd_contact_0'] = array(
			'id' => 'emd_contact_info_emd_contact_0',
			'title' => __('Contact Info', 'wp-easy-contact') ,
			'app_name' => 'wp_easy_contact',
			'pages' => array(
				'emd_contact'
			) ,
			'context' => 'normal',
		);
		list($search_args, $filter_args) = $this->set_args_boxes();
		if (!post_type_exists($this->post_type) || in_array($this->post_type, Array(
			'post',
			'page'
		))) {
			self::register();
		}
		do_action('emd_set_adv_filtering', $this->post_type, $search_args, $this->boxes, $filter_args, $this->textdomain, $this->plural_label);
		$ent_map_list = get_option(str_replace('-', '_', $this->textdomain) . '_ent_map_list');
	}
	/**
	 * Initialize metaboxes
	 * @since WPAS 4.5
	 *
	 */
	public function set_metabox() {
		if (class_exists('EMD_Meta_Box') && is_array($this->boxes)) {
			foreach ($this->boxes as $meta_box) {
				new EMD_Meta_Box($meta_box);
			}
		}
	}
	/**
	 * Change content for created frontend views
	 * @since WPAS 4.0
	 * @param string $content
	 *
	 * @return string $content
	 */
	public function change_content($content) {
		global $post;
		$layout = "";
		$this->id = $post->ID;
		add_filter('the_title', array(
			$this,
			'change_title_disable_emd_temp'
		) , 10, 2);
		if (get_post_type() == $this->post_type && is_single()) {
			ob_start();
			emd_get_template_part($this->textdomain, 'single', 'emd-contact');
			$layout = ob_get_clean();
		}
		if ($layout != "") {
			$content = $layout;
		}
		remove_filter('the_title', array(
			$this,
			'change_title_disable_emd_temp'
		) , 10, 2);
		return $content;
	}
	/**
	 * Add operations and add new submenu hook
	 * @since WPAS 4.4
	 */
	public function add_menu_link() {
		add_submenu_page(null, __('Operations', 'wp-easy-contact') , __('Operations', 'wp-easy-contact') , 'manage_operations_emd_contacts', 'operations_emd_contact', array(
			$this,
			'get_operations'
		));
	}
	/**
	 * Display operations page
	 * @since WPAS 4.0
	 */
	public function get_operations() {
		if (current_user_can('manage_operations_emd_contacts')) {
			$myapp = str_replace("-", "_", $this->textdomain);
			do_action('emd_operations_entity', $this->post_type, $this->plural_label, $this->sing_label, $myapp, $this->menu_entity);
		}
	}
	public function change_title_text($title) {
		$screen = get_current_screen();
		if ($this->post_type == $screen->post_type) {
			$title = __('Enter Subject here', 'wp-easy-contact');
		}
		return $title;
	}
}
new Emd_Contact;
