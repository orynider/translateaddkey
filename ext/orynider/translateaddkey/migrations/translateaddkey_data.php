<?php
/**
*
* @package phpBB Extension - Google Translator
* @copyright (c) 2018 orynider
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace orynider\translateaddkey\migrations;

class translateaddkey_data extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
		'\phpbb\db\migration\data\v31x\v314'
		);
	}

	public function update_data()
	{
		return array(
		// Add configs
			array('config.add', array('translateaddkey_default_lang', 'en')),
			array('config.add', array('translateaddkey_choice_lang', 'fr,es,ro,it')),
			array('config.add', array('translateaddkey_version', '0.9.0')),
			
			// Add module
			array('module.add', array(
				'acp', 
				'ACP_CAT_DOT_MODS', 
				'ACP_TRANSLATEADDKEY',
				array(
					'module_enabled'  => 1,
					'module_display'  => 1,
					'module_langname' => 'ACP_TRANSLATEADDKEY',
					'module_auth'     => 'ext_orynider/translateaddkey && acl_a_board',
				)				
			)),
			array('module.add', array(
				'acp', 
				'ACP_TRANSLATEADDKEY',
				array(
					'module_basename' => '\orynider\translateaddkey\acp\translateaddkey_module',
					'modes'           => array('config', 'phpbb_add', 'phpbb_ext_add', 'phpbb', 'phpbb_ext'),
				)							
			)),
		);
	}
}
