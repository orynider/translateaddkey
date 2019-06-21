<?php
/**
 *
 * Language Tools Extension for the phpBB Forum Software package
 *
* @copyright (c) orynider <http://mxpcms.sourceforge.net>
* @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace orynider\translateaddkey\acp;

/**
* mx_langtools ACP module
 */
$basename = basename( __FILE__);
define('MODULE_URL', generate_board_url() . 'ext/orynider/translateaddkey/');		
$no_page_header = 'no_page_header';

/**
* Class  translateaddkey_module extends translateaddkey
* Displays a message to the user and allows him to send an email
*/
class translateaddkey_module
{
	var	$tpl_name;
	var	$page_title;
	var	$request;
	var	$config;
	var	$lang;
	var	$log;
	var	$template;
	var	$user;
	
	var $u_action;
	var $parent_id = 0;	
	/**#@+
	 * mx_user class specific vars
	 *
	 */
	var $template_path = 'templates/';
	var $theme = array(); 
	var $template_name = '';
	var $template_names = array();
	
	var $default_template_name = 'all';
	
	var $default_current_template_path = '';
	var $default_current_style_path = '';
	
	var $user_current_template_path = '';	
	var $user_current_style_path = '';

	var $style_name = '';	
	var $style_path = 'styles/';	
	
	var $default_style_name = 'prosilver';
	var $default_style2_name = 'subsilver2';	
	
	var $default_module_style = '';
	var $module_lang_path = 'ext/orynider/translateaddkey/language/';

	var $is_admin = false;
	var $keyoptions = false;
	
	function main($id, $mode = 'generate')
	{
		global $user, $template, $request;
		global $config, $phpbb_container;
		
		/** @var \phpbb\language\language $lang */
		$this->lang = $phpbb_container->get('language');
		/** @var \phpbb\request\request $request */
		$this->request = $phpbb_container->get('request');
		/** @var \phpbb\log\log $log */		
		$this->log = $phpbb_container->get('log');

		// Requests
		$action = $request->variable('action', '');
		$page_id = $request->variable('page_id', 0);
		$currency_id = $request->variable('currency_id', 0);		
		
		/* general vars */
		$mode = $request->variable('mode', $mode);
		$start = $request->variable('start', 0);  
		$s = $request->variable('mode', 'generate');	
		/* */	
		
		/* Get an instance of the admin controller */
		$translateaddkey = $phpbb_container->get('orynider.translateaddkey.admin.controller');

		/** Load the "settings" or "manage" module modes **/
		switch ($mode)
		{
			case 'config':
				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'acp_translateaddkey_config';
				// Set the page title for our ACP page
				$this->page_title = $this->lang->lang('ACP_TRANSLATOR');	
				// Make the $u_action url available in the admin controller direct with no ajax
				$translateaddkey->set_page_url($this->u_action);
				// Load the display options handle in the admin controller 
				$translateaddkey->display_settings($this->tpl_name, $this->page_title);				
			break;
			case 'phpbb':
				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'lang_translate';
				// Set the page title for our ACP page
				$this->page_title = $this->lang->lang('ACP_TRANSLATE_PHPBB_LANG');
				// Load the display options handle in the admin controller 
				$translateaddkey->display_translate($this->tpl_name, $this->page_title);
			break;			
			case 'phpbb_ext':
				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'lang_translate';
				// Set the page title for our ACP page
				$this->page_title = $this->lang->lang('ACP_TRANSLATE_PHPBB_EXT');
				// Load the display options handle in the admin controller 
				$translateaddkey->display_translate($this->tpl_name, $this->page_title);
			break;
			case 'phpbb_add':
				// Load a template from adm/style for our ACP page
				switch ($action)
				{	
					case 'key':
						$this->tpl_name = 'lang_user_created_key_body';
					break;						
					case 'pack':
						$this->tpl_name = 'lang_user_created_pack_body';
					break;	
					case 'search':
						$this->tpl_name = 'lang_user_created_search_body';
					break;
					default:
						$this->tpl_name = 'lang_user_created_body';
					break;
				}	
				// Set the page title for our ACP page
				$this->page_title = $this->lang->lang('ACP_TRANSLATE_PHPBB_LANG');
				// Make the $u_action url available in the admin controller direct with no ajax
				$translateaddkey->set_page_url($this->u_action);
				// Load the display options handle in the admin controller 
				$translateaddkey->manage_translateaddkey($this->tpl_name, $this->page_title);
			break;			
			case 'phpbb_ext_add':
				// Load a template from adm/style for our ACP page
				switch ($action)
				{	
					case 'key':
						$this->tpl_name = 'lang_user_created_key_body';
					break;						
					case 'pack':
						$this->tpl_name = 'lang_user_created_pack_body';
					break;	
					case 'search':
						$this->tpl_name = 'lang_user_created_search_body';
					break;
					default:
						$this->tpl_name = 'lang_user_created_body';
					break;
				}
				// Set the page title for our ACP page
				$this->page_title = $this->lang->lang('ACP_TRANSLATE_PHPBB_EXT');
				// Make the $u_action url available in the admin controller direct with no ajax
				$translateaddkey->set_page_url($this->u_action);
				// Load the display options handle in the admin controller 
				$translateaddkey->manage_translateaddkey($this->tpl_name, $this->page_title);
			break;					
		}			
	}
}
