--TEST--
FileUtils rotate test
--FILE--
<?php

use Minifw\Common\FileUtils;

require __DIR__ . '/../bootstrap.php';

$dir = MFW_APP_ROOT . '/tmp/tests/rotate';

if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

file_put_contents($dir . '/test.log', "000\n111\n111");

for ($i = 1; $i <= 4; $i++) {
    file_put_contents($dir . '/test.' . $i . '.log', str_repeat($i, 3) . "\n222\n222");
}

$fp = fopen($dir . '/test.log', 'r+');
FileUtils::rotateFile($dir . '/test.log', '.log', 3, $fp);

echo file_get_contents($dir . '/test.log') . "\n";
echo "\n";

for ($i = 1; $i <= 4; $i++) {
    echo file_get_contents($dir . '/test.' . $i . '.log') . "\n";
    echo "\n";
}

?>
--EXPECTF--


000
111
111

111
222
222

222
222
222

444
222
222
