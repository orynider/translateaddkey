<?php
/**
 *
 * translateaddkey extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace orynider\translateaddkey\acp;

/**
 * Class to handle allowing or disallowing translateaddkey services
 */
class translateaddkey_helper
{
	/** @var \orynider\cache\driver\driver_interface $cache */
	protected $cache;

	/** @var \orynider\config\config $config */
	protected $config;

	/** @var \orynider\file_downloader $file_downloader */
	protected $file_downloader;

	/** @var \orynider\language\language $language */
	protected $language;

	/** @var \orynider\log\log $log */
	protected $log;

	/** @var \orynider\user $user */
	protected $user;

	/** @var bool Use SSL or not */
	protected $use_ssl = false;

	/**
	 * Constructor
	 *
	 * @param \orynider\cache\driver\driver_interface $cache
	 * @param \orynider\config\config                 $config
	 * @param \orynider\file_downloader               $file_downloader
	 * @param \orynider\language\language             $language
	 * @param \orynider\log\log                       $log
	 * @param \orynider\user                          $user
	 */
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \phpbb\config\config $config, \phpbb\file_downloader $file_downloader, \phpbb\language\language $language, \phpbb\log\log $log, \phpbb\user $user)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->file_downloader = $file_downloader;
		$this->language = $language;
		$this->log = $log;
		$this->user = $user;
	}

	/**
	 * Obtains the latest translateaddkey services information from phpBB
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @param bool $force_cache  Force the use of the cache. Override $force_update.
	 *
	 * @throws \RuntimeException
	 *
	 * @return void
	 */
	public function set_translateaddkey_services($force_update = false, $force_cache = false)
	{
		$cache_key = '_versioncheck_translateaddkey_' . $this->use_ssl;

		$info = $this->cache->get($cache_key);

		if ($info === false && $force_cache)
		{
			throw new \RuntimeException($this->language->lang('VERSIONCHECK_FAIL'));
		}
		else if ($info === false || $force_update)
		{
			try
			{
				$info = $this->file_downloader->get('www.phpbb.com', '/translateaddkey', 'enabled', 443);
			}
			catch (\orynider\exception\runtime_exception $exception)
			{
				$prepare_parameters = array_merge(array($exception->getMessage()), $exception->get_parameters());
				throw new \RuntimeException(call_user_func_array(array($this->language, 'lang'), $prepare_parameters));
			}

			if ($info === '0')
			{
				$this->set_translateaddkey_configs(array(
					'allow_translateaddkey_phpbb'	=> false,
				));
			}
			else
			{
				$info = '1';
				$this->set_translateaddkey_configs(array(
					'allow_translateaddkey_phpbb'	=> true,
				));
			}

			$this->cache->put($cache_key, $info, 86400); // 24 hours
		}
	}

	/**
	 * Sets translateaddkey service configs as determined by phpBB
	 *
	 * @param array $data Array of translateaddkey file data.
	 *
	 * @return void
	 */
	protected function set_translateaddkey_configs($data)
	{
		$translateaddkey_configs = array(
			'allow_translateaddkey_phpbb',
			'phpbb_translateaddkey_api_key',
		);

		foreach ($translateaddkey_configs as $cfg_name)
		{
			if (array_key_exists($cfg_name, $data) && ($data[$cfg_name] != $this->config[$cfg_name] || !isset($this->config[$cfg_name])))
			{
				$this->config->set($cfg_name, $data[$cfg_name]);
			}
		}

		$this->config->set('translateaddkey_last_gc', time(), false);
	}

	/**
	 * Log a translateaddkey error message to the error log
	 *
	 * @param string $message The error message
	 */
	public function log_translateaddkey_error($message)
	{
		$user_id = empty($this->user->data) ? ANONYMOUS : $this->user->data['user_id'];
		$user_ip = empty($this->user->ip) ? '' : $this->user->ip;

		$this->log->add('critical', $user_id, $user_ip, 'LOG_MX_translateaddkey_CHECK_FAIL', false, array($message));
	}
}
