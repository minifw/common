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

use Minifw\Common\Image;
use Minifw\Common\Exception;
use Minifw\Common\FileUtils;

class ImageUtils {

    const CUT_TOP_LEFT = 1;
    const CUT_TOP_RIGHT = 2;
    const CUT_BTM_LEFT = 3;
    const CUT_BTM_RIGHT = 4;
    const CUT_CENTER = 5;

    /**
     *
     * @param string $full
     * @param int $format
     * @return \Minifw\Common\Image
     */
    public static function load_image_obj($full, $format) {
        switch ($format) {
            case Image::FORMAT_GIF:
                $image_obj = imagecreatefromgif($full);
                break;
            case Image::FORMAT_JPG:
                $image_obj = imagecreatefromjpeg($full);
                break;
            case Image::FORMAT_PNG:
                $image_obj = imagecreatefrompng($full);
                imagealphablending($image_obj, false);
                imagesavealpha($image_obj, true);
                break;
        }
        return $image_obj;
    }

    public static function get_image_info($full) {
        if (!file_exists($full)) {
            throw new Exception('文件不存在');
        }
        $info = getimagesize($full);

        $hash = [
            1 => Image::FORMAT_GIF,
            2 => Image::FORMAT_JPG,
            3 => Image::FORMAT_PNG,
        ];

        if (!isset($hash[$info[2]])) {
            throw new Exception('不支持的文件格式');
        }

        $ret = [
            'width' => $info[0],
            'height' => $info[1],
            'format' => $hash[$info[2]],
        ];
        return $ret;
    }

    /**
     *
     * @param string $format
     * @param int $width
     * @param int $height
     * @param int $bgcolor
     * @return \Minifw\Common\Image
     * @throws Exception
     */
    public static function get_new_image($format, $width, $height, $bgcolor = null) {
        switch ($format) {
            case Image::FORMAT_GIF:
                $image_obj = imagecreate($width, $height);
                if ($bgcolor == null) {
                    $bgcolor = imagecolorallocate($image_obj, 0, 0, 0);
                }
                imagecolortransparent($image_obj, $bgcolor);
                break;
            case Image::FORMAT_JPG:
                $image_obj = imagecreatetruecolor($width, $height);
                if ($bgcolor == null) {
                    $bgcolor = imagecolorallocate($image_obj, 255, 255, 255);
                }
                imagefill($image_obj, 0, 0, $bgcolor);
                break;
            case Image::FORMAT_PNG:
                $image_obj = imagecreatetruecolor($width, $height);
                imagealphablending($image_obj, false);
                imagesavealpha($image_obj, true);
                if ($bgcolor == null) {
                    $bgcolor = imagecolorallocatealpha($image_obj, 0, 0, 0, 127);
                }
                imagefill($image_obj, 0, 0, $bgcolor);
                break;
            default :
                throw new Exception('不支持的图片格式');
        }
        return $image_obj;
    }

    public static function calc_dst_size($width, $height, $max_width, $max_height) {
        if ($max_width <= 0 && $max_height <= 0) {
            return [$width, $height];
        }
        elseif ($max_width <= 0) {
            $width = intval($max_height * $width / $height);
            $height = $max_height;
        }
        elseif ($max_height <= 0) {
            $height = intval($max_width * $height / $width);
            $width = $max_width;
        }
        else {
            if ($width * $max_height >= $height * $max_width) {
                $height = intval($max_width * $height / $width);
                $width = $max_width;
            }
            else {
                $width = intval($max_height * $width / $height);
                $height = $max_height;
            }
        }

        return [$width, $height];
    }

    public static function calc_src_size($width, $height, $max_width, $max_height) {
        if ($max_width <= 0 || $max_height <= 0) {
            return [$width, $height];
        }
        if ($width * $max_height <= $height * $max_width) {
            $height = intval($max_height * $width / $max_width);
        }
        else {
            $width = intval($max_width * $height / $max_height);
        }
        return [$width, $height];
    }

    public static function image_scale($src, $tail = '', $max_width = 0, $max_height = 0) {
        $dest = FileUtils::appent_tail($src, $tail);

        if (!file_exists($dest)) {
            try {
                $info = self::get_image_info($src);

                list($dst_w, $dst_h) = self::calc_dst_size($info['width'], $info['height'], $max_width, $max_height);

                $obj = new Image();
                $obj->load_image($src)
                        ->transform($dst_w, $dst_h, NULL, 0, 0, 0, 0, $dst_w, $dst_h, $info['width'], $info['height'])
                        ->save($dest)
                        ->destroy();
            }
            catch (\Exception $ex) {
                copy($src, $dest);
            }
        }

        return $dest;
    }

    public static function image_scale_padding($src, $tail, $max_width, $max_height, $bgcolor = null) {
        $dest = FileUtils::appent_tail($src, $tail);

        if (!file_exists($dest)) {
            try {
                $info = self::get_image_info($src);

                list($dst_w, $dst_h) = self::calc_dst_size($info['width'], $info['height'], $max_width, $max_height);

                if ($dst_w < $max_width) {
                    $dst_x = intval(($max_width - $dst_w) / 2);
                }
                else {
                    $dst_x = 0;
                }

                if ($dst_h < $max_height) {
                    $dst_y = intval(($max_height - $dst_h) / 2);
                }
                else {
                    $dst_y = 0;
                }

                $obj = new Image();
                $obj->load_image($src)
                        ->transform($max_width, $max_height, $bgcolor, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $info['width'], $info['height'])
                        ->save($dest)
                        ->destroy();
            }
            catch (\Exception $ex) {
                copy($src, $dest);
            }
        }

        return $dest;
    }

    public static function image_scale_cut($src, $tail, $max_width, $max_height, $cut = self::CUT_CENTER) {
        $dest = FileUtils::appent_tail($src, $tail);

        if (!file_exists($dest)) {
            try {
                $info = self::get_image_info($src);

                list($src_w, $src_h) = self::calc_src_size($info['width'], $info['height'], $max_width, $max_height, false);

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
                $obj->load_image($src)
                        ->transform($max_width, $max_height, NULL, 0, 0, $src_x, $src_y, $max_width, $max_height, $src_w, $src_h)
                        ->save($dest)
                        ->destroy();
            }
            catch (\Exception $ex) {
                copy($src, $dest);
            }
        }

        return $dest;
    }

    public static function image_round_corner($src, $tail, $r, $level = 2, $bgcolor = null) {
        $dest = FileUtils::appent_tail($src, $tail);

        if (!file_exists($dest)) {
            try {
                $obj = new Image();
                $obj->load_image($src)
                        ->round_corner($r, $level, $bgcolor)
                        ->save($dest)
                        ->destroy();
            }
            catch (\Exception $ex) {
                copy($src, $dest);
            }
        }

        return $dest;
    }

}
