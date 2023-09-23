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

class Utils
{
    /**
     * 清除所有的html标记.
     */
    public static function stripTags(string $str) : string
    {
        return preg_replace('/\\<(\\/?[a-zA-Z0-9]+)(\\s+[^>]*)?\\/?\\>/i', '', $str);
    }

    /**
     * 判断字符串中是否具有html标记.
     */
    public static function isRich(string $str) : bool
    {
        return boolval(preg_match('/\\<(\\/?[a-zA-Z0-9]+)(\\s+[^>]*)?\\/?\\>/i', $str));
    }

    /**
     * 清除标记后截取指定长度的字符串.
     */
    public static function subText(string $str, int $begin, int $len, string $encoding = 'utf-8') : string
    {
        $str = self::stripTags($str);
        $str = preg_replace('/(\\s|&nbsp;)+/i', ' ', $str);

        return mb_substr($str, $begin, $len, $encoding);
    }

    /**
     * 截取指定长度的具有基本格式的字符串.
     */
    public static function subRich(string $str, int $begin, int $len, string $encoding = 'utf-8') : string
    {
        if (self::isRich($str)) {
            $str = preg_replace('/\\r/i', '', preg_replace('/\\n/i', '', $str));
            $str = preg_replace('/\\<br[^>]*\\>/i', "\n", preg_replace('/\\<p[^>]*\\>/i', "\n", $str));
            $str = self::stripTags($str);
        }
        $str = preg_replace('/^\\s*\\n/im', '', preg_replace('/(\\t| |　|&nbsp;)+/i', ' ', $str));
        $str = mb_substr($str, $begin, $len, $encoding);
        $str = preg_replace('/^([^\\r\\n]*)\\r?\\n?$/im', '<p>$1</p>', $str);

        return $str;
    }

    public static function strLen(string $str, string $encoding = 'utf-8') : int
    {
        return mb_strlen($str, $encoding);
    }

    public static function isEmail(string $str) : bool
    {
        if (!filter_var($str, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    public static function isPhone(string $str) : bool
    {
        if (!preg_match('/^1\\d{10}$/', $str)) {
            return false;
        }

        return true;
    }

    public static function isTel(string $str) : bool
    {
        if (!preg_match('/^\\d{3,4}-\\d{7,8}(-\\d{1,6})?$/', $str)) {
            return false;
        }

        return true;
    }

    public static function isNum(string $str) : bool
    {
        if (!preg_match('/^-?\\d+(\\.\\d+)?$/', $str)) {
            return false;
        }

        return true;
    }

    public static function isPositive(string $str) : bool
    {
        if (!preg_match('/^\\d+(\\.\\d+)?$/', $str)) {
            return false;
        }

        return true;
    }

    /**
     * 按照 时:分:秒 的格式显示传入的秒数.
     */
    public static function showDuration(int $duration) : string
    {
        $hour = intval($duration / 3600);
        if ($hour < 10) {
            $hour = '0' . $hour;
        }
        $duration = $duration % 3600;
        $min = intval($duration / 60);
        if ($min < 10) {
            $min = '0' . $min;
        }
        $sec = $duration % 60;
        if ($sec < 10) {
            $sec = '0' . $sec;
        }

        return $hour . ':' . $min . ':' . $sec;
    }

    /**
     * 将传入的文件尺寸显示成合适的计量单位.
     */
    public static function showSize(int $size) : string
    {
        $units = ['', ' K', ' M', ' G', ' T', ' P', ' E', ' Z', ' Y'];

        return self::showInUnits($size, $units, 1024, 3);
    }

    public static function showInUnits(int $number, array $units, int $base, $precision = 3) : string
    {
        $cur_unit = 0;
        $count = count($units);

        while ($number >= $base && $cur_unit < $count - 1) {
            $cur_unit++;
            $number = bcdiv($number, $base, $precision - 1);
        }

        if ($cur_unit != 0) {
            for ($i = $precision - 1;$i > 0; $i--) {
                if ($number >= pow(10, $i)) {
                    $number = bcdiv($number, 1, 2 - $i);
                    break;
                }
            }
        }

        return $number . $units[$cur_unit];
    }
}
