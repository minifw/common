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

use finfo;

class File
{
    public static string $defaultFsEncoding = '';
    protected string $fsEncoding;
    protected string $appEncoding = 'utf-8';
    protected string $fsPath = '';
    protected string $appPath = '';
    public static array $mimeHash = [
        'css' => 'text/css',
        'html' => 'text/html',
        'js' => 'text/javascript'
    ];

    public function toFile($appPath, ?string $fsEncoding = null) : self
    {
        if (is_string($appPath)) {
            return new self($appPath, null, $fsEncoding);
        } elseif (!($appPath instanceof self)) {
            throw new Exception('参数不合法');
        }

        return $appPath;
    }

    public function __construct(?string $appPath, ?string $fsPath = null, ?string $fsEncoding = null)
    {
        if (empty($fsEncoding)) {
            $this->fsEncoding = self::$defaultFsEncoding;
        } else {
            $this->fsEncoding = $fsEncoding;
        }

        if (!empty($appPath)) {
            $this->setAppPath($appPath);
        } elseif (!empty($fsPath)) {
            $this->setFsPath($fsPath);
        } else {
            throw new Exception('必须指定路径');
        }
    }

    public function getFsPath() : string
    {
        return $this->fsPath;
    }

    public function getAppPath() : string
    {
        return $this->appPath;
    }

    public function setAppPath(string $path) : void
    {
        $path = rtrim($path, '/\\');

        $this->appPath = $path;
        if (!empty($this->fsEncoding) && $this->fsEncoding != $this->appEncoding) {
            $this->fsPath = iconv($this->appEncoding, $this->fsEncoding, $this->appPath);
        } else {
            $this->fsPath = $this->appPath;
        }
    }

    public function setFsPath(string $path) : void
    {
        $path = rtrim($path, '/\\');

        $this->fsPath = $path;
        if (!empty($this->fsEncoding) && $this->fsEncoding != $this->appEncoding) {
            $this->appPath = iconv($this->fsEncoding, $this->appEncoding, $this->fsPath);
        } else {
            $this->appPath = $this->fsPath;
        }
    }

    public function getContent() : string
    {
        if (file_exists($this->fsPath)) {
            return file_get_contents($this->fsPath);
        }

        return '';
    }

    public function call(callable $function)
    {
        return call_user_func($function, $this->fsPath);
    }

    public function getParent() : self
    {
        $parent = FileUtils::dirname($this->appPath);

        return new File($parent, '', $this->fsEncoding);
    }

    public function isDirEmpty() : bool
    {
        if (is_dir($this->fsPath)) {
            $dir = opendir($this->fsPath);
            while (false !== ($file = readdir($dir))) {
                if ($file == '.' || $file == '..') {
                    continue;
                } else {
                    closedir($dir);

                    return false;
                }
            }
            closedir($dir);

            return true;
        } else {
            return false;
        }
    }

    public function ls(string $ext = '', bool $hidden = false) : ?array
    {
        if (!is_dir($this->fsPath)) {
            return null;
        }

        $list = $this->map(function ($obj, $prefix) use ($hidden, $ext) {
            $name = $obj->getName();
            $isDir = $obj->isDir();

            if (!$hidden && $name[0] == '.') {
                return null;
            }

            if ($ext !== '') {
                if ($isDir || substr($name, -1 * strlen($ext)) != $ext) {
                    return null;
                }
            }

            return [
                'name' => $name,
                'dir' => $isDir
            ];
        }, false);

        usort($list, function ($left, $right) {
            if ($left['dir'] == $right['dir']) {
                return strcmp($left['name'], $right['name']);
            } elseif ($left['dir']) {
                return -1;
            }

            return 1;
        });

        return $list;
    }

    public function map(callable $callable, bool $includeSub = true, string $prefix = '')
    {
        if (!is_dir($this->fsPath)) {
            return null;
        }

        $res = [];

        $full = $this->fsPath;
        if (substr($full, -1) !== '/') {
            $full .= '/';
        }

        if ($prefix !== '') {
            $prefix .= '/';
        }

        if ($dh = opendir($full)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if ($file[0] == '.') {
                    continue;
                }

                $obj = new self(null, $full . $file);

                if (is_dir($full . $file) && $includeSub) {
                    $sub = $obj->map($callable, $includeSub, $prefix . $obj->getName());
                    if ($sub !== null) {
                        $res = array_merge($res, $sub);
                    }
                } else {
                    $sub = call_user_func($callable, $obj, $prefix);
                    if ($sub !== null) {
                        $res[] = $sub;
                    }
                }
            }
            closedir($dh);
        }

        return $res;
    }

    public function getMime() : ?string
    {
        if (file_exists($this->fsPath)) {
            $pinfo = pathinfo($this->fsPath);
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            $mimeType = 'application/octet-stream';
            if (isset(self::$mimeHash[$ext])) {
                $mimeType = self::$mimeHash[$ext];
            } else {
                $fi = new finfo(FILEINFO_MIME_TYPE, null);
                $mimeType = $fi->file($this->fsPath);
            }

            return $mimeType;
        }

        return null;
    }

    public function readfile() : void
    {
        if (!headers_sent()) {
            $mimeType = $this->getMime();
            if ($mimeType !== null) {
                header('Content-Type: ' . $mimeType);
            }
        }
        readfile($this->fsPath);
    }

    public function isDir() : bool
    {
        return is_dir($this->fsPath);
    }

    public function isFile() : bool
    {
        return is_file($this->fsPath);
    }

    public function getName() : string
    {
        return FileUtils::basename($this->appPath);
    }

    ///////////////////////////////////////

    public function mkdir() : void
    {
        if (!file_exists($this->fsPath)) {
            if (!\mkdir($this->fsPath, 0777, true)) {
                throw new Exception('目录创建失败');
            }
        } elseif (!is_dir($this->fsPath)) {
            throw new Exception('已存在同名文件');
        }
    }

    public function putContent($data, int $flags = 0) : void
    {
        $this->getParent()->mkdir();

        $ret = file_put_contents($this->fsPath, $data, $flags);
        if ($ret === false) {
            throw new Exception('文件写入失败');
        }
    }

    public function copy($dest) : self
    {
        if (!file_exists($this->fsPath)) {
            throw new Exception('文件不存在');
        }

        $dest = self::toFile($dest, $this->fsEncoding);

        $dest->getParent()->mkdir();

        if (!\copy($this->fsPath, $dest->fsPath)) {
            throw new Exception('文件复制失败');
        }

        return $dest;
    }

    public function copyDir($dest, bool $hidden = false) : self
    {
        $dest = self::toFile($dest, $this->fsEncoding);

        $dest->mkdir();

        if (!\is_dir($dest->fsPath) || !\is_dir($this->fsPath)) {
            throw new Exception('对象不是目录');
        }

        $from_dir = $this->fsPath;
        if (substr($from_dir, -1) !== '/') {
            $from_dir .= '/';
        }
        $to_dir = $dest->fsPath;
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

                $file_from = new File(null, $from, $this->fsEncoding);
                $file_to = new File(null, $to, $this->fsEncoding);

                if (is_dir($from)) {
                    $file_from->copyDir($file_to, $hidden);
                } else {
                    $file_from->copy($file_to);
                }
            }
            closedir($dh);
        }

        return $dest;
    }

    public function rename($dest) : self
    {
        if (!file_exists($this->fsPath)) {
            throw new Exception('文件不存在');
        }

        $dest = self::toFile($dest, $this->fsEncoding);

        $dest->getParent()->mkdir();

        if (!\rename($this->fsPath, $dest->fsPath)) {
            throw new Exception('文件重命名失败');
        }

        return $dest;
    }

    public function delete(bool $delete_parent = false) : void
    {
        if (file_exists($this->fsPath)) {
            if (is_dir($this->fsPath)) {
                if (!rmdir($this->fsPath)) {
                    throw new Exception('目录删除失败');
                }
            } else {
                if (!@unlink($this->fsPath)) {
                    throw new Exception('文件删除失败');
                }
            }

            if ($delete_parent) {
                $parent = $this->getParent();

                if ($parent->isDirEmpty()) {
                    $parent->delete();
                }
            }
        }
    }

    public function deleteWithTail() : void
    {
        $pinfo = pathinfo($this->fsPath);
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
            $file = new File(null, $one, $this->fsEncoding);
            $file->delete();
        }
    }

    public function clearDir(bool $delete_self = false) : void
    {
        if (!is_dir($this->fsPath)) {
            throw new Exception('目录不存在');
        }

        $full = $this->fsPath;
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
                    $file = new File(null, $sub, $this->fsEncoding);
                    $file->clearDir(true);
                } else {
                    if (!@unlink($sub)) {
                        throw new Exception('文件删除失败');
                    }
                }
            }
            closedir($dh);
        }

        if ($delete_self) {
            if (!rmdir($full)) {
                throw new Exception('目录删除失败');
            }
        }
    }

    /////////////////////////////////////////////////////

    public function initFile(int $filesize) : void
    {
        $dir = FileUtils::dirname($this->fsPath);
        if (!file_exists($dir)) {
            if (!\mkdir($dir, 0777, true)) {
                throw new Exception('文件创建失败');
            }
        }

        $dh = fopen($this->fsPath, 'w+');
        if ($dh === false) {
            throw new Exception('文件建立失败');
        }
        if (!ftruncate($dh, $filesize)) {
            unlink($this->fsPath);
            throw new Exception('文件建立失败');
        }
        fclose($dh);

        $cfgPath = $this->fsPath . '.ucfg';
        $dh = fopen($cfgPath, 'w+');
        if ($dh === false) {
            throw new Exception('文件建立失败');
        }
        fclose($dh);
    }

    public function writeChunk(int $chunk, int $total, array $file) : void
    {
        $cfgPath = $this->fsPath . '.ucfg';

        if (!file_exists($this->fsPath) || !file_exists($cfgPath)) {
            throw new Exception('文件不存在');
        }

        $tmp_name = $file['tmp_name'];
        $content = file_get_contents($tmp_name);
        $size = strlen($content);
        @unlink($tmp_name);

        $dh = fopen($this->fsPath, 'r+');
        if ($chunk == $total - 1) {
            fseek($dh, -1 * $size, SEEK_END);
        } else {
            fseek($dh, $size * $chunk, SEEK_SET);
        }
        fwrite($dh, $content, $size);
        fclose($dh);

        $cfg = file($cfgPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
        file_put_contents($cfgPath, implode("\n", $cfg));
    }
}
