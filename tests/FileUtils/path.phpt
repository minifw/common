--TEST--
FileUtils path test
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

use Minifw\Common\FileUtils;

var_dump(FileUtils::pathJoin('123'));

var_dump(FileUtils::pathJoin('123', '456'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5', '/tmp/123'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5', '/', '123'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5', 'd:/tmp/123'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5', 'd:', '123'));

var_dump(FileUtils::pathJoin('1/2/3', '../../../123'));

echo "------------------\n";

var_dump(FileUtils::dirname('./123/456'));
var_dump(FileUtils::dirname('/123/456'));
var_dump(FileUtils::dirname('/'));
var_dump(FileUtils::dirname('.'));
var_dump(FileUtils::dirname(''));

echo "------------------\n";

var_dump(FileUtils::basename('./123/456'));
var_dump(FileUtils::basename('/123/456'));
var_dump(FileUtils::basename('/'));
var_dump(FileUtils::basename('.'));
var_dump(FileUtils::basename(''));

echo "------------------\n";

var_dump(FileUtils::filename('./123/456.jpg'));
var_dump(FileUtils::filename('/123/456.jpg'));
var_dump(FileUtils::filename('/'));
var_dump(FileUtils::filename('.jpg'));
var_dump(FileUtils::filename(''));

echo "------------------\n";

$root = dirname(dirname(__DIR__)) . '/tmp/';

var_dump(FileUtils::appentTail('./123/456.jpg', '_gg'));
var_dump(FileUtils::appentTail('./123/456', '_gg'));
var_dump(FileUtils::mkname($root, '.jpg'));
?>
--EXPECTF--
string(3) "123"
string(7) "123/456"
string(3) "1/5"
string(8) "/tmp/123"
string(4) "/123"
string(10) "d:/tmp/123"
string(6) "d:/123"
NULL
------------------
string(5) "./123"
string(4) "/123"
string(1) "/"
string(0) ""
string(0) ""
------------------
string(3) "456"
string(3) "456"
string(0) ""
string(1) "."
string(0) ""
------------------
string(9) "./123/456"
string(8) "/123/456"
string(1) "/"
string(0) ""
string(0) ""
------------------
string(16) "./123/456_gg.jpg"
string(12) "./123/456_gg"
string(34) "%s"