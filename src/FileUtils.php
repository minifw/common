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

class FileUtils
{
    public static function pathJoin(string ...$args) : ?string
    {
        $args = array_reverse($args);

        $cur_path = [];
        foreach ($args as $arg) {
            if ($arg == '') {
                continue;
            }
            $arg = str_replace('\\', '/', $arg);
            $arg = rtrim($arg, '/');
            $path_array = explode('/', $arg);
            $cur_path = array_merge($path_array, $cur_path);
            if ($cur_path[0] == '' || preg_match('/^[a-zA-Z]:$/', $cur_path[0])) {
                break;
            }
        }

        $parsed = [];
        foreach ($cur_path as $v) {
            if ($v == '.') {
                continue;
            }
            if ($v == '..') {
                if (count($parsed) <= 1) {
                    return null;
                } else {
                    unset($parsed[count($parsed) - 1]);
                }
            } else {
                $parsed[count($parsed)] = $v;
            }
        }

        if (count($parsed) == 1 && $parsed[0] == '') {
            return '/';
        }

        return implode('/', $parsed);
    }

    /**
     * 从路径中截取出开头到第一个 `/` 之间的部分，不存在时返回空字符串.
     */
    public static function dirname(string $path) : string
    {
        $path = \dirname($path);
        if ($path == '.') {
            $path = '';
        }

        return $path;
    }

    /**
     * 从路径中截取出最后一个 `/` 到末尾的部分，不存在的时候返回完整路径.
     */
    public static function basename(string $path) : string
    {
        $path = basename($path);

        return $path;
    }

    /**
     * 从路径中截取出开头到 第一个|最后一个 `.`之间的部分，不存在的时候返回完整路径.
     */
    public static function filename(string $file, bool $last = true) : string
    {
        $pos = false;
        if ($last) {
            $pos = strrpos($file, '.');
        } else {
            $pos = strpos($file, '.');
        }
        if ($pos === false) {
            return $file;
        }

        return substr($file, 0, $pos);
    }

    public static function appentTail(string $path, string $tail) : string
    {
        if ($path == '') {
            return '';
        }
        if ($tail == '') {
            return $path;
        }

        $temp = pathinfo($path);
        $name = $temp['filename'];
        $path = $temp['dirname'];

        $ext = '';
        if (isset($temp['extension'])) {
            $ext = '.' . $temp['extension'];
        }

        return $path . '/' . $name . $tail . $ext;
    }

    public static function mkname(string $base_dir, string $tail) : string
    {
        $name = '';
        $count = 0;
        $now = time();
        $time = date('YmdHis', $now);
        $year = date('Y', $now);
        $month_day = date('md', $now);
        while ($name == '' && $count < 100) {
            $rand = rand(100000, 999999);
            $name = $year . '/' . $month_day . '/' . $time . $rand . $tail;
            if (file_exists($base_dir . '/' . $name)) {
                $name = '';
            }
            $count++;
        }

        return $name;
    }

    public static function getUploadName(array $file, bool $ext = false) : string
    {
        $name = trim($file['name']);
        $name = str_replace(' ', '', $name);
        $name = str_replace('　', '', $name);
        if ($ext) {
            return $name;
        }
        $pos = strrpos($name, '.');
        if ($pos === false) {
            return $name;
        }

        return trim(substr($name, 0, $pos));
    }

    //////////////////////////////////////////////////

    /**
     * 滚动日志文件.
     */
    public static function rotateFile(string $path, string $ext, int $count, $fp = null)
    {
        $basename = self::filename($path, true);
        $dir = self::dirname($path);
        if (empty($dir)) {
            throw new Exception('参数不合法');
        }

        if ($count < 1) {
            throw new Exception('参数不合法');
        }

        for ($i = $count;$i > 0;$i--) {
            $full = $basename . '.' . $i . $ext;
            if (file_exists($full)) {
                if ($i >= $count) {
                    unlink($full);
                } else {
                    rename($full, $basename . '.' . ($i + 1) . $ext);
                }
            }
        }

        $bakFile = $basename . '.1' . $ext;
        copy($path, $bakFile);
        if ($fp !== null) {
            ftruncate($fp, 0);
        }
    }

    public static function uploadFile(array $file, string $base_dir, int $maxsize = 0, array $allow = []) : string
    {
        if (empty($file)) {
            return '';
        }
        if ($file['error'] != UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    throw new Exception('文件超过服务器允许的大小');
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception('文件超过表单允许的大小');
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception('文件上传不完整');
                case UPLOAD_ERR_NO_FILE: //未选择文件
                    return '';
                case UPLOAD_ERR_NO_TMP_DIR:
                    throw new Exception('文件上传出错');
                case UPLOAD_ERR_CANT_WRITE:
                    throw new Exception('文件上传出错');
                case UPLOAD_ERR_EXTENSION:
                    throw new Exception('文件上传出错');
                default:
                    throw new Exception('文件上传出错');
            }
        }

        $filesize = intval($file['size']);
        if ($maxsize > 0 && $filesize > $maxsize) {
            throw new Exception('文件大小超过限制');
        }

        $pinfo = pathinfo($file['name']);
        $ext = strtolower($pinfo['extension']);

        if (!empty($allow) && !in_array($ext, $allow)) {
            throw new Exception('不允许的文件类型');
        }

        $name = self::mkname($base_dir, '.' . $ext);
        if ($name == '') {
            throw new Exception('同一时间上传的文件过多');
        }
        $dest = $base_dir . '/' . $name;

        $fileObj = new File($dest);
        $fileObj->getParent()->mkdir();

        if (move_uploaded_file($file['tmp_name'], $fileObj->getFsPath())) {
            return $name;
        } else {
            throw new Exception('文件移动出错');
        }
    }

    public static function saveFile($data, string $base_dir, string $ext) : string
    {
        $name = self::mkname($base_dir, '.' . $ext);

        if ($name == '') {
            throw new Exception('同一时间上传的文件过多');
        }
        $dest = $base_dir . '/' . $name;

        $file = new File($dest);
        if ($file->putContent($data)) {
            return $name;
        } else {
            throw new Exception('文件写入出错');
        }
    }

    public static function initFile(string $oname, int $filesize, string $base_dir, int $maxsize = 0, array $allow = []) : string
    {
        if ($maxsize > 0 && $filesize > $maxsize) {
            throw new Exception('文件大小超过限制');
        }

        $pinfo = pathinfo($oname);
        $ext = strtolower($pinfo['extension']);

        if (!empty($allow) && !in_array($ext, $allow)) {
            throw new Exception('不允许的文件类型');
        }

        $name = self::mkname($base_dir, '.' . $ext);
        if ($name == '') {
            throw new Exception('同一时间上传的文件过多');
        }
        $dest = $base_dir . '/' . $name;

        $file = new File($dest);
        $file->getParent()->mkdir();
        $file->initFile($filesize);

        return $name;
    }
}
