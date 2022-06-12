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

/**
 * 自定义的异常类，只在本程序内抛出和捕获
 */
class Exception extends \Exception
{
    /**
     * @var mixed
     */
    protected $extraMsg;

    /**
     *
     * @param mixed $message 错误消息，如果是数组或对象，则会使用print_r转换
     * @param int $code 错误码
     * @param \Exception $previous 触发者
     * @param mixed $extraMsg 额外信息
     */
    public function __construct($message = "", $code = -1, \Exception $previous = null, $extraMsg = null)
    {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        parent::__construct($message, intval($code), $previous);

        $this->extraMsg = $extraMsg;
    }

    /**
     * @return mixed
     */
    public function getExtraMsg()
    {
        return $this->extraMsg;
    }
}
