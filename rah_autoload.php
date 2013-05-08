<?php

/**
 * Rah_autoload plugin for Textpattern CMS.
 *
 * @author  Jukka Svahn
 * @license GNU GPLv2
 * @link    https://github.com/gocom/rah_autoload
 *
 * Copyright (C) 2013 Jukka Svahn http://rahforum.biz
 * Licensed under GNU General Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

class rah_autoload
{
	/**
	 * Constructor.
	 */

	public function __construct()
	{
		if (($path = $this->find()) !== false)
		{
			include_once $path;
		}
	}

	/**
	 * Finds the autoload file Composer generated.
	 *
	 * @return string|bool The path, or FALSE on failure
	 */

	protected function find()
	{
		// Try the suggested default.

		if (($path = $this->isFile(dirname(txpath) . '/vendor/autoload.php')) !== false)
		{
			return $path;
		}

		// If not there, try to find a composer.json from a parent.

		$directory = txpath;

		while (1)
		{
			$directory = dirname($directory);

			if (!$directory || $directory === '.' || $directory === '/' || $directory === '\\' || !is_dir($directory) || !is_readable($directory))
			{
				return false;
			}

			if (($composer = $this->isFile($directory . '/composer.json')) !== false)
			{
				break;
			}
		}

		// Check the default location.

		if (($path = $this->isFile($directory . '/vendor/autoload.php')) !== false)
		{
			return $path;
		}

		// If not the default, parse the path from the composer.json.

		if (($json = @json_decode($composer)) && isset($json->config->{'vendor-dir'}))
		{
			return $this->isFile($directory . '/' . $json->config->{'vendor-dir'} . '/autoload.php');
		}

		return false;
	}

	/**
	 * Whether it is a safe file.
	 *
	 * @param  string      $file
	 * @return string|bool The path, or FALSE on failure
	 */

	protected function isFile($file)
	{
		if (file_exists($file) && is_file($file) && is_readable($file))
		{
			return $file;
		}

		return false;
	}
}

new rah_autoload();