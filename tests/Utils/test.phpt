--TEST--
Utils test
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

use Minifw\Common\Utils;

var_dump(Utils::strip_tags('<p>123</p><br /><img src="123123213" />'));

var_dump(Utils::is_rich('<p>123</p><br /><img src="123123213" />'));
var_dump(Utils::is_rich('<3131232144 4fsdf'));

var_dump(Utils::sub_text('<p>你好123456</p><br /><img src="123123213" />', 1, 3));
var_dump(Utils::sub_text('你好123456', 1, 3));

var_dump(Utils::sub_rich('<p>你好123456</p><p>你好123456</p>', 1, 9));

var_dump(Utils::str_len('你好123456'));
var_dump(Utils::str_len('123456'));

var_dump(Utils::is_email('123456@qq.com'));
var_dump(Utils::is_email('123456'));

var_dump(Utils::is_phone('13200000000'));
var_dump(Utils::is_phone('123456'));

var_dump(Utils::is_tel('010-22225555'));
var_dump(Utils::is_tel('123456'));

var_dump(Utils::is_num('-123.456'));
var_dump(Utils::is_num('123a'));

var_dump(Utils::is_positive('23444'));
var_dump(Utils::is_positive('-55433a'));



echo Utils::show_duration(30) . PHP_EOL;
echo Utils::show_duration(90) . PHP_EOL;
echo Utils::show_duration(3600) . PHP_EOL;
echo Utils::show_duration(3661) . PHP_EOL;
echo Utils::show_size(500) . PHP_EOL;
echo Utils::show_size(1024) . PHP_EOL;
echo Utils::show_size(10240) . PHP_EOL;
echo Utils::show_size(102400) . PHP_EOL;
echo Utils::show_size(1048576) . PHP_EOL;
echo Utils::show_size(10485760) . PHP_EOL;
echo Utils::show_size(104857600) . PHP_EOL;
?>
--EXPECTF--
string(3) "123"
bool(true)
bool(false)
string(5) "好12"
string(5) "好12"
string(27) "<p>好123456</p>
<p>你</p>"
int(8)
int(6)
bool(true)
bool(false)
bool(true)
bool(false)
bool(true)
bool(false)
bool(true)
bool(false)
bool(true)
bool(false)
00:00:30
00:01:30
01:00:00
01:01:01
500
1.00 K
10.0 K
100 K
1.00 M
10.0 M
100 M