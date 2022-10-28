--TEST--
File map test
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';

use Minifw\Common\File;

$dir = __DIR__ . '/test/';
$file = new File($dir);

$ret = $file->map(function (File $file, string $prefix) {
    if ($file->isFile()) {
        return $file->getContent();
    }

    return null;
}, '', true);

sort($ret);
foreach ($ret as $value) {
    echo $value . PHP_EOL;
}

?>
--EXPECTF--
file1
file1

file2
file2

file3
file3

file4
file4