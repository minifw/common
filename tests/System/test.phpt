--TEST--
System test
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';

use Minifw\Common\System;

System::loadConfig(__DIR__ . '/config.php');
var_dump(System::getConfig('path', 'app_root'));
var_dump(System::getConfig('path', 'caroot'));
var_dump(System::getConfig('mysql'));

System::init(__DIR__ . '/config.php');

var_dump(DATA_DIR);
var_dump(TMP_DIR);

?>
--EXPECTF--
string(8) "test/123"
string(16) "/data/caroot.pem"
array(5) {
  ["host"]=>
  string(9) "localhost"
  ["username"]=>
  string(4) "user"
  ["password"]=>
  string(3) "pwd"
  ["dbname"]=>
  string(6) "dbname"
  ["encoding"]=>
  string(7) "utf8mb4"
}
string(37) "%s/data"
string(36) "%s/tmp"