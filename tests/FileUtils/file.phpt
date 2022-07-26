--TEST--
FileUtils file test
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';

use Minifw\Common\FileUtils;

var_dump(FileUtils::pathJoin('123'));

var_dump(FileUtils::pathJoin('123', '456'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5', '/tmp/123'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5', '/', '123'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5', 'd:/tmp/123'));

var_dump(FileUtils::pathJoin('1/2/3', '../../5', 'd:', '123'));

var_dump(FileUtils::pathJoin('1/2/3', '../../../123'));
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