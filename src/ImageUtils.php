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

class ImageUtils
{
    const CUT_TOP_LEFT = 1;
    const CUT_TOP_RIGHT = 2;
    const CUT_BTM_LEFT = 3;
    const CUT_BTM_RIGHT = 4;
    const CUT_CENTER = 5;

    public static function loadImageObj(string $full, int $format)
    {
        switch ($format) {
            case Image::FORMAT_GIF:
                $imageObj = imagecreatefromgif($full);
                break;
            case Image::FORMAT_JPG:
                $imageObj = imagecreatefromjpeg($full);
                break;
            case Image::FORMAT_PNG:
                $imageObj = imagecreatefrompng($full);
                imagealphablending($imageObj, false);
                imagesavealpha($imageObj, true);
                break;
        }

        return $imageObj;
    }

    public static function getImageInfo(string $full) : array
    {
        if (!file_exists($full)) {
            throw new Exception('文件不存在');
        }
        $info = getimagesize($full);

        $hash = [
            1 => Image::FORMAT_GIF,
            2 => Image::FORMAT_JPG,
            3 => Image::FORMAT_PNG
        ];

        if (!isset($hash[$info[2]])) {
            throw new Exception('不支持的文件格式');
        }

        $ret = [
            'width' => $info[0],
            'height' => $info[1],
            'format' => $hash[$info[2]]
        ];

        return $ret;
    }

    public static function getNewImage(int $format, int $width, int $height, $bgcolor = null)
    {
        switch ($format) {
            case Image::FORMAT_GIF:
                $imageObj = imagecreate($width, $height);
                if ($bgcolor == null) {
                    $bgcolor = imagecolorallocate($imageObj, 0, 0, 0);
                }
                imagecolortransparent($imageObj, $bgcolor);
                break;
            case Image::FORMAT_JPG:
                $imageObj = imagecreatetruecolor($width, $height);
                if ($bgcolor == null) {
                    $bgcolor = imagecolorallocate($imageObj, 255, 255, 255);
                }
                imagefill($imageObj, 0, 0, $bgcolor);
                break;
            case Image::FORMAT_PNG:
                $imageObj = imagecreatetruecolor($width, $height);
                imagealphablending($imageObj, false);
                imagesavealpha($imageObj, true);
                if ($bgcolor == null) {
                    $bgcolor = imagecolorallocatealpha($imageObj, 0, 0, 0, 127);
                }
                imagefill($imageObj, 0, 0, $bgcolor);
                break;
            default:
                throw new Exception('不支持的图片格式');
        }

        return $imageObj;
    }

    /**
     * 将尺寸为width*height的图片等比缩放，并限制在maxWidth*maxHeight之内时的尺寸，当某一max参数为0时会忽略对应的参数.
     */
    public static function calcDstSize(int $width, int $height, int $maxWidth, int $maxHeight) : array
    {
        if ($maxWidth <= 0 && $maxHeight <= 0) {
            return [$width, $height];
        } elseif ($maxWidth <= 0) {
            $width = intval($maxHeight * $width / $height);
            $height = $maxHeight;
        } elseif ($maxHeight <= 0) {
            $height = intval($maxWidth * $height / $width);
            $width = $maxWidth;
        } else {
            if ($width * $maxHeight >= $height * $maxWidth) {
                $height = intval($maxWidth * $height / $width);
                $width = $maxWidth;
            } else {
                $width = intval($maxHeight * $width / $height);
                $height = $maxHeight;
            }
        }

        return [$width, $height];
    }

    /**
     * 将尺寸为width*height的图片等比缩放，并要求 width<=maxWidth||height<=maxHeight 时的尺寸.
     */
    public static function calcSrcSize(int $width, int $height, int $maxWidth, int $maxHeight) : array
    {
        if ($maxWidth <= 0 || $maxHeight <= 0) {
            return [$width, $height];
        }
        if ($width * $maxHeight <= $height * $maxWidth) {
            $height = intval($maxHeight * $width / $maxWidth);
        } else {
            $width = intval($maxWidth * $height / $maxHeight);
        }

        return [$width, $height];
    }

    /**
     * 生成指定尺寸的缩略图，并给缩略图添加指定后缀
     */
    public static function imageScale(string $src, string $tail = '', int $maxWidth = 0, int $maxHeight = 0) : string
    {
        $dest = FileUtils::appentTail($src, $tail);

        if (!file_exists($dest)) {
            try {
                $info = self::getImageInfo($src);

                list($dst_w, $dst_h) = self::calcDstSize($info['width'], $info['height'], $maxWidth, $maxHeight);

                $obj = new Image();
                $obj->loadImage($src)
                    ->transform($dst_w, $dst_h, null, 0, 0, 0, 0, $dst_w, $dst_h, $info['width'], $info['height'])
                    ->save($dest)
                    ->destroy();
            } catch (\Exception $ex) {
                copy($src, $dest);
            }
        }

        return $dest;
    }

    /**
     * 生成指定尺寸的缩略图，并给缩略图添加指定后缀，不足部分用指定颜色填充.
     */
    public static function imageScalePadding(string $src, string $tail, int $maxWidth, int $maxHeight, $bgcolor = null) : string
    {
        $dest = FileUtils::appentTail($src, $tail);

        if (!file_exists($dest)) {
            try {
                $info = self::getImageInfo($src);

                list($dst_w, $dst_h) = self::calcDstSize($info['width'], $info['height'], $maxWidth, $maxHeight);

                if ($dst_w < $maxWidth) {
                    $dst_x = intval(($maxWidth - $dst_w) / 2);
                } else {
                    $dst_x = 0;
                }

                if ($dst_h < $maxHeight) {
                    $dst_y = intval(($maxHeight - $dst_h) / 2);
                } else {
                    $dst_y = 0;
                }

                $obj = new Image();
                $obj->loadImage($src)
                    ->transform($maxWidth, $maxHeight, $bgcolor, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $info['width'], $info['height'])
                    ->save($dest)
                    ->destroy();
            } catch (\Exception $ex) {
                copy($src, $dest);
            }
        }

        return $dest;
    }

    /**
     * 生成指定尺寸的缩略图，并给缩略图添加指定后缀，多余部分会被切除.
     */
    public static function imageScaleCut(string $src, string $tail, int $maxWidth, int $maxHeight, int $cut = self::CUT_CENTER) : string
    {
        $dest = FileUtils::appentTail($src, $tail);

        if (!file_exists($dest)) {
            try {
                $info = self::getImageInfo($src);

                list($src_w, $src_h) = self::calcSrcSize($info['width'], $info['height'], $maxWidth, $maxHeight, false);

                switch ($cut) {
                    case self::CUT_TOP_LEFT:
                        $src_x = 0;
                        $src_y = 0;
                        break;
                    case self::CUT_TOP_RIGHT:
                        $src_x = $info['width'] - $src_w;
                        $src_y = 0;
                        break;
                    case self::CUT_BTM_LEFT:
                        $src_x = 0;
                        $src_y = $info['height'] - $src_h;
                        break;
                    case self::CUT_BTM_RIGHT:
                        $src_x = $info['width'] - $src_w;
                        $src_y = $info['height'] - $src_h;
                        break;
                    case self::CUT_CENTER:
                        $src_x = intval(($info['width'] - $src_w) / 2);
                        $src_y = intval(($info['height'] - $src_h) / 2);
                        break;
                }

                $obj = new Image();
                $obj->loadImage($src)
                    ->transform($maxWidth, $maxHeight, null, 0, 0, $src_x, $src_y, $maxWidth, $maxHeight, $src_w, $src_h)
                    ->save($dest)
                    ->destroy();
            } catch (\Exception $ex) {
                copy($src, $dest);
            }
        }

        return $dest;
    }

    /**
     * 给图片添加圆角，并添加指定后缀
     */
    public static function imageRoundCorner(string $src, string $tail, int $r, int $level = 2, $bgcolor = null) : string
    {
        $dest = FileUtils::appentTail($src, $tail);

        if (!file_exists($dest)) {
            try {
                $obj = new Image();
                $obj->loadImage($src)
                    ->roundCorner($r, $level, $bgcolor)
                    ->save($dest)
                    ->destroy();
            } catch (\Exception $ex) {
                copy($src, $dest);
            }
        }

        return $dest;
    }
}
