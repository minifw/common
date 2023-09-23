--TEST--
Utils test
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';

use Minifw\Common\Perf;

$perf = new Perf();
$perf->start('app1');
$perf->start('app2');
usleep(300);
$perf->stop('app1');
usleep(100);
$perf->stop('app2');

var_dump($perf->get('app1'));
var_dump($perf->get('app2'));
var_dump($perf->get());

var_dump(Perf::showTime($perf->get('app1')));
var_dump(Perf::showTime($perf->get('app2')));
?>
--EXPECTF--
int(%d)
int(%d)
array(2) {
  ["app1"]=>
  array(2) {
    ["total"]=>
    int(%d)
    ["last"]=>
    int(0)
  }
  ["app2"]=>
  array(2) {
    ["total"]=>
    int(%d)
    ["last"]=>
    int(0)
  }
}
string(%d) "%d %s"
string(%d) "%d %s"
