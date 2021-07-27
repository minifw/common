--TEST--
Random test
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

use Minifw\Common\Random;

var_dump(Random::gen_int(1, 999));

var_dump(bin2hex(Random::gen_byte(8, true)));
var_dump(Random::gen_byte(8, false));

var_dump(Random::gen_key(12));

var_dump(Random::gen_str(12));

var_dump(Random::gen_num(12));
?>
--EXPECTREGEX--
int\([0-9]{1,3}\)
string\(16\) "[0-9a-z]{16}"
string\(16\) "[0-9a-z]{16}"
string\(12\) "(.){12}"
string\(12\) "[a-zA-Z0-9]{12}"
string\(12\) "[0-9]{12}"