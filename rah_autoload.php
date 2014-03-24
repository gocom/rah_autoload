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

class Rah_Autoload
{
    /**
     * Autoloader file.
     *
     * @var string
     */

    protected $autoload = 'autoload.php';

    /**
     * Constructor.
     */

    public function __construct()
    {
        add_privs('prefs.rah_autoload', '1');
        register_callback(array($this, 'install'), 'plugin_lifecycle.rah_autoload', 'installed');
        register_callback(array($this, 'uninstall'), 'plugin_lifecycle.rah_autoload', 'deleted');

        if (($path = get_pref('rah_autoload_path')) !== '') {
            foreach (do_list($path) as $file) {
                include_once txpath . '/' . $file;
            }
        }

        if (get_pref('rah_autoload_search')) {
            if (($path = $this->find()) !== false) {
                include_once $path;
            }
        }
    }

    /**
     * Installer.
     */

    public function install()
    {
        $position = 250;

        foreach (array(
            'rah_autoload_path'   => array('text_input', ''),
            'rah_autoload_search' => array('yesnoradio', 1),
        ) as $name => $val) {
            if (get_pref($name, false) === false) {
                set_pref($name, $val[1], 'rah_autoload', PREF_ADVANCED, $val[0], $position);
            }

            $position++;
        }
    }

    /**
     * Uninstaller.
     */

    public function uninstall()
    {
        safe_delete('txp_prefs', "name like 'rah\_autoload\_%'");
    }

    /**
     * Finds the autoload file Composer generated.
     *
     * @return string|bool The path, or FALSE on failure
     */

    protected function find()
    {
        // Try the suggested default.

        if (($path = $this->isFile(dirname(txpath) . '/vendor/' . $this->autoload)) !== false) {
            return $path;
        }

        // If not there, try to find a composer.json from a parent.

        $directory = txpath;

        while (1) {
            $directory = dirname($directory);

            if (!$directory || $directory === '.' || $directory === '/' || $directory === '\\' || !is_dir($directory) || !is_readable($directory)) {
                return false;
            }

            if (($composer = $this->isFile($directory . '/composer.json')) !== false) {
                break;
            }
        }

        // Check the default location.

        if (($path = $this->isFile($directory . '/vendor/' . $this->autoload)) !== false) {
            return $path;
        }

        // If not the default, parse the path from the composer.json.

        if ($composer = file_get_contents($composer)) {
            if (($json = @json_decode($composer)) && isset($json->config->{'vendor-dir'})) {
                return $this->isFile($directory . '/' . $json->config->{'vendor-dir'} . '/' . $this->autoload);
            }
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
        if (file_exists($file) && is_file($file) && is_readable($file)) {
            return $file;
        }

        return false;
    }
}

new Rah_Autoload();
