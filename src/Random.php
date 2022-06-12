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
    /**
     * @var string
     */
    public static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
    /**
     * @var string
     */
    public static $alphas = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    /**
     * @var string
     */
    public static $digits = '0123456789';
    /**
     * @var mixed
     */
    protected static $charLen;
    /**
     * @var mixed
     */
    protected static $alphaLen;
    /**
     * @var mixed
     */
    protected static $digitLen;

    /**
     * @param $min
     * @param $max
     * @return int
     */
    public static function genInt($min, $max)
    {
        if (function_exists('random_int')) {
            return random_int($min, $max);
        }

        return mt_rand($min, $max);
    }

    /**
     * @param $len
     * @param $bin
     * @return mixed
     */
    public static function genByte($len, $bin = false)
    {
        $byte = null;
        if (function_exists('random_bytes')) {
            $byte = random_bytes($len);
        }
        if (function_exists('mcrypt_create_iv')) {
            $byte = mcrypt_create_iv($len, MCRYPT_DEV_URANDOM);
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $byte = openssl_random_pseudo_bytes($len);
        }
        if ($bin) {
            return $byte;
        } else {
            return bin2hex($byte);
        }
    }

    /**
     * @param $len
     * @return string
     */
    public static function genKey($len)
    {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::$chars[self::genInt(0, self::$charLen - 1)];
        }

        return $key;
    }

    /**
     * @param $len
     * @return string
     */
    public static function genStr($len)
    {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::$alphas[self::genInt(0, self::$alphaLen - 1)];
        }

        return $key;
    }

    /**
     * @param $len
     * @return string
     */
    public static function genNum($len)
    {
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= self::$digits[self::genInt(0, self::$digitLen - 1)];
        }

        return $key;
    }

    public static function init()
    {
        self::$charLen = strlen(self::$chars);
        self::$alphaLen = strlen(self::$alphas);
        self::$digitLen = strlen(self::$digits);
    }
}

Random::init();
