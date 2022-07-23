--TEST--
Utils test
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

use Minifw\Common\Utils;

var_dump(Utils::stripTags('<p>123</p><br /><img src="123123213" />'));

var_dump(Utils::isRich('<p>123</p><br /><img src="123123213" />'));
var_dump(Utils::isRich('<3131232144 4fsdf'));

var_dump(Utils::subText('<p>你好123456</p><br /><img src="123123213" />', 1, 3));
var_dump(Utils::subText('你好123456', 1, 3));

var_dump(Utils::subRich('<p>你好123456</p><p>你好123456</p>', 1, 9));

var_dump(Utils::strLen('你好123456'));
var_dump(Utils::strLen('123456'));

var_dump(Utils::isEmail('123456@qq.com'));
var_dump(Utils::isEmail('123456'));

var_dump(Utils::isPhone('13200000000'));
var_dump(Utils::isPhone('123456'));

var_dump(Utils::isTel('010-22225555'));
var_dump(Utils::isTel('123456'));

var_dump(Utils::isNum('-123.456'));
var_dump(Utils::isNum('123a'));

var_dump(Utils::isPositive('23444'));
var_dump(Utils::isPositive('-55433a'));

echo Utils::showDuration(30) . PHP_EOL;
echo Utils::showDuration(90) . PHP_EOL;
echo Utils::showDuration(3600) . PHP_EOL;
echo Utils::showDuration(3661) . PHP_EOL;
echo Utils::showSize(2) . PHP_EOL;
echo Utils::showSize(23) . PHP_EOL;
echo Utils::showSize(500) . PHP_EOL;
echo Utils::showSize(1024) . PHP_EOL;
echo Utils::showSize(10240) . PHP_EOL;
echo Utils::showSize(102400) . PHP_EOL;
echo Utils::showSize(1048576) . PHP_EOL;
echo Utils::showSize(10485760) . PHP_EOL;
echo Utils::showSize(104857600) . PHP_EOL;
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
2
23
500
1.00 K
10.0 K
100 K
1.00 M
10.0 M
100 M