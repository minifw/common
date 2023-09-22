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

class Image
{
    public const FORMAT_GIF = 1;
    public const FORMAT_JPG = 2;
    public const FORMAT_PNG = 3;
    public static int $defaultQuality = 90;
    public static int $defaultLevel = 2;
    protected $imageObj = null;
    protected int $width = 0;
    protected int $height = 0;
    protected int $format = 0;

    public function __construct()
    {
    }

    public function loadImage(string $full): self
    {
        $this->destroy();

        $info = ImageUtils::getImageInfo($full);
        $this->format = $info['format'];
        $this->width = $info['width'];
        $this->height = $info['height'];

        $this->imageObj = ImageUtils::loadImageObj($full, $this->format);

        return $this;
    }

    public function initImage(int $format, int $width, int $height, $bgcolor = null): self
    {
        $this->destroy();
        $this->imageObj = ImageUtils::getNewImage($format, $width, $height, $bgcolor);
        $this->format = $format;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function rotate(int $degree): self
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

    public function merge(string $srcFull, int $dstX, int $dstY, int $srcX, int $srcY, int $dstW, int $dstH, int $srcW, int $srcH): self
    {
        $info = ImageUtils::getImageInfo($srcFull);
        $srcObj = ImageUtils::loadImageObj($srcFull, $info['format']);
        imagecopyresampled($this->imageObj, $srcObj, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
        imagedestroy($srcObj);

        return $this;
    }

    public function transform(int $newW, int $newH, $bgColor, int $dstX, int $dstY, int $srcX, int $srcY, int $dstW, int $dstH, int $srcW, int $srcH): self
    {
        $srcObj = $this->imageObj;
        $this->imageObj = ImageUtils::getNewImage($this->format, $newW, $newH, $bgColor);
        $this->width = $newW;
        $this->height = $newH;

        imagecopyresampled($this->imageObj, $srcObj, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
        imagedestroy($srcObj);

        return $this;
    }

    public function roundCorner(int $r, int $level = -1, $bgColor = null): self
    {
        if ($level < 0) {
            $level = self::$defaultLevel;
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

        if ($bgColor == null) {
            switch ($this->format) {
                case self::FORMAT_GIF:
                    $bgColor = imagecolortransparent($this->imageObj);
                    break;
                case self::FORMAT_JPG:
                    $bgColor = imagecolorallocate($this->imageObj, 255, 255, 255);
                    break;
                case self::FORMAT_PNG:
                    $bgColor = imagecolorallocatealpha($this->imageObj, 0, 0, 0, 127);
                    break;
            }
        }

        $this->roundOneCorner($r, $r, $r, 0, 0, $w, $h, $level, $bgColor);
        $this->roundOneCorner($r, $this->width - $r, $r, $this->width - $w, 0, $w, $h, $level, $bgColor);
        $this->roundOneCorner($r, $r, $this->height - $r, 0, $this->height - $h, $w, $h, $level, $bgColor);
        $this->roundOneCorner($r, $this->width - $r, $this->height - $r, $this->width - $w, $this->height - $h, $w, $h, $level, $bgColor);

        return $this;
    }

    public function roundOneCorner(int $r, int $cx, int $cy, int $x, int $y, int $w, int $h, int $level, $bgcolor): self
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

                    $color = (int)($ca / 127) << 24 | (int)($cr / 127) << 16 | (int)($cg / 127) << 8 | (int)($cb / 127);
                    imagesetpixel($this->imageObj, $px, $py, $color);
                }
            }
        }

        return $this;
    }

    public function save(string $dest, int $quality = -1): self
    {
        if ($quality < 0) {
            $quality = self::$defaultQuality;
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

    public function destroy(): void
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

    public function getFormat(): int
    {
        return $this->format;
    }

    public function getSize(): array
    {
        return [$this->width, $this->height];
    }

    public function getObj()
    {
        return $this->imageObj;
    }

    //////////////////////////////////////

    public static function calcAlpha(int $r, int $x, int $y, int $level): int
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
