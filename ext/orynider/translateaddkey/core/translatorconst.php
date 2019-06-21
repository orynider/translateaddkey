<?php
/**
 *
 * Language Tools Extension for the phpBB Forum Software package
 * @author culprit_cz, orynider
* @copyright (c) orynider <http://mxpcms.sourceforge.net>
* @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
namespace orynider\translateaddkey\core;
/** -------------------------------------------------------------------------
* Extend User Style with module lang and images
* Usage:  $this->extend(LANG, IMAGES, '_core', 'img_file_in_dir', 'img_file_ext')
* Switches:
* - LANG: MX_LANG_MAIN (default), MX_LANG_ADMIN, MX_LANG_ALL, MX_LANG_NONE
* - IMAGES: MX_IMAGES (default), MX_IMAGES_NONE
** ------------------------------------------------------------------------- */
class translatorconst
{	
	const MX_LANG_MAIN = '10';
	const SHARED2_LANG_MAIN = '12';
	const SHARED3_LANG_MAIN = '13';
	const MX_LANG_ADMIN = '20';
	const SHARED2_LANG_ADMIN = '22';
	const SHARED3_LANG_ADMIN = '23';
	const MX_LANG_ALL = '30';
	const MX_LANG_NONE = '40';
	const MX_IMAGES = '50';
	const MX_IMAGES_NONE = '60';
	const MX_LANG_CUSTOM = '70';
	const SHARED2_LANG_CUSTOM = '72';
	const SHARED3_LANG_CUSTOM = '73';

	const MX_BUTTON_IMAGE = '10';
	const MX_BUTTON_TEXT =	'20';
	const MX_BUTTON_GENERIC = '30';	

	
}
