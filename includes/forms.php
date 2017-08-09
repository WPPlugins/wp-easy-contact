<?php
/**
 * Setup and Process submit and search forms
 * @package WP_EASY_CONTACT
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
if (is_admin()) {
	add_action('wp_ajax_nopriv_emd_check_unique', 'emd_check_unique');
}
add_action('init', 'wp_easy_contact_form_shortcodes', -2);
/**
 * Start session and setup upload idr and current user id
 * @since WPAS 4.0
 *
 */
function wp_easy_contact_form_shortcodes() {
	global $file_upload_dir;
	$upload_dir = wp_upload_dir();
	$file_upload_dir = $upload_dir['basedir'];
	if (!empty($_POST['emd_action'])) {
		if ($_POST['emd_action'] == 'wp_easy_contact_user_login' && wp_verify_nonce($_POST['emd_login_nonce'], 'emd-login-nonce')) {
			emd_process_login($_POST, 'wp_easy_contact');
		} elseif ($_POST['emd_action'] == 'wp_easy_contact_user_register' && wp_verify_nonce($_POST['emd_register_nonce'], 'emd-register-nonce')) {
			emd_process_register($_POST, 'wp_easy_contact');
		}
	}
}
add_shortcode('contact_submit', 'wp_easy_contact_process_contact_submit');
/**
 * Set each form field(attr,tax and rels) and render form
 *
 * @since WPAS 4.0
 *
 * @return object $form
 */
function wp_easy_contact_set_contact_submit($atts) {
	global $file_upload_dir;
	$show_captcha = 0;
	$form_variables = get_option('wp_easy_contact_glob_forms_list');
	$form_init_variables = get_option('wp_easy_contact_glob_forms_init_list');
	$current_user = wp_get_current_user();
	if (!empty($atts['set'])) {
		$set_arrs = emd_parse_set_filter($atts['set']);
	}
	if (!empty($form_variables['contact_submit']['captcha'])) {
		switch ($form_variables['contact_submit']['captcha']) {
			case 'never-show':
				$show_captcha = 0;
			break;
			case 'show-always':
				$show_captcha = 1;
			break;
			case 'show-to-visitors':
				if (is_user_logged_in()) {
					$show_captcha = 0;
				} else {
					$show_captcha = 1;
				}
			break;
		}
	}
	$req_hide_vars = emd_get_form_req_hide_vars('wp_easy_contact', 'contact_submit');
	$form = new Zebra_Form('contact_submit', 0, 'POST', '', array(
		'class' => 'form-container wpas-form wpas-form-stacked',
		'session_obj' => WP_EASY_CONTACT()->session
	));
	$csrf_storage_method = (isset($form_variables['contact_submit']['csrf']) ? $form_variables['contact_submit']['csrf'] : $form_init_variables['contact_submit']['csrf']);
	if ($csrf_storage_method == 0) {
		$form->form_properties['csrf_storage_method'] = false;
	}
	if (!in_array('emd_contact_first_name', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_contact_first_name', 'emd_contact_first_name', __('First Name', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg form-control',
			'placeholder' => __('First Name', 'wp-easy-contact')
		);
		if (!empty($_GET['emd_contact_first_name'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_contact_first_name']);
		} elseif (!empty($set_arrs['attr']['emd_contact_first_name'])) {
			$attrs['value'] = $set_arrs['attr']['emd_contact_first_name'];
		}
		$obj = $form->add('text', 'emd_contact_first_name', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_contact_first_name', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('First Name is required', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_contact_last_name', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_contact_last_name', 'emd_contact_last_name', __('Last Name', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg form-control',
			'placeholder' => __('Last Name', 'wp-easy-contact')
		);
		if (!empty($_GET['emd_contact_last_name'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_contact_last_name']);
		} elseif (!empty($set_arrs['attr']['emd_contact_last_name'])) {
			$attrs['value'] = $set_arrs['attr']['emd_contact_last_name'];
		}
		$obj = $form->add('text', 'emd_contact_last_name', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_contact_last_name', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Last Name is required', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_contact_email', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_contact_email', 'emd_contact_email', __('Email', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg form-control',
			'placeholder' => __('Email', 'wp-easy-contact')
		);
		if (!empty($current_user) && !empty($current_user->user_email)) {
			$attrs['value'] = (string)$current_user->user_email;
		}
		if (!empty($_GET['emd_contact_email'])) {
			$attrs['value'] = sanitize_email($_GET['emd_contact_email']);
		} elseif (!empty($set_arrs['attr']['emd_contact_email'])) {
			$attrs['value'] = $set_arrs['attr']['emd_contact_email'];
		}
		$obj = $form->add('text', 'emd_contact_email', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
			'email' => array(
				'error',
				__('Email: Please enter a valid email address', 'wp-easy-contact')
			) ,
		);
		if (in_array('emd_contact_email', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Email is required', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_contact_phone', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_contact_phone', 'emd_contact_phone', __('Phone', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg form-control',
			'placeholder' => __('Phone', 'wp-easy-contact')
		);
		if (!empty($_GET['emd_contact_phone'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_contact_phone']);
		} elseif (!empty($set_arrs['attr']['emd_contact_phone'])) {
			$attrs['value'] = $set_arrs['attr']['emd_contact_phone'];
		}
		$obj = $form->add('text', 'emd_contact_phone', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_contact_phone', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Phone is required', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_contact_address', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_contact_address', 'emd_contact_address', __('Address', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg form-control',
			'placeholder' => __('Address', 'wp-easy-contact')
		);
		if (!empty($_GET['emd_contact_address'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_contact_address']);
		} elseif (!empty($set_arrs['attr']['emd_contact_address'])) {
			$attrs['value'] = $set_arrs['attr']['emd_contact_address'];
		}
		$obj = $form->add('text', 'emd_contact_address', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_contact_address', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Address is required', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_contact_city', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_contact_city', 'emd_contact_city', __('City', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg form-control',
			'placeholder' => __('City', 'wp-easy-contact')
		);
		if (!empty($_GET['emd_contact_city'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_contact_city']);
		} elseif (!empty($set_arrs['attr']['emd_contact_city'])) {
			$attrs['value'] = $set_arrs['attr']['emd_contact_city'];
		}
		$obj = $form->add('text', 'emd_contact_city', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_contact_city', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('City is required', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('contact_state', $req_hide_vars['hide'])) {
		$form->add('label', 'label_contact_state', 'contact_state', __('State', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg'
		);
		if (!empty($_GET['contact_state'])) {
			$attrs['value'] = sanitize_text_field($_GET['contact_state']);
		} elseif (!empty($set_arrs['tax']['contact_state'])) {
			$attrs['value'] = $set_arrs['tax']['contact_state'];
		}
		$obj = $form->add('selectadv', 'contact_state', __('Please Select', 'wp-easy-contact') , $attrs, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-easy-contact") . '","placeholderOption":"first"}');
		//get taxonomy values
		$txn_arr = Array();
		$txn_arr[''] = __('Please Select', 'wp-easy-contact');
		$txn_obj = get_terms('contact_state', array(
			'hide_empty' => 0
		));
		foreach ($txn_obj as $txn) {
			$txn_arr[$txn->slug] = $txn->name;
		}
		$obj->add_options($txn_arr);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('contact_state', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('State is required!', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('emd_contact_zipcode', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_emd_contact_zipcode', 'emd_contact_zipcode', __('Zip Code', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg form-control',
			'placeholder' => __('Zip Code', 'wp-easy-contact')
		);
		if (!empty($_GET['emd_contact_zipcode'])) {
			$attrs['value'] = sanitize_text_field($_GET['emd_contact_zipcode']);
		} elseif (!empty($set_arrs['attr']['emd_contact_zipcode'])) {
			$attrs['value'] = $set_arrs['attr']['emd_contact_zipcode'];
		}
		$obj = $form->add('text', 'emd_contact_zipcode', '', $attrs);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('emd_contact_zipcode', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Zip Code is required', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('contact_country', $req_hide_vars['hide'])) {
		$form->add('label', 'label_contact_country', 'contact_country', __('Country', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg'
		);
		if (!empty($_GET['contact_country'])) {
			$attrs['value'] = sanitize_text_field($_GET['contact_country']);
		} elseif (!empty($set_arrs['tax']['contact_country'])) {
			$attrs['value'] = $set_arrs['tax']['contact_country'];
		}
		$obj = $form->add('selectadv', 'contact_country', __('Please Select', 'wp-easy-contact') , $attrs, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-easy-contact") . '","placeholderOption":"first"}');
		//get taxonomy values
		$txn_arr = Array();
		$txn_arr[''] = __('Please Select', 'wp-easy-contact');
		$txn_obj = get_terms('contact_country', array(
			'hide_empty' => 0
		));
		foreach ($txn_obj as $txn) {
			$txn_arr[$txn->slug] = $txn->name;
		}
		$obj->add_options($txn_arr);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('contact_country', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Country is required!', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('contact_topic', $req_hide_vars['hide'])) {
		$form->add('label', 'label_contact_topic', 'contact_topic', __('Topic', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg'
		);
		if (!empty($_GET['contact_topic'])) {
			$attrs['value'] = sanitize_text_field($_GET['contact_topic']);
		} elseif (!empty($set_arrs['tax']['contact_topic'])) {
			$attrs['value'] = $set_arrs['tax']['contact_topic'];
		}
		$obj = $form->add('selectadv', 'contact_topic', __('Please Select', 'wp-easy-contact') , $attrs, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-easy-contact") . '","placeholderOption":"first"}');
		//get taxonomy values
		$txn_arr = Array();
		$txn_arr[''] = __('Please Select', 'wp-easy-contact');
		$txn_obj = get_terms('contact_topic', array(
			'hide_empty' => 0
		));
		foreach ($txn_obj as $txn) {
			$txn_arr[$txn->slug] = $txn->name;
		}
		$obj->add_options($txn_arr);
		$zrule = Array(
			'dependencies' => array() ,
		);
		if (in_array('contact_topic', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Topic is required!', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('blt_title', $req_hide_vars['hide'])) {
		//text
		$form->add('label', 'label_blt_title', 'blt_title', __('Subject', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$attrs = array(
			'class' => 'input-lg form-control',
			'placeholder' => __('Subject', 'wp-easy-contact')
		);
		if (!empty($_GET['blt_title'])) {
			$attrs['value'] = sanitize_text_field($_GET['blt_title']);
		} elseif (!empty($set_arrs['attr']['blt_title'])) {
			$attrs['value'] = $set_arrs['attr']['blt_title'];
		}
		$obj = $form->add('text', 'blt_title', '', $attrs);
		$zrule = Array();
		if (in_array('blt_title', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Subject is required', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	if (!in_array('blt_content', $req_hide_vars['hide'])) {
		//wysiwyg
		$form->add('label', 'label_blt_content', 'blt_content', __('Message', 'wp-easy-contact') , array(
			'class' => 'control-label'
		));
		$obj = $form->add('wysiwyg', 'blt_content', '', array(
			'placeholder' => __('Enter text ...', 'wp-easy-contact') ,
			'style' => 'width: 100%; height: 200px',
			'class' => 'wyrj'
		));
		$zrule = Array();
		if (in_array('blt_content', $req_hide_vars['req'])) {
			$zrule = array_merge($zrule, Array(
				'required' => array(
					'error',
					__('Message is required', 'wp-easy-contact')
				)
			));
		}
		$obj->set_rule($zrule);
	}
	//hidden_func
	$emd_contact_id = emd_get_hidden_func('autoinc');
	$form->add('hidden', 'emd_contact_id', $emd_contact_id);
	//hidden
	$obj = $form->add('hidden', 'wpas_form_name', 'contact_submit');
	//hidden_func
	$wpas_form_submitted_by = emd_get_hidden_func('user_login');
	$form->add('hidden', 'wpas_form_submitted_by', $wpas_form_submitted_by);
	//hidden_func
	$wpas_form_submitted_ip = emd_get_hidden_func('user_ip');
	$form->add('hidden', 'wpas_form_submitted_ip', $wpas_form_submitted_ip);
	$ext_inputs = Array();
	$ext_inputs = apply_filters('emd_ext_form_inputs', $ext_inputs, 'wp_easy_contact', 'contact_submit');
	foreach ($ext_inputs as $input_param) {
		$inp_name = $input_param['name'];
		if (!in_array($input_param['name'], $req_hide_vars['hide'])) {
			if ($input_param['type'] == 'hidden') {
				$obj = $form->add('hidden', $input_param['name'], $input_param['vals']);
			} elseif ($input_param['type'] == 'select') {
				$form->add('label', 'label_' . $input_param['name'], $input_param['name'], $input_param['label'], array(
					'class' => 'control-label'
				));
				$ext_class['class'] = 'input-md';
				if (!empty($input_param['multiple'])) {
					$ext_class['multiple'] = 'multiple';
					$input_param['name'] = $input_param['name'] . '[]';
				}
				$obj = $form->add('select', $input_param['name'], '', $ext_class, '', '{"allowClear":true,"placeholder":"' . __("Please Select", "wp-easy-contact") . '","placeholderOption":"first"}');
				$obj->add_options($input_param['vals']);
				$obj->disable_spam_filter();
			} elseif ($input_param['type'] == 'text') {
				$form->add('label', 'label_' . $input_param['name'], $input_param['name'], $input_param['label'], array(
					'class' => 'control-label'
				));
				$obj = $form->add('text', $input_param['name'], '', array(
					'class' => 'input-md form-control',
					'placeholder' => $input_param['label']
				));
			} elseif ($input_param['type'] == 'checkbox') {
				$form->add('label', 'label_' . $input_param['name'] . '_1', $input_param['name'] . '_1', $input_param['label'], array(
					'class' => 'control-label'
				));
				$obj = $form->add('checkbox', $input_param['name'], 1, $input_param['default']);
				$obj->set_attributes(array(
					'class' => 'input_' . $input_param['name'] . ' control checkbox',
				));
			}
			if ($input_param['type'] != 'hidden' && in_array($inp_name, $req_hide_vars['req'])) {
				$zrule = Array(
					'dependencies' => $input_param['dependencies'],
					'required' => array(
						'error',
						$input_param['label'] . __(' is required', 'wp-easy-contact')
					)
				);
				$obj->set_rule($zrule);
			}
		}
	}
	$cust_fields = Array();
	$cust_fields = apply_filters('emd_get_cust_fields', $cust_fields, 'emd_contact');
	foreach ($cust_fields as $ckey => $clabel) {
		if (!in_array($ckey, $req_hide_vars['hide'])) {
			$form->add('label', 'label_' . $ckey, $ckey, $clabel, array(
				'class' => 'control-label'
			));
			$obj = $form->add('text', $ckey, '', array(
				'class' => 'input-lg form-control',
				'placeholder' => $clabel
			));
			if (in_array($ckey, $req_hide_vars['req'])) {
				$zrule = Array(
					'required' => array(
						'error',
						$clabel . __(' is required', 'wp-easy-contact')
					)
				);
				$obj->set_rule($zrule);
			}
		}
	}
	$form->assign('show_captcha', $show_captcha);
	if ($show_captcha == 1) {
		//Captcha
		$form->add('captcha', 'captcha_image', 'captcha_code', '', '<span style="font-weight:bold;" class="refresh-txt">Refresh</span>', 'refcapt');
		$form->add('label', 'label_captcha_code', 'captcha_code', __('Please enter the characters with black color.', 'wp-easy-contact'));
		$obj = $form->add('text', 'captcha_code', '', array(
			'placeholder' => __('Code', 'wp-easy-contact')
		));
		$obj->set_rule(array(
			'required' => array(
				'error',
				__('Captcha is required', 'wp-easy-contact')
			) ,
			'captcha' => array(
				'error',
				__('Characters from captcha image entered incorrectly!', 'wp-easy-contact')
			)
		));
	}
	$form->add('submit', 'singlebutton_contact_submit', '' . __('Send', 'wp-easy-contact') . ' ', array(
		'class' => 'wpas-button wpas-juibutton-secondary wpas-button-large  col-md-12 col-lg-12 col-xs-12 col-sm-12'
	));
	return $form;
}
/**
 * Process each form and show error or success
 *
 * @since WPAS 4.0
 *
 * @return html
 */
function wp_easy_contact_process_contact_submit($atts) {
	$show_form = 1;
	$access_views = get_option('wp_easy_contact_access_views', Array());
	if (!current_user_can('view_contact_submit') && !empty($access_views['forms']) && in_array('contact_submit', $access_views['forms'])) {
		$show_form = 0;
	}
	$form_init_variables = get_option('wp_easy_contact_glob_forms_init_list');
	$form_variables = get_option('wp_easy_contact_glob_forms_list');
	if ($show_form == 1) {
		if (!empty($form_init_variables['contact_submit']['login_reg'])) {
			$show_login_register = (isset($form_variables['contact_submit']['login_reg']) ? $form_variables['contact_submit']['login_reg'] : $form_init_variables['contact_submit']['login_reg']);
			if (!is_user_logged_in() && $show_login_register != 'none') {
				do_action('emd_show_login_register_forms', 'wp_easy_contact', 'contact_submit', $show_login_register);
				return;
			}
		}
		wp_enqueue_script('wpas-jvalidate-js');
		wp_enqueue_style('wpasui');
		wp_enqueue_style('contact-submit-forms');
		wp_enqueue_script('contact-submit-forms-js');
		wp_easy_contact_enq_custom_css();
		do_action('emd_ext_form_enq', 'wp_easy_contact', 'contact_submit');
		$success_msg = (isset($form_variables['contact_submit']['success_msg']) ? $form_variables['contact_submit']['success_msg'] : $form_init_variables['contact_submit']['success_msg']);
		$error_msg = (isset($form_variables['contact_submit']['error_msg']) ? $form_variables['contact_submit']['error_msg'] : $form_init_variables['contact_submit']['error_msg']);
		return emd_submit_php_form('contact_submit', 'wp_easy_contact', 'emd_contact', 'publish', 'publish', $success_msg, $error_msg, 0, 1, $atts);
	} else {
		$noaccess_msg = (isset($form_variables['contact_submit']['noaccess_msg']) ? $form_variables['contact_submit']['noaccess_msg'] : $form_init_variables['contact_submit']['noaccess_msg']);
		return "<div class='alert alert-info not-authorized'>" . $noaccess_msg . "</div>";
	}
}
