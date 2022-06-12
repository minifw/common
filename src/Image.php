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
use Minifw\Common\ImageUtils;

class Image
{
    const FORMAT_GIF = 1;
    const FORMAT_JPG = 2;
    const FORMAT_PNG = 3;

    /**
     * @var int
     */
    public static $default_quality = 90;
    /**
     * @var int
     */
    public static $default_level = 2;
    /**
     * @var \GdImage
     */
    protected $imageObj = null;
    /**
     * @var int
     */
    protected $width = 0;
    /**
     * @var int
     */
    protected $height = 0;
    /**
     * @var int
     */
    protected $format = 0;

    public function __construct()
    {
    }

    /**
     * @param $full
     * @return mixed
     */
    public function loadImage($full)
    {
        $this->destroy();

        $info = ImageUtils::getImageInfo($full);
        $this->format = $info['format'];
        $this->width = $info['width'];
        $this->height = $info['height'];

        $this->imageObj = ImageUtils::loadImageObj($full, $this->format);

        return $this;
    }

    /**
     * @param $format
     * @param $width
     * @param $height
     * @param $bgcolor
     * @return mixed
     */
    public function initImage($format, $width, $height, $bgcolor = null)
    {
        $this->destroy();
        $this->imageObj = ImageUtils::getNewImage($format, $width, $height, $bgcolor);
        $this->format = $format;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * @param $degree
     * @return mixed
     */
    public function rotate($degree)
    {
        if ($degree % 90 !== 0) {
            throw new Exception('不支持该操作');
        }
        $degree = $degree % 360;
        if ($degree == 0) {
            return $this;
        }
        $new_obj = imagerotate($this->imageObj, $degree, 0);
        imagedestroy($this->imageObj);
        $this->imageObj = $new_obj;

        if ($degree % 180 !== 0) {
            $tmp = $this->height;
            $this->height = $this->width;
            $this->width = $tmp;
        }

        return $this;
    }

    /**
     * @param $full
     * @param $dst_x
     * @param $dst_y
     * @param $src_x
     * @param $src_y
     * @param $dst_w
     * @param $dst_h
     * @param $src_w
     * @param $src_h
     * @return mixed
     */
    public function merge($full, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
    {
        $info = ImageUtils::getImageInfo($full);
        $src_obj = ImageUtils::loadImageObj($full, $info['format']);
        imagecopyresampled($this->imageObj, $src_obj, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        imagedestroy($src_obj);

        return $this;
    }

    /**
     * @param $new_w
     * @param $new_h
     * @param $bgcolor
     * @param $dst_x
     * @param $dst_y
     * @param $src_x
     * @param $src_y
     * @param $dst_w
     * @param $dst_h
     * @param $src_w
     * @param $src_h
     * @return mixed
     */
    public function transform($new_w, $new_h, $bgcolor, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
    {
        $src_obj = $this->imageObj;
        $this->imageObj = ImageUtils::getNewImage($this->format, $new_w, $new_h, $bgcolor);
        $this->width = $new_w;
        $this->height = $new_h;

        imagecopyresampled($this->imageObj, $src_obj, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        imagedestroy($src_obj);

        return $this;
    }

    /**
     * @param $r
     * @param $level
     * @param $bgcolor
     * @return mixed
     */
    public function roundCorner($r, $level = -1, $bgcolor = null)
    {
        if ($level < 0) {
            $level = self::$default_level;
        }
        if ($r <= 0) {
            return $this;
        }
        $w = ceil($this->width / 2);
        $h = ceil($this->height / 2);

        if ($w > $r) {
            $w = $r;
        }
        if ($h > $r) {
            $h = $r;
        }

        if ($this->format == self::FORMAT_GIF) {
            $level = 0; //gif图片不支持抗锯齿
        }

        if ($bgcolor == null) {
            switch ($this->format) {
                case self::FORMAT_GIF:
                    $bgcolor = imagecolortransparent($this->imageObj);
                    break;
                case self::FORMAT_JPG:
                    $bgcolor = imagecolorallocate($this->imageObj, 255, 255, 255);
                    break;
                case self::FORMAT_PNG:
                    $bgcolor = imagecolorallocatealpha($this->imageObj, 0, 0, 0, 127);
                    break;
            }
        }

        $this->roundOneCorner($r, $r, $r, 0, 0, $w, $h, $level, $bgcolor);
        $this->roundOneCorner($r, $this->width - $r, $r, $this->width - $w, 0, $w, $h, $level, $bgcolor);
        $this->roundOneCorner($r, $r, $this->height - $r, 0, $this->height - $h, $w, $h, $level, $bgcolor);
        $this->roundOneCorner($r, $this->width - $r, $this->height - $r, $this->width - $w, $this->height - $h, $w, $h, $level, $bgcolor);

        return $this;
    }

    /**
     * @param $r
     * @param $cx
     * @param $cy
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     * @param $level
     * @param $bgcolor
     * @return mixed
     */
    public function roundOneCorner($r, $cx, $cy, $x, $y, $w, $h, $level, $bgcolor)
    {
        $br = (($bgcolor >> 16) & 0xFF);
        $bg = (($bgcolor >> 8) & 0xFF);
        $bb = ($bgcolor & 0xFF);
        $ba = (($bgcolor >> 24) & 0xFF);

        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $px = $x + $i;
                $py = $y + $j;

                $alpha = self::calcAlpha($r, $px - $cx, $py - $cy, $level);
                if ($alpha <= 0) {
                    continue;
                } elseif ($alpha >= 127) {
                    imagesetpixel($this->imageObj, $px, $py, $bgcolor);
                } else {
                    $color = imagecolorat($this->imageObj, $px, $py);
                    $alpah_c = 127 - $alpha;

                    $cr = (($color >> 16) & 0xFF) * $alpah_c + $br * $alpha;
                    $cg = (($color >> 8) & 0xFF) * $alpah_c + $bg * $alpha;
                    $cb = ($color & 0xFF) * $alpah_c + $bg + $bb * $alpha;
                    $ca = (($color >> 24) & 0xFF) * $alpah_c + $ba * $alpha;

                    $color = ($ca / 127) << 24 | ($cr / 127) << 16 | ($cg / 127) << 8 | ($cb / 127);
                    imagesetpixel($this->imageObj, $px, $py, $color);
                }
            }
        }

        return $this;
    }

    /**
     * @param $dest
     * @param $quality
     * @return mixed
     */
    public function save($dest, $quality = -1)
    {
        if ($quality < 0) {
            $quality = self::$default_quality;
        }
        switch ($this->format) {
            case self::FORMAT_GIF:
                imagegif($this->imageObj, $dest);
                break;
            case self::FORMAT_JPG:
                imagejpeg($this->imageObj, $dest, $quality);
                break;
            case self::FORMAT_PNG:
                //imagealphablending($imageObj, true);
                //imagesavealpha($imageObj, true);
                imagepng($this->imageObj, $dest);
                break;
        }

        return $this;
    }

    /**
     * @return null
     */
    public function destroy()
    {
        if ($this->imageObj === null) {
            return;
        }
        imagedestroy($this->imageObj);
        $this->imageObj = null;
        $this->width = 0;
        $this->height = 0;
        $this->format = 0;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    public function getSize()
    {
        return [$this->width, $this->height];
    }

    /**
     * @return mixed
     */
    public function getObj()
    {
        return $this->imageObj;
    }

    //////////////////////////////////////

    /**
     * @param $r
     * @param $x
     * @param $y
     * @param $level
     * @return int
     */
    public static function calcAlpha($r, $x, $y, $level)
    {
        $r2 = $r * $r;
        $offset = $x * $x + $y * $y - $r2;
        if ($level <= 0) {
            if ($offset > 0) {
                return 127;
            } else {
                return 0;
            }
        }

        $offset -= 0.5;
        $limit = 1.42 * $r;
        if ($offset >= $limit) {
            return 127;
        } elseif ($offset < -1 * $limit) {
            return 0;
        }

        if ($level > 4) {
            $level = 4;
        }

        $len = 1 << $level;
        $step = 1 / $len;
        $bx = $x - 0.5 + $step / 2;
        $by = $y - 0.5 + $step / 2;
        $count = 0;

        for ($i = 0; $i < $len; $i++) {
            for ($j = 0; $j < $len; $j++) {
                $sx = $bx + $step * $i;
                $sy = $by + $step * $j;
                if ($sx * $sx + $sy * $sy - $r2 > 0) {
                    $count++;
                }
            }
        }

        return intval(($count / $len / $len) * 127);
    }
}
