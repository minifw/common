<?php

/*
 * Copyright (C) 2023 Yang Ming <yangming0116@163.com>.
 *
 * This library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this library.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Minifw\Common;

class System
{
    private static $config;

    public static function init($configFile)
    {
        self::loadConfig($configFile);

        if (!defined('MFW_APP_ROOT')) {
            $app_root = self::getConfig('path', 'app_root', '');
            $app_root = rtrim(str_replace('\\', '/', $app_root), '/');
            if (empty($app_root)) {
                throw new Exception('app_root path not set');
            }
            define('MFW_APP_ROOT', $app_root);
        }

        if (!defined('MFW_DEBUG')) {
            define('MFW_DEBUG', self::getConfig('debug', 'enable', 0));
        }
        if (!defined('MFW_DBPREFIX')) {
            define('MFW_DBPREFIX', self::getConfig('main', 'dbprefix', ''));
        }

        if (!defined('MFW_DATA_DIR')) {
            $path = MFW_APP_ROOT . self::getConfig('path', 'data', '');
            define('MFW_DATA_DIR', $path);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
        }

        if (!defined('MFW_TMP_DIR')) {
            $path = MFW_APP_ROOT . self::getConfig('path', 'tmp', '');
            define('MFW_TMP_DIR', $path);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
        }

        date_default_timezone_set(self::getConfig('main', 'timezone', 'PRC'));
    }

    public static function loadConfig(string $configFile) : void
    {
        $cfg = [];
        require __DIR__ . '/defaults.php';
        if (file_exists($configFile)) {
            require $configFile;
        }
        self::$config = $cfg;
    }

    public static function getConfig(string $section, string $key = '', ?string $default = null) : string|array|null
    {
        if ($section === '' || !isset(self::$config[$section])) {
            return null;
        }
        if ($key === '') {
            return self::$config[$section];
        }
        if (!isset(self::$config[$section][$key])) {
            return $default;
        }

        return self::$config[$section][$key];
    }
}
