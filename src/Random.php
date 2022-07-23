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

class Random
{
    public static string $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
    public static string $alphas = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    public static string $digits = '0123456789';
    protected static int $charLen;
    protected static int $alphaLen;
    protected static int $digitLen;

    public static function genInt(int $min, int $max) : int
    {
        if (function_exists('random_int')) {
            return random_int($min, $max);
        }

        return mt_rand($min, $max);
    }

    public static function genByte(int $len, bool $bin = false) : string
    {
        $byte = null;
        if (function_exists('random_bytes')) {
            $byte = random_bytes($len);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $byte = openssl_random_pseudo_bytes($len);
        }
        if ($bin) {
            return $byte;
        } else {
            return bin2hex($byte);
        }
    }

    public static function genKey(int $len) : string
    {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::$chars[self::genInt(0, self::$charLen - 1)];
        }

        return $key;
    }

    public static function genStr(int $len) : string
    {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::$alphas[self::genInt(0, self::$alphaLen - 1)];
        }

        return $key;
    }

    public static function genNum(int $len) : string
    {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::$digits[self::genInt(0, self::$digitLen - 1)];
        }

        return $key;
    }

    public static function init() : void
    {
        self::$charLen = strlen(self::$chars);
        self::$alphaLen = strlen(self::$alphas);
        self::$digitLen = strlen(self::$digits);
    }
}

Random::init();
