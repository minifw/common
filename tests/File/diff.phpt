--TEST--
FileUtils file test
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

use Minifw\Common\File;

File::$defaultFsEncoding = 'gbk';
$root = dirname(dirname(__DIR__)) . '/tmp/diff_test';

$file = new File($root . '/test', '');
var_dump($file->getAppPath());
var_dump($file->getFsPath());
var_dump($file->getParent()->getAppPath());
$file->putContent('测试文件内容');
var_dump($file->getContent());
$file->readfile();
echo PHP_EOL;
echo json_encode($file->getParent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
$file->delete();
echo json_encode($file->getParent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL;

$file = new File('', $root . '/test');
var_dump($file->getAppPath());
var_dump($file->getFsPath());
var_dump($file->getParent()->getAppPath());
$file->putContent('测试文件内容');
var_dump($file->getContent());
$file->readfile();
echo PHP_EOL;

echo PHP_EOL;

$file = new File($root . '/测试', '');
var_dump($file->getAppPath());
var_dump(iconv('gbk', 'utf-8', $file->getFsPath()));
var_dump($file->getParent()->getAppPath());
$file->putContent('测试文件内容2');
var_dump($file->getContent());
$file->readfile();
echo PHP_EOL;
echo json_encode($file->getParent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
$new_file = $file->rename($root . '/dir/测试', '');
echo json_encode($new_file->getParent()->getParent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL;

$file = new File('', $root . '/' . iconv('utf-8', 'gbk', '测试'));
var_dump($file->getAppPath());
var_dump(iconv('gbk', 'utf-8', $file->getFsPath()));
var_dump($file->getParent()->getAppPath());
$file->putContent('测试文件内容2');
var_dump($file->getContent());
$file->readfile();
echo PHP_EOL;

echo PHP_EOL;

$diff2 = $file->getParent()->copyDir($root . '/../diff2_test');
echo json_encode($diff2->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
$diff2->clearDir(true);
echo json_encode($diff2->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

$file->getParent()->clearDir(true);
echo json_encode($file->getParent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
?>
--EXPECTF--
string(%d) "%s/tmp/diff_test/test"
string(%d) "%s/tmp/diff_test/test"
string(%d) "%s/tmp/diff_test"
string(18) "测试文件内容"
测试文件内容
[{"name":"test","dir":false}]
[]

string(%d) "%s/tmp/diff_test/test"
string(%d) "%s/tmp/diff_test/test"
string(%d) "%s/tmp/diff_test"
string(18) "测试文件内容"
测试文件内容

string(%d) "%s/tmp/diff_test/测试"
string(%d) "%s/tmp/diff_test/测试"
string(%d) "%s/tmp/diff_test"
string(19) "测试文件内容2"
测试文件内容2
[{"name":"test","dir":false},{"name":"测试","dir":false}]
[{"name":"dir","dir":true},{"name":"test","dir":false}]

string(%d) "%s/tmp/diff_test/测试"
string(%d) "%s/tmp/diff_test/测试"
string(%d) "%s/tmp/diff_test"
string(19) "测试文件内容2"
测试文件内容2

[{"name":"dir","dir":true},{"name":"test","dir":false},{"name":"测试","dir":false}]
false
false