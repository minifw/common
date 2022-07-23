--TEST--
Exception test
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

use Minifw\Common\Exception;

$ex = new Exception('msg', 1, null, ['type' => 'extra']);
var_dump($ex->getCode());
var_dump($ex->getMessage());
var_dump($ex->getExtraMsg());
?>
--EXPECTF--
int(1)
string(3) "msg"
array(1) {
  ["type"]=>
  string(5) "extra"
}
