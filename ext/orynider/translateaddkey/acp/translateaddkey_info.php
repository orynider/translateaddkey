<?php
/**
*
* @package phpBB Extension - Google Translator
* @copyright (c) 2018 orynider
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace orynider\translateaddkey\acp;

class translateaddkey_info
{
	function module()
	{
		return array(
		'filename'	=> '\orynider\translateaddkey\acp\translateaddkey_module',
		'title'	=> 'ACP_TRANSLATEADDKEY',
			'modes'	=> array(
				'config'	=> array(
					'title'	=> 'ACP_TRANSLATE_CONFIG', 
					'auth'	=> 'ext_orynider/translateaddkey && acl_a_board', 
					'cat'	=> array('ACP_TRANSLATEADDKEY')
				),
				'phpbb_add'	=> array(
					'title'	=> 'ACP_ADD_PHPBB_KEY',
					'auth'	=> 'ext_orynider/translateaddkey && acl_a_board',
					'cat'	=> array('ACP_TRANSLATEADDKEY')
				),				
				'phpbb_ext_add'	=> array(
					'title'	=> 'ACP_ADD_PHPBB_EXT_KEY',
					'auth'	=> 'ext_orynider/translateaddkey && acl_a_board',
					'cat'	=> array('ACP_TRANSLATEADDKEY')
				),	
				'phpbb'	=> array(
					'title'	=> 'ACP_TRANSLATE_PHPBB_LANG',
					'auth'	=> 'ext_orynider/translateaddkey && acl_a_board',
					'cat'	=> array('ACP_TRANSLATEADDKEY')
				),				
				'phpbb_ext'	=> array(
					'title'	=> 'ACP_TRANSLATE_PHPBB_EXT',
					'auth'	=> 'ext_orynider/translateaddkey && acl_a_board',
					'cat'	=> array('ACP_TRANSLATEADDKEY')
				),				
			),
		);
	}
}
