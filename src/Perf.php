<?php

/*
 * Copyright (C) 2023 Yang Ming <yangming0116@163.com>.
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

class Perf
{
    private $data = [];

    public function __construct()
    {
    }

    public function reset() : void
    {
        $this->data = [];
    }

    public function start(string $name) : void
    {
        if (!isset($this->data[$name])) {
            $this->data[$name] = [
                'total' => 0,
                'last' => 0,
            ];
        }
        if ($this->data[$name]['last'] == 0) {
            $this->data[$name]['last'] = self::currentTime();
        }
    }

    public function stop(string $name) : void
    {
        if (!isset($this->data[$name])) {
            return;
        }
        if ($this->data[$name]['last'] == 0) {
            return;
        }
        $this->data[$name]['total'] += self::currentTime() - $this->data[$name]['last'];
        $this->data[$name]['last'] = 0;
    }

    public function get(?string $name = null) : int|array|null
    {
        if ($name === null) {
            return $this->data;
        }

        if (!isset($this->data[$name])) {
            return null;
        }

        return $this->data[$name]['total'];
    }

    public static function currentTime() : int
    {
        return hrtime(true);
    }

    public static function showTime(int $ns) : string
    {
        $units = [' ns', ' us', ' ms', ' s'];

        return Utils::showInUnits($ns, $units, 1000, 3);
    }
}
