<?php
/**
 *
 * Lnaguage Tools Extension for the phpBB Forum Software package
 * @author MG
* @copyright (c) orynider <http://mxpcms.sourceforge.net>
* @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace orynider\translateaddkey\controller;
use orynider\translateaddkey\core\translatorconst;
/**
 * acp_translateaddkey use orynider\translateaddkey\core\translateaddkey;
 * 
 * @package Translator
 * @author MG
 * @copyright Copyright (c) 2008
 * @version $Id: translateaddkey.php,v 1.5 2008/02/29 15:36:48 orynider Exp $
 * @access public
 */
class translateaddkey extends \orynider\translateaddkey\core\translateaddkey
{
	
	/**
	 * Display the options a user can configure for this extension
	 *
	 * @return void
	 * @access public
	 */
	public function display_settings($tpl_name, $page_title)
	{
		$this->tpl_name = $tpl_name;
		$this->page_title = $page_title;
		
		// Create a form key for preventing CSRF attacks
		add_form_key($tpl_name);
		// Create an array to collect errors that will be output to the user
		$errors = array();		
		// Is the form being submitted to us?
		if ($submit = $this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key($tpl_name))
			{
				$errors[] = $this->lang->lang('FORM_INVALID');
			}
			
			$s_errors = (bool) count($errors);
			
			$this->config->set('translator_default_lang', ($this->request->variable('translator_default_lang', 'en')));
			$this->config->set('translator_choice_lang', ($this->request->variable('translator_choice_lang', 'de,fr,es,ro')));
		
			// If no errors, process the form data
			if (empty($errors))
			{
				// Add option settings change action to the admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_TRANSLATOR_SETTINGS_LOG');
				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->lang('ACP_TRANSLATOR_SETTINGS_CHANGED') . adm_back_link($this->u_action));
			}		
			trigger_error($this->lang('TRANSLATOR_CONFIG_SAVED') . adm_back_link($this->u_action));
		}
		
		$this->assign_template_vars($this->template);
		$this->template->assign_vars(array(
			'TRANSLATOR_DEFAULT_LANG'	=> (isset($this->config['translator_default_lang'])) ? $this->config['translator_default_lang'] : 'error',
			'TRANSLATOR_CHOICE_LANG'		=> (isset($this->config['translator_choice_lang'])) ? $this->config['translator_choice_lang'] : 'error',
			'U_ACTION'									=> $this->u_action,
		));
	}
	
	/**
	 * Display the options a user can configure for this extension
	 *
	 * @return void
	 * @access public
	 */
	public function display_translate($tpl_name, $page_title)
	{
		if (!defined('IN_AJAX'))
		{
			define('IN_AJAX', (isset($_GET['ajax']) && ($this->ajax == 1) && ($this->server['HTTP_SEREFER'] = $this->server['PHP_SELF'])) ? 1 : 0);
		}
		
		$phpEx = $this->php_ext;
		
		// Requests
		$action = $this->request->variable('action', '');
		$page_id = $this->request->variable('page_id', 0);
		$currency_id = $this->request->variable('currency_id', 0);		
		$this->parent_id = $this->request->variable('parent_id', 0);		
		
		/* general vars */
		$mode = $this->request->variable('mode', 'phpbb');
		$start = $this->request->variable('start', 0);  
		$s = $this->request->variable('mode', 'generate');	
		
		/* */	
		if (IN_AJAX == 0)
		{
			$lang = $this->user->lang;
			$lang['ENCODING'] = $this->file_encoding;
			if ($this->request->is_set_post('save') || $this->request->is_set_post('download'))
			{
				$this->file_preparesave();
			}
			if ($this->request->is_set_post('save'))
			{
				$this->file_save();
			}
			else if ($this->request->is_set_post('download'))
			{
				$this->file_download();
			}			
			
			$this->user->add_lang('acp/board');							
			
			// Create a form key for preventing CSRF attacks
			add_form_key($tpl_name);
			
			// Create an array to collect errors that will be output to the user
			$errors = array();		
			
			// Is the form being submitted to us?
			if ($submit = $this->request->is_set_post('submit'))
			{
				// Test if the submitted form is valid
				if (!check_form_key($tpl_name))
				{
					$errors[] = $this->lang->lang('FORM_INVALID');
				}
				
				$s_errors = (bool) count($errors);			
				
				// If no errors, process the form data
				if (empty($errors))
				{
					// Add option settings change action to the admin log
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_TRANSLATOR_SETTINGS_LOG');
					// Option settings have been updated and logged
					// Confirm this to the user and provide link back to previous page
					trigger_error($this->lang('ACP_TRANSLATOR_SETTINGS_CHANGED') . adm_back_link($this->u_action));
				}		
				trigger_error($this->lang('TRANSLATOR_CONFIG_SAVED') . adm_back_link($this->u_action));
			}		
					
			$this->cache->destroy('_translator');
			$this->cache->destroy('_translateaddkey_module');			
			$this->template->assign_block_vars('file_to_translate_select', array());
			
			$basename = basename( __FILE__);
			
			$mx_root_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $this->root_path;
			$module_root_path = $this->root_path . 'ext/orynider/translateaddkey/';
			$admin_module_root_path = $this->root_path . 'adm/';		

			$s_action = $admin_module_root_path . $basename;
			$params = $this->request->server('QUERY_STRING');
					
			if ($this->request->is_set_post('submit'))
			{
				if (!check_form_key('orynider/translateaddkey'))
				{
					trigger_error('FORM_INVALID', E_USER_WARNING);
				}
			}			
			/** -------------------------------------------------------------------------
			* Extend User Style with module lang and images
			* Usage:  $user->extend(LANG, IMAGES, '_core', 'img_file_in_dir', 'img_file_ext')
			* Switches:
			* - LANG: translatorconst::MX_LANG_MAIN (default), translatorconst::MX_LANG_ADMIN, translatorconst::MX_LANG_ALL, translatorconst::MX_LANG_NONE
			* - IMAGES: translatorconst::MX_IMAGES (default), translatorconst::MX_IMAGES_NONE
			** ------------------------------------------------------------------------- */
			$this->extend(false, false, 'all', 'icon_info', false);				
			
			/**
			* Reset custom module default style, once used.
			*/
			if (@file_exists($this->user_current_style_path . 'images/menu_icons/icon_info.gif'))
			{
				$img_info = $this->user_current_style_path . 'images/menu_icons/icon_info.gif';
			}
			else
			{
				$img_info = $this->default_current_style_path . 'images/menu_icons/icon_info.gif';
			}
			
			if (@file_exists( $this->user_current_style_path . 'images/menu_icons/icon_google.gif'))
			{
				$img_google = $this->user_current_style_path . 'images/menu_icons/icon_google.gif';
			}
			else
			{
				$img_google = $this->default_current_style_path . 'images/menu_icons/icon_google.gif';
			}			
			
			$params = !empty($params) ? $params : "&i=-orynider-translateaddkey-acp-translateaddkey_module&mode=".$mode;
			$this->u_action = !empty($this->u_action) ? $this->u_action : '';
			
			/* * /	
			print_r($this->gen_select_list( 'html', 'dirs', $this->dir_select)); 
			/* */					
			$this->template->assign_vars(array( // #
				'TH_COLOR2' => isset($theme['th_color2']) ? isset($theme['th_color2']) : '#fff',
				
				'S_LANGUAGE_INTO' 	=> $this->gen_select_list( 'html', 'language', $this->language_into, $this->language_from),
				'S_MODULE_LIST' 			=> $this->gen_select_list( 'html', 'modules', $this->module_select),
				'S_DIR_LIST' 					=> $this->gen_select_list( 'html', 'dirs', $this->dir_select),
				'S_FILE_LIST' 					=> $this->gen_select_list('html', 'files', $this->module_file),				
				'S_ACTION' 					=> $this->u_action . '?' . str_replace('&amp;', '&', $params),
				'S_ACTION_AJAX' 			=> $this->u_action . '?' . str_replace('&amp;', '&', $params) . '&ajax=1',
				
				'L_RESET' 						=> isset($this->user->lang['RESET']) ? $this->user->lang['RESET'] : 'Reset',
				'IMG_INFO' 					=> $img_info,
				'IMG_GOOGLE' 			=> $img_google,				
				'I_LANGUAGE' 				=> $this->language_into,
				'I_MODULE' 					=> $this->module_select,
				'I_DIR' 							=> $this->dir_select,
				'I_FILE' 							=> $this->module_file,			
			));		
			
			/* */
			$this->assign_template_vars($this->template);
			$this->template->assign_vars( array( // #
				'L_MX_MODULES' =>  isset($this->user->lang['MX_MODULES']) ? $this->user->lang['MX_MODULES'] : 'MX_Modules',
			));
			
			if (($this->s == 'MODS') || ($this->s == 'phpbb_ext') || ($this->s == 'phpbb_ext_add'))
			{
				$this->template->assign_block_vars('file_to_translate_select.modules', array());
				$this->template->assign_block_vars('modules', array());
			}

			$this->file_translate();			
		}
		else
		{ // AJAX
			$tpl_name = 'selects';
			//$this->template->set_filenames( array('body' => 'selects.html'));
			add_form_key($tpl_name);			
			$style = "width: 100%;"; 
			if ($this->into == 'language')
			{
				$option_list = $this->gen_select_list('html', 'language', $this->language_into, $this->language_from);
				$name = 'language[into]';
				$id = 'f_lang_into';
			}
			/* */
			if ($this->into == 'modules')
			{		
				$option_list = $this->gen_select_list('html', 'modules', $this->module_select);
				$name = 'translate[module]';
				$id = 'f_select_file';
			}			
			/* */
			if ($this->into == 'dirs')
			{		
				$option_list = $this->gen_select_list('html', 'dirs', $this->dir_select);
				$name = 'translate[dir]';
				$id = 'f_select_file';
			}
			/* */			
			if ($this->into == 'files')
			{		
				$option_list 	= $this->gen_select_list('html', 'files', $this->module_file);
				$name 			= 'translate[file]';
				$id 				= 'f_select_file';
			}			
			$this->template->assign_block_vars('ajax_select', array(
				'NAME'			=> $name,
				'ID'				=> $id,
				'STYLE'			=> $style,
				'OPTIONS'	=> $option_list,
			));
		}
	}	
	
	public function manage_translateaddkey($tpl_name, $page_title)
	{
		if (!defined('IN_AJAX'))
		{
			define('IN_AJAX', (isset($_GET['ajax']) && ($this->ajax == 1) && ($this->server['HTTP_SEREFER'] = $this->server['PHP_SELF'])) ? 1 : 0);
		}
		
		$phpEx = $this->php_ext;
		
		// Requests
		$action = $this->request->variable('action', 'search');
		$page_id = $this->request->variable('page_id', 0);
		$currency_id = $this->request->variable('currency_id', 0);		
		$parent_id = $this->parent_id = $this->request->variable('parent_id', 0);		
		
		/* general vars */
		$mode = $this->request->variable('mode', 'generate');
		$start = $this->request->variable('start', 0);  
		$s = $this->request->variable('mode', 'generate');			
			
		$this->user->add_lang('acp/board');							
			
		// Create a form key for preventing CSRF attacks
		add_form_key($tpl_name);	
			
		/* START Include language file lang_admin_extend_lang or all module language files */
		$language = ($this->user->user_language_name) ? $this->user->user_language_name : (($this->config['default_lang']) ? $this->config['default_lang'] : 'english');
		$this->user->add_lang_ext($this->ext_name, array('common', 'info_acp_admin_extend_lang'));
		
		$lang = $this->user->lang;
		
		set_time_limit(0);
		$mem_limit = $this->check_mem_limit();
		ini_set('memory_limit', $mem_limit);

		$value_maxlength = 250;

		// Remove the ADMIN / NORMAL options => force $_POST['search_admin'] = 2 options
		//$this->request->variable('search_admin', 2) = 2;
		//$this->request->variable('new_level', 'normal') = 'normal';

		// get languages installed
		$this->countries = $this->get_countries();

		// get packs installed
		$this->packs = $this->get_packs();
		
		// get entries (all lang keys)
		$this->entries = $this->get_entries();
		
		// get parameters
		$action = $this->request->variable('action', '');
		$level = $this->request->variable('level', 'normal');

		// pack file
		$pack_file = urldecode($this->request->variable('pack_file', ''));
		$pack = urldecode($this->request->variable('pack', './../language/en_us/common.php'));
		$pack_file = empty($pack_file) ? basename($pack) : $pack_file;
		
		if (!isset($this->packs[$pack_file]))
		{
			$pack_file = '';
			$action = '';
		}

		// keys
		$key_main = $this->request->variable('key_main', '');
		$key_main = empty($key_main) ? $this->request->variable('key', '') : $key_main;

		$key_sub = $this->request->variable('key_sub', '');
		$key_sub = empty($key_sub) ? $this->request->variable('sub', '') : $key_sub;

		if (empty($key_main))
		{
			$key_main = '';
			$key_sub = '';
		}
		if (!isset($this->entries['admin'][$key_main][$key_sub]))
		{
			$key_main = '';
			$key_sub = '';
		}

		// buttons
		$submit =  $this->request->is_set_post('submit'); 
		$delete = $this->request->is_set_post('delete');
		$cancel =$this->request->is_set_post('cancel');
		$add = $this->request->is_set_post('add');
		
		$l_admin = $this->language->lang('Lang_extend_level_admin');
		$l_normal = $this->language->lang('Lang_extend_level_normal');
		
		if ($add || $delete)
		{
			$action= 'key';
		}
		if (($action == 'key') && ($pack_file == ''))
		{
			$action = '';
		}

		if (($action == '') && $submit)
		{
			$action = 'search';
		}
			
		// key modification
		if ($action == 'key')
		{
			if ($delete)
			{
				$new_entries = array();
				
				@reset($this->entries['admin']);
				
				while (list($new_main, $subs) = each($this->entries['admin']))
				{
					@reset($subs);
					
					while (list($new_sub, $admin) = @each($subs))
					{
						if (($new_main != $key_main) || ($new_sub != $key_sub))
						{
							$new_entries['admin'][$new_main][$new_sub] = $this->entries['admin'][$new_main][$new_sub];
							$new_entries['pack'][$new_main][$new_sub] = $this->entries['pack'][$new_main][$new_sub];
							$new_entries['value'][$new_main][$new_sub] = $this->entries['value'][$new_main][$new_sub];
						}
					}
				}

				// write the result
				$this->write($new_entries);

				// send message
				$pack_url = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=' . (($level == 'normal') ? 'normal' : 'admin'));
				msg_handler(E_USER_ERROR, sprintf($this->user->lang['Lang_extend_delete_done'], '<a href="' . $pack_url . '">', '</a>'), __FILE__, __LINE__);

				// back to the list
				$action = 'pack';
				$delete = false;
			}
			elseif ($cancel)
			{
				// back to list
				$action = 'pack';
				$cancel = false;
			}
			elseif ($submit)
			{
				// get formular
				$new_main = $this->request->variable('new_main', 0, false, \phpbb\request\request_interface::REQUEST);
				$new_sub = $this->request->variable('new_sub', 0, false, \phpbb\request\request_interface::REQUEST);
				$new_level = $this->request->variable('new_level', 0, false, \phpbb\request\request_interface::REQUEST);
				$new_values = $this->request->variable('new_values', 0, false, \phpbb\request\request_interface::REQUEST);
				$new_pack = $this->request->variable('new_pack', 0, false, \phpbb\request\request_interface::REQUEST);

				// force
				if (!in_array($new_level, array('normal', 'admin')))
				{
					$new_level = 'normal';
				}

				// check values
				$error = false;
				$error_msg = false;
				
				$this->user->add_lang_ext($this->ext_name, array('common', 'info_acp_admin_extend_lang'));				
				$lang = $this->user->lang;				
				
				$dft_country = $this->config['default_lang'];
				
				@reset($this->countries);
				
				while (list($country_dir, $country_name) = each($this->countries))
				{
					if (empty($new_values[$country_dir]))
					{
						$new_values[$country_dir] = $new_values[$dft_country];
					}
					if (empty($new_values[$country_dir]) && ($dft_country != 'en'))
					{
						$new_values[$country_dir] = $new_values['en'];
					}
					if (empty($new_values[$country_dir]) && !$error)
					{
						$error = true;
						$error_msg .= (empty($error_msg) ? '' : '<br /><br />') . $this->user->lang['Lang_extend_missing_value'];
					}
				}

				// empty key
				if (empty($new_main))
				{
					$error = true;
					$error_msg .= (empty($error_msg) ? '' : '<br /><br />') . $this->user->lang['Lang_extend_key_missing'];
				}

				// we changed the key or create a new one
				if (!empty($new_main) && (($new_main != $key_main) || ($new_sub != $key_sub)))
				{
					// does the new key already exists ?
					if (isset($this->entries['admin'][$new_key][$new_sub]))
					{
						$error = true;
						$error_msg .= (empty($error_msg) ? '' : '<br /><br />') . sprintf($this->user->lang['Lang_extend_duplicate_entry'], $this->get_lang($this->entries['pack'][$new_key][$new_sub]));
					}
				}

				// error
				if ($error)
				{
					msg_handler(E_USER_ERROR, '<br />' . $error_msg . '<br /><br />', __FILE__, __LINE__);
					exit;
				}
				
				// perform the update
				$this->entries['pack'][$new_main][$new_sub] = $new_pack;
				$this->entries['admin'][$new_main][$new_sub] = ($new_level == 'admin');
				
				@reset($new_values);
				
				while (list($new_country, $new_value) = @each($new_values))
				{
					if (!empty($new_value))
					{
						$this->entries['value'][$new_main][$new_sub][$new_country] = $new_value;
					}
				}

				// write the result
				$this->write($this->entries);

				// send message
				$key_url = append_sid($this->u_action . '&amp;action=key&amp;pack=' . urlencode($new_pack) . '&amp;key=' . urlencode($new_main) . '&amp;sub=' . urlencode($new_sub) . '&amp;level=' . urlencode(($new_level == 'normal') ? 'normal' : 'admin'));
				$pack_url = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($new_pack) . '&amp;level=' . (($new_level == 'normal') ? 'normal' : 'admin'));
				
				msg_handler(E_USER_ERROR, sprintf($this->user->lang['Lang_extend_update_done'], '<a href="' . $key_url . '">','</a>', '<a href="' . $pack_url . '">', '</a>'), __FILE__, __LINE__);
			}
			else
			{			
				$params = !empty($params) ? $params : "&i=-orynider-translateaddkey-acp-translateaddkey_module&mode=".$mode;
				$this->u_action = !empty($this->u_action) ? $this->u_action : '?sid=' . $this->user->session_id;
				
				$this->user->add_lang_ext($this->ext_name, array('common', 'info_acp_admin_extend_lang'));				
				$lang = $this->user->lang;
				
				// template
				$tpl_name = 'lang_user_created_key_body';
				$this->template->set_filenames(array('body' => $tpl_name));				
			
				// Create a form key for preventing CSRF attacks
				add_form_key($tpl_name);
				
				// header
				$this->template->assign_vars(array(
					'L_TITLE'					=> $this->user->lang['Lang_extend'],
					'L_TITLE_EXPLAIN'		=> $this->user->lang['Lang_extend_explain'],
					'L_KEY'						=> $this->user->lang['Lang_extend_entry'],
					'L_LANGUAGES'		=> $this->user->lang['Languages'],

					'L_SUBMIT'				=> $this->user->lang['SUBMIT'],
					'L_DELETE'				=> $this->user->lang['DELETE'],
					'L_CANCEL'				=> $this->user->lang['CANCEL'],
					)
				);
				
				// pack list
				$s_packs = '';
				@reset($this->packs);
				
				while (list($file, $name) = each($this->packs))
				{
					$selected = ($file == $pack_file) ? ' selected="selected"' : '';
					/* MG Lang DB - BEGIN */
					$s_packs .= '<option value="' . $file . '"' . $selected . '>' . $name . '</option>';
					/* MG Lang DB - END */
				}
				
				if (!empty($s_packs))
				{
					$s_packs = sprintf('<select name="new_pack">%s</select>', $s_packs);
				}
				
				// vars
				$this->template->assign_vars(array(
					'L_KEY_MAIN'					=> $this->user->lang['Lang_extend_key_main'],
					'L_KEY_MAIN_EXPLAIN'		=> $this->user->lang['Lang_extend_key_main_explain'],
					'KEY_MAIN'						=> $key_main,
					'L_KEY_SUB'						=> $this->user->lang['Lang_extend_key_sub'],
					'L_KEY_SUB_EXPLAIN'		=> $this->user->lang['Lang_extend_key_sub_explain'],
					'KEY_SUB'							=> $key_sub,

					'L_PACK'							=> $this->user->lang['Lang_extend_pack'],
					'L_PACK_EXPLAIN'			=> $this->user->lang['Lang_extend_pack_explain'],
					'S_PACKS'							=> $s_packs,

					'L_LEVEL'							=> $this->user->lang['Lang_extend_level'],
					'L_LEVEL_EXPLAIN'			=> $this->user->lang['Lang_extend_level_explain'],
					'LEVEL_NORMAL'				=> 'normal',
					'L_EDIT'							=> $this->user->lang['Lang_extend_level_edit'],
					'S_LEVEL_NORMAL'			=> ($level == 'normal') ? 'checked="checked"' : '',
					'L_LEVEL_NORMAL'			=> $this->user->lang['Lang_extend_level_normal'],
					'LEVEL_ADMIN'					=> 'admin',
					'S_LEVEL_ADMIN'				=> ($level != 'normal') ? 'checked="checked"' : '',
					'L_LEVEL_ADMIN'				=> $this->user->lang['Lang_extend_level_admin'],

					'L_PACKS'							=> $this->user->lang['Lang_extend_pack'],
					'L_PACKS'							=> $this->user->lang['Lang_extend_pack_explain'],
					)
				);

				// get all language values
				@reset($this->countries);
				
				$values = $this->entries['value'];
				$statuses = $this->entries['status'];
			
				while (list($country_dir, $country_name) = each($this->countries))
				{
					$country_name = !empty($country_name) ? $country_name : 'english';
					$country_dir = !empty($country_dir) ? $country_dir : 'en';
					
					$value = isset($values[$key_main][$key_sub][$country_dir]) ? $values[$key_main][$key_sub][$country_dir] : '';
					$status = isset($statuses[$key_main][$key_sub][$country_dir]) ? $statuses[$key_main][$key_sub][$country_dir] : 0;
					
					$l_status = '';
					switch ($status)
					{
						case 1:
							$l_status = $this->language->lang('Lang_extend_modified');
						break;
						case 2:
							$l_status = $this->language->lang('Lang_extend_added');
						break;
						default:
							$l_status = $this->language->lang('Lang_extend_added');
						break;
					}
					
					$this->template->assign_block_vars('row', array(
						'L_COUNTRY'		=> $country_name,
						'COUNTRY'			=> $country_dir,
						'VALUE'				=> is_array($value) ? htmlspecialchars(print_r($value, true)) :  htmlspecialchars($value),
						'L_STATUS'			=> $l_status,
						)
					);
				}
				
				// footer
				$s_hidden_fields = '';
				$s_hidden_fields .= '<input type="hidden" name="action" value="' . $action . '" />';
				$s_hidden_fields .= '<input type="hidden" name="pack_file" value="' . urlencode($pack_file) . '" />';
				$s_hidden_fields .= '<input type="hidden" name="key_main" value="' . urlencode($key_main) . '" />';
				$s_hidden_fields .= '<input type="hidden" name="key_sub" value="' . urlencode($key_sub) . '" />';
				$s_hidden_fields .= '<input type="hidden" name="level" value="' . urlencode($level) . '" />';
				
				$this->template->assign_vars(array(
					'S_ACTION'				=> $this->u_action,
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
					)
				);
			}
		}
		elseif ($action == 'pack')
		{			
			if ($cancel)
			{
				// back to the main list
				$action = '';
				$cancel = false;
			}
			else
			{
				$params = !empty($params) ? $params : "&i=-orynider-translateaddkey-acp-translateaddkey_module&mode=".$mode;
				$this->u_action = !empty($this->u_action) ? $this->u_action : '?sid=' . $this->user->session_id;
				
				$this->user->add_lang_ext($this->ext_name, array('common', 'info_acp_admin_extend_lang'));				
				$lang = $this->user->lang;
				
				// template
				$tpl_name = 'lang_user_created_pack_body';
				$this->template->set_filenames(array('body' => $tpl_name));				
				// Create a form key for preventing CSRF attacks
				add_form_key($tpl_name);
				
				// header
				$this->template->assign_vars(array(
					'L_TITLE'				=> $this->user->lang['Lang_extend'],
					'L_TITLE_EXPLAIN'	=> $this->user->lang['Lang_extend_explain'],
					'LEVEL'					=> ($level == 'admin') ? $this->user->lang['Lang_extend_level_admin'] : $this->user->lang['Lang_extend_level_normal'],
					
					'L_PACK'				=> $this->user->lang['Lang_extend_pack'],
					'U_PACK'				=> append_sid($this->u_action),
					
					/* MG Lang DB - BEGIN */
					//'PACK'				=> $this->get_lang('Lang_extend_' . $this->packs[$pack_file]),
					'PACK'					=> !empty($this->packs[$pack_file]) ? $this->packs[$pack_file] : $pack_file,
					/* MG Lang DB - END */
					
					'L_EDIT'				=> $this->user->lang['Lang_extend_level_edit'],
					'L_LEVEL_NEXT'		=> ($level == 'admin') ? $this->user->lang['Lang_extend_level_normal'] : $this->user->lang['Lang_extend_level_admin'],
					'U_LEVEL_NEXT'	=> append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=' . (($level == 'admin') ? 'normal' : 'admin')),

					'L_KEYS'				=> $this->user->lang['Lang_extend_entries'],
					'L_NONE'				=> $this->user->lang['None'],
					'L_ADD'				=> $this->user->lang['Lang_extend_add_entry'],
					'L_CANCEL'			=> $this->user->lang['CANCEL'],
					)
				);
			
				// search form
				$this->template->assign_vars(array(
					'L_SEARCH'								=> $this->user->lang['Lang_extend_search'],
					'L_SEARCH_WORDS'					=> $this->user->lang['Lang_extend_search_words'],
					'L_SEARCH_WORDS_EXPLAIN'	=> $this->user->lang['Lang_extend_search_words_explain'],
					'L_SEARCH_ALL'						=> $this->user->lang['Lang_extend_search_all'],
					'L_SEARCH_ONE'						=> $this->user->lang['Lang_extend_search_one'],
					'L_SEARCH_IN'							=> $this->user->lang['Lang_extend_search_in'],
					'L_SEARCH_IN_EXPLAIN'			=> $this->user->lang['Lang_extend_search_in_explain'],
					'L_SEARCH_IN_KEY'					=> $this->user->lang['Lang_extend_search_in_key'],
					'L_SEARCH_IN_VALUE'				=> $this->user->lang['Lang_extend_search_in_value'],
					'L_SEARCH_IN_BOTH'				=> $this->user->lang['Lang_extend_search_in_both'],
					'L_EDIT'									=> $this->user->lang['Lang_extend_level_edit'],
					'L_SEARCH_LEVEL_ADMIN'		=> $this->user->lang['Lang_extend_level_admin'],
					'L_SEARCH_LEVEL_NORMAL'		=> $this->user->lang['Lang_extend_level_normal'],
					'L_SEARCH_LEVEL_BOTH'			=> $this->user->lang['Lang_extend_search_in_both'],
					)
				);
				
				// dump
				$color = false;
				
				$i = 0;
				
				@reset($this->entries['pack']);
				
				while (list($key_main, $data) = @each($this->entries['pack']))
				{		
					@reset($data);
					while (list($key_sub, $pack) = @each($data))
					{
						if ( ($key_main == 'mx_meta') || ($key_main == 'meta'))
						{
							$lang[$key_main]['langcode'] = isset($this->user->lang['USER_LANG']) ? $this->user->lang['USER_LANG'] : $country_dir;
						}
						elseif ($key_main == 'DEMO_VAR') 
						{
							$lang[$key_main]= isset($this->user->lang['DEMO_VAR']) ? $this->user->lang['DEMO_VAR'] : 'Demo Variable';
						}
						
						if (($pack == $pack_file) && (($this->entries['admin'][$key_main][$key_sub] && ($level == 'admin')) || (!$this->entries['admin'][$key_main][$key_sub] && ($level == 'normal'))))
						{
							$value = trim((!isset($this->user->lang[$key_main][$key_sub]) ? $this->language->lang($key_main) : $this->user->lang[$key_main][$key_sub]));
							if (strlen($value) > $value_maxlength)
							{
								$value = substr($value, 0, $value_maxlength-3) . '...';
							}
							$value = htmlspecialchars($value);

							// get the status
							$modified_added = false;
							if ($pack != 'custom')
							{
								$found = false;
								@reset($this->entries['status'][$key_main][$key_sub]);
								while (list($country_dir, $status) = @each($this->entries['status'][$key_main][$key_sub]))
								{
									$found = ($status > 0);
									if ($found)
									{
										$modified_added = true;
										break;
									}
								}
							}

							$i++;
							$color = !$color;
							$this->template->assign_block_vars('row', array(
								'CLASS'			=> $color ? 'row1' : 'row2',
								'KEY_MAIN'	=> "['" . $key_main . "']",
								'KEY_SUB'		=> empty($key_sub) ? '' : "['" . $key_sub . "']",
								'U_KEY'			=> append_sid($this->u_action . '&amp;action=key&amp;pack=' . urlencode($pack_file) . '&amp;level=' . $level . '&amp;key=' . urlencode($key_main) . '&amp;sub=' . urlencode($key_sub)),
								'VALUE'		=> $value,
								'STATUS'		=> $modified_added ? $this->user->lang['Lang_extend_added_modified'] : '',
								)
							);
						}
					}
				}
				
				if ($i == 0)
				{
					$pack_file = !empty($pack_file) ? $pack_file : './../language/en/common.php';
					/* MG Lang DB - BEGIN */
					$u_normal = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=normal');
					$u_admin = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=admin');
					/* MG Lang DB - END */
					
					$this->template->assign_block_vars('norow', array(
						'COLOR'					=> $color ? 'row1' : 'row2',
						/* MG Lang DB - BEGIN */
						//'PACK'					=> $this->get_lang('Lang_extend_' . $pack_name),
						'PACK'						=> !empty($pack_name) ? $pack_name : 'common',
						/* MG Lang DB - END */
						'L_EDIT'					=> $this->user->lang['EDIT'],
						'L_PACK_ADMIN'		=> $l_admin,
						'U_PACK_ADMIN'		=> $u_admin,
						'L_PACK_NORMAL'	=> $l_normal,
						'U_PACK_NORMAL'	=> $u_normal,
						)
					);
				}
				
				// footer
				$s_hidden_fields = '';
				$s_hidden_fields .= '<input type="hidden" name="action" value="' . $action . '" />';
				$s_hidden_fields .= '<input type="hidden" name="pack_file" value="' . urlencode($pack_file) . '" />';
				$s_hidden_fields .= '<input type="hidden" name="level" value="' . urlencode($level) . '" />';
				
				$this->template->assign_vars(array(
					'S_ACTION'				=> $this->u_action,
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
					)
				);
			}
		}
		elseif ($action == 'search')
		{
			if ($cancel)
			{
				$cancel = '';
				$action = '';
			}
			else
			{
				// search
				$search_words = $this->request->variable('search_words', '', false, \phpbb\request\request_interface::REQUEST);
				$search_words = !empty ($search_words) ? str_replace("\'", "'", urldecode($search_words)) : '';
				$search_logic = (int) $this->request->variable('search_logic', 0, false, \phpbb\request\request_interface::REQUEST);
				$search_in = (int) $this->request->variable('search_in', 2, false, \phpbb\request\request_interface::REQUEST);
				$search_country = $this->request->variable('search_country', 'en', false, \phpbb\request\request_interface::REQUEST);
				$search_language = $this->request->variable('search_language', $this->config['default_lang'], false, \phpbb\request\request_interface::REQUEST);
				$search_language = str_replace("\'", "'", urldecode($search_language));
				$search_admin = (int) $this->request->variable('search_words', 2, false, \phpbb\request\request_interface::REQUEST);
			
				// results
				$results = array();
				
				// get all the words to search
				if (empty($search_words))
				{
					$main_url = append_sid($this->u_action);
					msg_handler(E_USER_NOTICE, sprintf($this->language->lang('Lang_extend_search_no_words'), '<a href="' . $main_url . '">', '</a>'), __FILE__, __LINE__);
					//trigger_error($this->language->lang('Lang_extend_search_no_words', '<a href="' . $main_url . '">') . adm_back_link($this->u_action));
					exit;
				}
				
				$w_words = explode(' ', strtolower(str_replace('_', ' ', str_replace("\'", "'", str_replace("''", "'", $search_words)))));
				
				for ($i = 0; $i < sizeof($w_words); $i++)
				{
					if (!empty($w_words[$i]))
					{
						$words[] = $w_words[$i];
					}
				}

				// check each entry
				@reset($this->entries['pack']);
				while (list($key_main, $subs) = @each($this->entries['pack']))
				{
					@reset($subs);
					while (list($key_sub, $pack_dir) = @each($subs))
					{
						$admin = $this->entries['admin'][$key_main][$key_sub];
						if (($admin && ($search_admin != 1)) || (!$admin && ($search_admin != 0)))
						{
							$w_key = strtolower(str_replace('_', ' ', str_replace("\'", "'", str_replace("''", "'", $key_main))));
							$w_key .= ' ' . strtolower(str_replace('_', ' ', str_replace("\'", "'", str_replace("''", "'", $key_sub))));
							$w_words = explode(' ', $w_key);

							$words_key = array();
							for ($i = 0; $i < sizeof($w_words); $i++)
							{
								if (!empty($w_words[$i]))
								{
									$words_key[] = $w_words[$i];
								}
							}

							$words_val = array();
							@reset($this->countries);
							while (list($country, $country_name) = @each($this->countries))
							{
								if (empty($search_country) || ($country == $search_country))
								{
									$w_words_val = explode(' ', strtolower(str_replace("\'", "'", str_replace("''", "'", $this->entries['value'][$key_main][$key_sub][$this->country]))));
									for ($i = 0; $i < sizeof($w_words_val); $i++)
									{
										if (!empty($w_words_val[$i]))
										{
											if (empty($words_val) || !in_array($w_words_val[$i], $words_val))
											{
												$words_val[] = $w_words_val[$i];
											}
										}
									}
								}
							}

							// is this key convenient ?
							$ok = ($search_logic == 0);
							for ($i = 0; $i < sizeof($words); $i++)
							{
								$found = ((($search_in != 1) && in_array($words[$i], $words_key)) || (($search_in != 0) && in_array($words[$i], $words_val)));
								if (($search_logic == 1) && $found)
								{
									$ok = true;
									break;
								}
								if (($search_logic == 0) && !$found)
								{
									$ok = false;
									break;
								}
							}
							if ($ok)
							{
								$results[] = array('main' => $key_main, 'sub' => $key_sub);
							}
						}
					}
				}

				$params = !empty($params) ? $params : "&i=-orynider-translateaddkey-acp-translateaddkey_module&mode=".$mode;
				$this->u_action = !empty($this->u_action) ? $this->u_action : '?sid=' . $this->user->session_id;
				
				$this->user->add_lang_ext($this->ext_name, array('common', 'info_acp_admin_extend_lang'));				
				$lang = $this->user->lang;
				
				// template
				$tpl_name = 'lang_user_created_search_body';
				$this->template->set_filenames(array('body' => $tpl_name));				
				// Create a form key for preventing CSRF attacks
				add_form_key($tpl_name);
				
				// search form
				$this->template->assign_vars(array(
					'L_SEARCH'								=> $this->user->lang['Lang_extend_search'],
					'L_SEARCH_WORDS'					=> $this->user->lang['Lang_extend_search_words'],
					'L_SEARCH_WORDS_EXPLAIN'	=> $this->user->lang['Lang_extend_search_words_explain'],
					'L_SEARCH_ALL'						=> $this->user->lang['Lang_extend_search_all'],
					'L_SEARCH_ONE'						=> $this->user->lang['Lang_extend_search_one'],
					'L_SEARCH_IN'							=> $this->user->lang['Lang_extend_search_in'],
					'L_SEARCH_IN_EXPLAIN'			=> $this->user->lang['Lang_extend_search_in_explain'],
					'L_SEARCH_IN_KEY'					=> $this->user->lang['Lang_extend_search_in_key'],
					'L_SEARCH_IN_VALUE'				=> $this->user->lang['Lang_extend_search_in_value'],
					'L_SEARCH_IN_BOTH'				=> $this->user->lang['Lang_extend_search_in_both'],
					'L_EDIT'									=> $this->user->lang['Lang_extend_level_edit'],
					'L_SEARCH_LEVEL_ADMIN'		=> $this->user->lang['Lang_extend_level_admin'],
					'L_SEARCH_LEVEL_NORMAL'		=> $this->user->lang['Lang_extend_level_normal'],
					'L_SEARCH_LEVEL_BOTH'			=> $this->user->lang['Lang_extend_search_in_both'],
					)
				);				
				
				// header
				$this->template->assign_vars(array(
					'L_TITLE'						=> $this->user->lang['Lang_extend'],
					'L_TITLE_EXPLAIN'			=> $this->user->lang['Lang_extend_explain'],
					'L_SEARCH_RESULTS'	=> $this->user->lang['Lang_extend_search_results'],
					'L_PACK'						=> $this->user->lang['Lang_extend_pack'],
					'L_KEY'							=> $this->user->lang['Lang_extend_entries'],
					'L_VALUE'						=> $this->user->lang['Lang_extend_value'],
					'L_LEVEL'						=> $this->user->lang['Lang_extend_level_leg'],
					'L_NONE'						=> $this->user->lang['None'],
					'L_CANCEL'					=> $this->user->lang['CANCEL'],
					)
				);

				$color = false;
				for ($i = 0; $i < sizeof($results); $i++)
				{
					// get data
					$key_main	= $results[$i]['main'];
					$key_sub		= $results[$i]['sub'];
					$pack_file		= $this->entries['pack'][$key_main][$key_sub];
					$pack_name	= $this->packs[$pack_file];
					$admin			= $this->entries['admin'][$key_main][$key_sub];

					// value
					$value = trim((empty($key_sub) ? $lang[$key_main] : $lang[$key_main][$key_sub]));
					if (strlen($value) > $value_maxlength)
					{
						$value = substr($value, 0, $value_maxlength - 3) . '...';
					}
					$value = htmlspecialchars($value);

					// status
					$modified_added = false;
					
					if ($pack_file != 'custom')
					{
						$found = false;						
						@reset($this->entries['status'][$key_main][$key_sub]);					
						while (list($country_dir, $status) = @each($this->entries['status'][$key_main][$key_sub]))
						{
							$found = ($status > 0);
							if ($found)
							{
								$modified_added = true;
								break;
							}
						}
					}

					$color = !$color;
					$this->template->assign_block_vars('row', array(
						'CLASS'			=> $color ? 'row1' : 'row2',
						/* MG Lang DB - BEGIN */
						//'PACK'		=> $this->get_lang('Lang_extend_' . $pack_name),
						'PACK'				=> !empty($pack_name) ? $pack_name : 'common',
						/* MG Lang DB - END */
						'KEY_MAIN'	=> "['" . $key_main . "']",
						'KEY_SUB'		=> empty($key_sub) ? '' : "['" . $key_sub . "']",
						'VALUE'		=> $value,
						'L_EDIT'		=> $this->user->lang['Lang_extend_level_edit'],
						'LEVEL'			=> $admin ? $this->user->lang['Lang_extend_level_admin'] : $this->user->lang['Lang_extend_level_normal'],
						'STATUS'		=> $modified_added ? $this->user->lang['Lang_extend_added_modified'] : '',

						'U_PACK'		=> append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=' . ($admin ? 'admin' : 'normal')),
						'U_KEY'			=> append_sid($this->u_action . '&amp;action=key&amp;pack=' . urlencode($pack_file) . '&amp;level=' . ($admin ? 'admin' : 'normal') . '&amp;key=' . urlencode($key_main). '&amp;sub=' . urlencode($key_sub)),
						)
					);
				}

				if (sizeof($results) == 0)
				{
					$pack_file = !empty($pack_file) ? $pack_file : './../language/en/common.php';
					/* MG Lang DB - BEGIN */
					$u_normal = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=normal');
					$u_admin = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=admin');
					/* MG Lang DB - END */
					
					$this->template->assign_block_vars('norow', array(
						'COLOR'					=> $color ? 'row1' : 'row2',
						/* MG Lang DB - BEGIN */
						//'PACK'					=> $this->get_lang('Lang_extend_' . $pack_name),
						'PACK'						=> !empty($pack_name) ? $pack_name : 'common',
						/* MG Lang DB - END */
						'L_EDIT'					=> $this->user->lang['EDIT'],
						'L_PACK_ADMIN'		=> $l_admin,
						'U_PACK_ADMIN'		=> $u_admin,
						'L_PACK_NORMAL'	=> $l_normal,
						'U_PACK_NORMAL'	=> $u_normal,
						)
					);
				}

				// footer
				$s_hidden_fields = '';
				$s_hidden_fields .= '<input type="hidden" name="action" value="' . $action . '" />';
				$s_hidden_fields .= '<input type="hidden" name="search_words" value="' . urlencode(str_replace("'", "\'", $search_words)) . '" />';
				$s_hidden_fields .= '<input type="hidden" name="search_logic" value="' . $search_logic . '" />';
				$s_hidden_fields .= '<input type="hidden" name="search_in" value="' . $search_in . '" />';
				$s_hidden_fields .= '<input type="hidden" name="search_language" value="' . urlencode($search_language) . '" />';
				$s_hidden_fields .= '<input type="hidden" name="search_admin" value="' . $search_admin . '" />';

				$this->template->assign_vars(array(
					'S_ACTION'				=> $this->u_action,
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
					)
				);
			}
		}
		else
		{
			// search
			$search_words = $this->request->variable('search_words', '', false, \phpbb\request\request_interface::REQUEST);
			$search_words = !empty ($search_words) ? str_replace("\'", "'", urldecode($search_words)) : '';
			$search_logic = (int) $this->request->variable('search_logic', 0, false, \phpbb\request\request_interface::REQUEST);
			$search_in = (int) $this->request->variable('search_in', 2, false, \phpbb\request\request_interface::REQUEST);
			$search_language = (string) $this->request->variable('search_language', $this->config['default_lang'], false, \phpbb\request\request_interface::REQUEST);
			$search_language = $search_country = str_replace("\'", "'", urldecode($search_language));
			$search_admin = (int) $this->request->variable('search_admin', 2, false, \phpbb\request\request_interface::REQUEST);
			
			$params = !empty($params) ? $params : "&i=-orynider-translateaddkey-acp-translateaddkey_module&mode=pack";
			$this->u_action = !empty($this->u_action) ? $this->u_action : '?sid=' . $this->user->session_id;
			
			$this->user->add_lang_ext($this->ext_name, array('common', 'info_acp_admin_extend_lang'));				
			$lang = $this->user->lang;
			
			// template
			$tpl_name = 'lang_user_created_body';
			$this->template->set_filenames(array('body' => $tpl_name));				
			// Create a form key for preventing CSRF attacks
			add_form_key($tpl_name);
				
			// header
			$this->template->assign_vars(array(
				'L_TITLE'						=> $this->user->lang['Lang_extend'],
				'L_TITLE_EXPLAIN'			=> $this->user->lang['Lang_extend_explain'],
				'L_PACK'						=> $this->user->lang['Lang_extend_pack'],
				'L_EDIT'						=> $this->user->lang['Lang_extend_level_edit'],
				'L_ADMIN'					=> $this->user->lang['Lang_extend_level_admin'],
				'L_NORMAL'					=> $this->user->lang['Lang_extend_level_normal'],

				'L_NONE'						=> $this->user->lang['None'],
				'L_SUBMIT'					=> $this->user->lang['SUBMIT'],
				)
			);

			// display packs
			$i = 0;
			$color = false;
			@reset($this->packs);
			
			while (list($pack_file, $pack_name) = @each($this->packs))
			{
				$i++;
				$color = !$color;
				
				/* MG Lang DB - BEGIN */
				$u_normal = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=normal');
				$u_admin = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=admin');
				/* MG Lang DB - END */
				
				$this->template->assign_block_vars('row', array(
					'COLOR'					=> $color ? 'row1' : 'row2',
					/* MG Lang DB - BEGIN */
					//'PACK'					=> $this->get_lang('Lang_extend_' . $pack_name),
					'PACK'						=> !empty($pack_name) ? $pack_name : 'common',
					/* MG Lang DB - END */
					'L_EDIT'					=> $this->user->lang['EDIT'],
					'L_PACK_ADMIN'		=> $l_admin,
					'U_PACK_ADMIN'		=> $u_admin,
					'L_PACK_NORMAL'	=> $l_normal,
					'U_PACK_NORMAL'	=> $u_normal,
					)
				);
			}
			
			if ($i == 0)
			{
				$pack_file = !empty($pack_file) ? $pack_file : './../language/en/common.php';
				/* MG Lang DB - BEGIN */
				$u_normal = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=normal');
				$u_admin = append_sid($this->u_action . '&amp;action=pack&amp;pack=' . urlencode($pack_file) . '&amp;level=admin');
				/* MG Lang DB - END */
				
				$this->template->assign_block_vars('norow', array(
					'COLOR'					=> $color ? 'row1' : 'row2',
					/* MG Lang DB - BEGIN */
					//'PACK'					=> $this->get_lang('Lang_extend_' . $pack_name),
					'PACK'						=> !empty($pack_name) ? $pack_name : 'common',
					/* MG Lang DB - END */
					'L_EDIT'					=> $this->user->lang['EDIT'],
					'L_PACK_ADMIN'		=> $l_admin,
					'U_PACK_ADMIN'		=> $u_admin,
					'L_PACK_NORMAL'	=> $l_normal,
					'U_PACK_NORMAL'	=> $u_normal,
					)
				);
			}

			// search form
			$this->template->assign_vars(array(
				'L_SEARCH'								=> $this->user->lang['Lang_extend_search'],
				'L_SEARCH_WORDS'					=> $this->user->lang['Lang_extend_search_words'],
				'L_SEARCH_WORDS_EXPLAIN'	=> $this->user->lang['Lang_extend_search_words_explain'],
				'L_SEARCH_ALL'						=> $this->user->lang['Lang_extend_search_all'],
				'L_SEARCH_ONE'						=> $this->user->lang['Lang_extend_search_one'],
				'L_SEARCH_IN'							=> $this->user->lang['Lang_extend_search_in'],
				'L_SEARCH_IN_EXPLAIN'			=> $this->user->lang['Lang_extend_search_in_explain'],
				'L_SEARCH_IN_KEY'					=> $this->user->lang['Lang_extend_search_in_key'],
				'L_SEARCH_IN_VALUE'				=> $this->user->lang['Lang_extend_search_in_value'],
				'L_SEARCH_IN_BOTH'				=> $this->user->lang['Lang_extend_search_in_both'],
				'L_EDIT'									=> $this->user->lang['Lang_extend_level_edit'],
				'L_SEARCH_LEVEL_ADMIN'		=> $this->user->lang['Lang_extend_level_admin'],
				'L_SEARCH_LEVEL_NORMAL'		=> $this->user->lang['Lang_extend_level_normal'],
				'L_SEARCH_LEVEL_BOTH'			=> $this->user->lang['Lang_extend_search_in_both'],
				)
			);

			// list of lang installed
			$selected = empty($search_country) ? ' selected="selected"' : '';
			$s_languages = '<option value=""' . $selected . '>' . $this->user->lang['Lang_extend_search_all_lang'] . '</option>';
			
			@reset($this->countries);
			
			while (list($country_dir, $country_name) = @each($this->countries))
			{
				$selected = ($country_dir == $search_country) ? ' selected="selected"' : '';
				$s_languages .= '<option value="' . $country_dir . '"' . $selected . '>' . $country_name . '</option>';
			}
			$s_languages = sprintf('<select name="search_language">%s</select>', $s_languages);

			$this->template->assign_vars(array(
				'SEARCH_WORDS'					=> $search_words,
				'SEARCH_ALL'						=> ($search_logic == 0) ? 'checked="checked"' : '',
				'SEARCH_ONE'						=> ($search_logic == 1) ? 'checked="checked"' : '',
				'SEARCH_IN_KEY'					=> ($search_in == 0) ? 'checked="checked"' : '',
				'SEARCH_IN_VALUE'				=> ($search_in == 1) ? 'checked="checked"' : '',
				'SEARCH_IN_BOTH'				=> ($search_in == 2) ? 'checked="checked"' : '',
				'SEARCH_LEVEL_ADMIN'		=> ($search_in == 0) ? 'checked="checked"' : '',
				'SEARCH_LEVEL_NORMAL'	=> ($search_in == 1) ? 'checked="checked"' : '',
				'SEARCH_LEVEL_BOTH'			=> ($search_in == 2) ? 'checked="checked"' : '',
				'S_LANGUAGES'					=> $s_languages,
				)
			);

			// footer
			$s_hidden_fields = '';
			$this->template->assign_vars(array(
				'S_ACTION'				=> $this->u_action,
				'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
				)
			);
		}
	}	



}
