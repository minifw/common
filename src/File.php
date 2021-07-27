<?php

/*
 * Copyright (C) 2021 Yang Ming <yangming0116@163.com>.
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

use Minifw\Common\Exception;
use Minifw\Common\FileUtils;

class File {

    public static $default_fs_encoding = '';
    protected $fs_encoding;
    protected $app_encoding = 'utf-8';
    protected $fs_path = '';
    protected $app_path = '';

    public function __construct($app_path, $fs_path = null, $fs_encoding = null) {
        if (empty($fs_encoding)) {
            $this->fs_encoding = self::$default_fs_encoding;
        }
        else {
            $this->fs_encoding = $fs_encoding;
        }

        if (!empty($app_path)) {
            $this->set_app_path($app_path);
        }
        elseif (!empty($fs_path)) {
            $this->set_fs_path($fs_path);
        }
        else {
            throw new Exception('必须指定路径');
        }
    }

    public function get_fs_path() {
        return $this->fs_path;
    }

    public function get_app_path() {
        return $this->app_path;
    }

    public function set_app_path($path) {
        $path = rtrim($path, '/\\');

        $this->app_path = $path;
        if (!empty($this->fs_encoding) && $this->fs_encoding != $this->app_encoding) {
            $this->fs_path = iconv($this->app_encoding, $this->fs_encoding, $this->app_path);
        }
        else {
            $this->fs_path = $this->app_path;
        }
    }

    public function set_fs_path($path) {
        $path = rtrim($path, '/\\');

        $this->fs_path = $path;
        if (!empty($this->fs_encoding) && $this->fs_encoding != $this->app_encoding) {
            $this->app_path = iconv($this->fs_encoding, $this->app_encoding, $this->fs_path);
        }
        else {
            $this->app_path = $this->fs_path;
        }
    }

    public function get_content() {
        if (file_exists($this->fs_path)) {
            return file_get_contents($this->fs_path);
        }
        return '';
    }

    public function call($function) {
        return call_user_func($function, $this->fs_path);
    }

    /**
     *
     * @return \Minifw\Common\File
     */
    public function get_parent() {
        $parent = FileUtils::dirname($this->app_path);
        return new File($parent, '', $this->fs_encoding);
    }

    public function is_dir_empty() {
        if (is_dir($this->fs_path)) {
            $dir = opendir($this->fs_path);
            while (false !== ($file = readdir($dir))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                else {
                    closedir($dir);
                    return false;
                }
            }
            closedir($dir);
            return true;
        }
        else {
            return false;
        }
    }

    public function ls($ext = '', $hidden = false) {
        if (is_dir($this->fs_path)) {
            $res = [];

            $full = $this->fs_path;
            if (substr($full, -1) !== '/') {
                $full .= '/';
            }

            if ($dh = opendir($full)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    if ($file[0] == '.' && !$hidden) {
                        continue;
                    }

                    if ($ext != '') {
                        if (is_file($full . '/' . $file) && substr($file, -1 * strlen($ext)) != $ext) {
                            continue;
                        }
                    }

                    if (!empty($this->fs_encoding) && $this->fs_encoding != $this->app_encoding) {
                        $file = iconv($this->fs_encoding, $this->app_encoding, $file);
                    }

                    $res[] = [
                        'name' => $file,
                        'dir' => is_dir($this->app_path . '/' . $file),
                    ];
                }
                closedir($dh);
            }

            usort($res, function ($left, $right) {
                if ($left['dir'] == $right['dir']) {
                    return strcmp($left['name'], $right['name']);
                }
                elseif ($left['dir']) {
                    return -1;
                }
                return 1;
            });

            return $res;
        }
        else {
            return false;
        }
    }

    public function get_mime() {
        if (file_exists($this->fs_path)) {
            $pinfo = pathinfo($this->fs_path);
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            $mime_type = 'application/octet-stream';
            if (isset(self::$mime_hash[$ext])) {
                $mime_type = self::$mime_hash[$ext];
            }
            else {
                $fi = new \finfo(FILEINFO_MIME_TYPE);
                $mime_type = $fi->file($this->fs_path);
            }
            return $mime_type;
        }
        return null;
    }

    public function readfile() {
        if (!headers_sent()) {
            $mime_type = $this->get_mime();
            if ($mime_type !== null) {
                header('Content-Type: ' . $mime_type);
            }
        }
        readfile($this->fs_path);
    }

    ///////////////////////////////////////

    public function mkdir() {
        if (!file_exists($this->fs_path)) {
            return \mkdir($this->fs_path, 0777, true);
        }
        elseif (!is_dir($this->fs_path)) {
            throw new Exception('已存在同名文件');
        }
        return true;
    }

    public function put_content($data, $flags = 0) {
        $this->get_parent()->mkdir();
        return file_put_contents($this->fs_path, $data, $flags);
    }

    /**
     *
     * @param \Minifw\Common\File $dest
     * @return boolean
     */
    public function copy($dest) {
        if (!file_exists($this->fs_path)) {
            return false;
        }

        if (!is_object($dest)) {
            $dest = new File($dest, null, $this->fs_encoding);
        }

        $dest->get_parent()->mkdir();

        return \copy($this->fs_path, $dest->fs_path);
    }

    /**
     *
     * @param \Minifw\Common\File $dest
     * @param boolean $hidden
     * @return boolean
     * @throws Exception
     */
    public function copy_dir($dest, $hidden = false) {
        if (!is_object($dest)) {
            $dest = new File($dest, null, $this->fs_encoding);
        }

        $dest->mkdir();

        if (!\is_dir($dest->fs_path) || !\is_dir($this->fs_path)) {
            throw new Exception('对象不是目录');
        }

        $from_dir = $this->fs_path;
        if (substr($from_dir, -1) !== '/') {
            $from_dir .= '/';
        }
        $to_dir = $dest->fs_path;
        if (substr($to_dir, -1) !== '/') {
            $to_dir .= '/';
        }

        if ($dh = opendir($from_dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if ($file[0] == '.' && !$hidden) {
                    continue;
                }

                $from = $from_dir . $file;
                $to = $to_dir . $file;

                $file_from = new File(null, $from, $this->fs_encoding);
                $file_to = new File(null, $to, $this->fs_encoding);

                if (is_dir($from)) {
                    $file_from->copy_dir($file_to, $hidden);
                }
                else {
                    $file_from->copy($file_to);
                }
            }
            closedir($dh);
        }

        return $dest;
    }

    /**
     *
     * @param \Minifw\Common\File $dest
     * @return boolean
     */
    public function rename($dest) {
        if (!file_exists($this->fs_path)) {
            return false;
        }

        if (!is_object($dest)) {
            $dest = new File($dest, null, $this->fs_encoding);
        }

        $dest->get_parent()->mkdir();

        if (\rename($this->fs_path, $dest->fs_path)) {
            $this->set_fs_path($dest->fs_path);
            return true;
        }

        return false;
    }

    public function delete($delete_parent = false) {
        if (file_exists($this->fs_path)) {
            if (is_dir($this->fs_path)) {
                rmdir($this->fs_path);
            }
            else {
                @unlink($this->fs_path);
            }

            if ($delete_parent) {
                $parent = $this->get_parent();

                if ($parent->is_dir_empty()) {
                    $parent->delete();
                }
            }
        }

        return true;
    }

    public function delete_with_tail() {
        $pinfo = pathinfo($this->fs_path);
        $dir = $pinfo['dirname'] . '/';
        $name = $pinfo['filename'];
        $files = [];

        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (preg_match('/^' . $name . '_?.*$/i', $file)) {
                    $files[] = $dir . $file;
                }
            }
            closedir($dh);
        }

        foreach ($files as $one) {
            $file = new File(null, $one, $this->fs_encoding);
            $file->delete();
        }

        return true;
    }

    public function clear_dir($delete_self = false) {
        if (!is_dir($this->fs_path)) {
            return;
        }

        $full = $this->fs_path;
        if (substr($full, -1) !== '/') {
            $full .= '/';
        }

        if ($dh = opendir($full)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $sub = $full . $file;

                if (is_dir($sub)) {
                    $file = new File(null, $sub, $this->fs_encoding);
                    $file->clear_dir(true);
                }
                else {
                    @unlink($sub);
                }
            }
            closedir($dh);
        }

        if ($delete_self) {
            rmdir($full);
        }
    }

    /////////////////////////////////////////////////////

    public function init_file($filesize) {
        $dir = FileUtils::dirname($this->fs_path);
        if (!file_exists($dir)) {
            \mkdir($dir, 0777, true);
        }

        $dh = fopen($this->fs_path, 'w+');
        if ($dh === false) {
            throw new Exception('文件建立失败');
        }
        if (!ftruncate($dh, $filesize)) {
            unlink($this->fs_path);
            throw new Exception('文件建立失败');
        }
        fclose($dh);

        $cfg_path = $this->fs_path . '.ucfg';
        $dh = fopen($cfg_path, 'w+');
        if ($dh === false) {
            throw new Exception('文件建立失败');
        }
        fclose($dh);
    }

    public function write_chunk($chunk, $total, $file) {
        $cfg_path = $this->fs_path . '.ucfg';

        if (!file_exists($this->fs_path) || !file_exists($cfg_path)) {
            throw new Exception('文件不存在');
        }

        $tmp_name = $file['tmp_name'];
        $content = file_get_contents($tmp_name);
        $size = strlen($content);
        unlink($tmp_name);

        $dh = fopen($this->fs_path, 'r+');
        if ($chunk == $total - 1) {
            fseek($dh, -1 * $size, SEEK_END);
        }
        else {
            fseek($dh, $size * $chunk, SEEK_SET);
        }
        fwrite($dh, $content, $size);
        fclose($dh);

        $cfg = file($cfg_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($cfg == null || $cfg == '') {
            $cfg = [
                $total, 0, str_repeat('0', $total)
            ];
        }
        $cfg[2][$chunk] = '1';
        $cfg[1] = strpos($cfg[2], '0', $cfg[1]);
        if ($cfg[1] === false) {
            $cfg[1] = $total;
        }
        file_put_contents($cfg_path, implode("\n", $cfg));
    }

}
