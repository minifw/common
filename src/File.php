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

class File
{
    /**
     * @var string
     */
    public static $defaultFsEncoding = '';
    /**
     * @var mixed
     */
    protected $fsEncoding;
    /**
     * @var string
     */
    protected $appEncoding = 'utf-8';
    /**
     * @var string
     */
    protected $fsPath = '';
    /**
     * @var string
     */
    protected $appPath = '';
    /**
     * @var array
     */
    public static $mimeHash = [
        'css' => 'text/css',
        'html' => 'text/html',
        'js' => 'text/javascript'
    ];

    /**
     * @param $appPath
     * @param $fsPath
     * @param null $fsEncoding
     */
    public function __construct($appPath, $fsPath = null, $fsEncoding = null)
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

    /**
     * @return mixed
     */
    public function getFsPath()
    {
        return $this->fsPath;
    }

    /**
     * @return mixed
     */
    public function getAppPath()
    {
        return $this->appPath;
    }

    /**
     * @param $path
     */
    public function setAppPath($path)
    {
        $path = rtrim($path, '/\\');

        $this->appPath = $path;
        if (!empty($this->fsEncoding) && $this->fsEncoding != $this->appEncoding) {
            $this->fsPath = iconv($this->appEncoding, $this->fsEncoding, $this->appPath);
        } else {
            $this->fsPath = $this->appPath;
        }
    }

    /**
     * @param $path
     */
    public function setFsPath($path)
    {
        $path = rtrim($path, '/\\');

        $this->fsPath = $path;
        if (!empty($this->fsEncoding) && $this->fsEncoding != $this->appEncoding) {
            $this->appPath = iconv($this->fsEncoding, $this->appEncoding, $this->fsPath);
        } else {
            $this->appPath = $this->fsPath;
        }
    }

    public function getContent()
    {
        if (file_exists($this->fsPath)) {
            return file_get_contents($this->fsPath);
        }

        return '';
    }

    /**
     * @param $function
     */
    public function call($function)
    {
        return call_user_func($function, $this->fsPath);
    }

    /**
     *
     * @return \Minifw\Common\File
     */
    public function getParent()
    {
        $parent = FileUtils::dirname($this->appPath);

        return new File($parent, '', $this->fsEncoding);
    }

    public function isDirEmpty()
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

    /**
     * @param $ext
     * @param $hidden
     * @return mixed
     */
    public function ls($ext = '', $hidden = false)
    {
        if (is_dir($this->fsPath)) {
            $res = [];

            $full = $this->fsPath;
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

                    if (!empty($this->fsEncoding) && $this->fsEncoding != $this->appEncoding) {
                        $file = iconv($this->fsEncoding, $this->appEncoding, $file);
                    }

                    $res[] = [
                        'name' => $file,
                        'dir' => is_dir($this->appPath . '/' . $file)
                    ];
                }
                closedir($dh);
            }

            usort($res, function ($left, $right) {
                if ($left['dir'] == $right['dir']) {
                    return strcmp($left['name'], $right['name']);
                } elseif ($left['dir']) {
                    return -1;
                }

                return 1;
            });

            return $res;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getMime()
    {
        if (file_exists($this->fsPath)) {
            $pinfo = pathinfo($this->fsPath);
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            $mimeType = 'application/octet-stream';
            if (isset(self::$mimeHash[$ext])) {
                $mimeType = self::$mimeHash[$ext];
            } else {
                $fi = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $fi->file($this->fsPath);
            }

            return $mimeType;
        }

        return null;
    }

    public function readfile()
    {
        if (!headers_sent()) {
            $mimeType = $this->getMime();
            if ($mimeType !== null) {
                header('Content-Type: ' . $mimeType);
            }
        }
        readfile($this->fsPath);
    }

    ///////////////////////////////////////

    public function mkdir()
    {
        if (!file_exists($this->fsPath)) {
            return \mkdir($this->fsPath, 0777, true);
        } elseif (!is_dir($this->fsPath)) {
            throw new Exception('已存在同名文件');
        }

        return true;
    }

    /**
     * @param $data
     * @param $flags
     */
    public function putContent($data, $flags = 0)
    {
        $this->getParent()->mkdir();

        return file_put_contents($this->fsPath, $data, $flags);
    }

    /**
     *
     * @param \Minifw\Common\File $dest
     * @return \Minifw\Common\File 新路径的文件对象
     */
    public function copy($dest)
    {
        if (!file_exists($this->fsPath)) {
            return false;
        }

        if (!is_object($dest)) {
            $dest = new File($dest, null, $this->fsEncoding);
        }

        $dest->getParent()->mkdir();

        if (\copy($this->fsPath, $dest->fsPath)) {
            return $dest;
        }

        return false;
    }

    /**
     *
     * @param \Minifw\Common\File $dest
     * @param boolean $hidden
     * @return \Minifw\Common\File 新路径的文件对象
     * @throws Exception
     */
    public function copyDir($dest, $hidden = false)
    {
        if (!is_object($dest)) {
            $dest = new File($dest, null, $this->fsEncoding);
        }

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

    /**
     *
     * @param \Minifw\Common\File $dest
     * @return \Minifw\Common\File 新路径的文件对象
     */
    public function rename($dest)
    {
        if (!file_exists($this->fsPath)) {
            return false;
        }

        if (!is_object($dest)) {
            $dest = new File($dest, null, $this->fsEncoding);
        }

        $dest->getParent()->mkdir();

        if (\rename($this->fsPath, $dest->fsPath)) {
            return $dest;
        }

        return false;
    }

    /**
     * @param $delete_parent
     */
    public function delete($delete_parent = false)
    {
        if (file_exists($this->fsPath)) {
            if (is_dir($this->fsPath)) {
                rmdir($this->fsPath);
            } else {
                @unlink($this->fsPath);
            }

            if ($delete_parent) {
                $parent = $this->getParent();

                if ($parent->isDirEmpty()) {
                    $parent->delete();
                }
            }
        }

        return true;
    }

    public function deleteWithTail()
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

        return true;
    }

    /**
     * @param $delete_self
     * @return null
     */
    public function clearDir($delete_self = false)
    {
        if (!is_dir($this->fsPath)) {
            return;
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

    /**
     * @param $filesize
     */
    public function initFile($filesize)
    {
        $dir = FileUtils::dirname($this->fsPath);
        if (!file_exists($dir)) {
            \mkdir($dir, 0777, true);
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

    /**
     * @param $chunk
     * @param $total
     * @param $file
     */
    public function writeChunk($chunk, $total, $file)
    {
        $cfgPath = $this->fsPath . '.ucfg';

        if (!file_exists($this->fsPath) || !file_exists($cfgPath)) {
            throw new Exception('文件不存在');
        }

        $tmp_name = $file['tmp_name'];
        $content = file_get_contents($tmp_name);
        $size = strlen($content);
        unlink($tmp_name);

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
