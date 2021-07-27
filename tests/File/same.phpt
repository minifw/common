--TEST--
FileUtils file test
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

use Minifw\Common\File;

File::$default_fs_encoding = 'utf-8';
$root = dirname(dirname(__DIR__)) . '/tmp/same_test';

$file = new File($root . '/test', '');
var_dump($file->get_app_path());
var_dump($file->get_fs_path());
var_dump($file->get_parent()->get_app_path());
$file->put_content('测试文件内容');
var_dump($file->get_content());
$file->readfile();
echo PHP_EOL;
echo json_encode($file->get_parent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
$file->delete();
echo json_encode($file->get_parent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL;

$file = new File('', $root . '/test');
var_dump($file->get_app_path());
var_dump($file->get_fs_path());
var_dump($file->get_parent()->get_app_path());
$file->put_content('测试文件内容');
var_dump($file->get_content());
$file->readfile();
echo PHP_EOL;

echo PHP_EOL;

$file = new File($root . '/测试', '');
var_dump($file->get_app_path());
var_dump($file->get_fs_path());
var_dump($file->get_parent()->get_app_path());
$file->put_content('测试文件内容2');
var_dump($file->get_content());
$file->readfile();
echo PHP_EOL;
echo json_encode($file->get_parent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
$file->rename($root . '/dir/测试', '');
echo json_encode($file->get_parent()->get_parent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL;

$file = new File('', $root . '/测试');
var_dump($file->get_app_path());
var_dump($file->get_fs_path());
var_dump($file->get_parent()->get_app_path());
$file->put_content('测试文件内容2');
var_dump($file->get_content());
$file->readfile();
echo PHP_EOL;

echo PHP_EOL;

$diff2 = $file->get_parent()->copy_dir($root . '/../diff2_test');
echo json_encode($diff2->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
$diff2->clear_dir(true);
echo json_encode($diff2->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

$file->get_parent()->clear_dir(true);
echo json_encode($file->get_parent()->ls(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
?>
--EXPECTF--
string(%d) "%s/tmp/same_test/test"
string(%d) "%s/tmp/same_test/test"
string(%d) "%s/tmp/same_test"
string(18) "测试文件内容"
测试文件内容
[{"name":"test","dir":false}]
[]

string(%d) "%s/tmp/same_test/test"
string(%d) "%s/tmp/same_test/test"
string(%d) "%s/tmp/same_test"
string(18) "测试文件内容"
测试文件内容

string(%d) "%s/tmp/same_test/测试"
string(%d) "%s/tmp/same_test/测试"
string(%d) "%s/tmp/same_test"
string(19) "测试文件内容2"
测试文件内容2
[{"name":"test","dir":false},{"name":"测试","dir":false}]
[{"name":"dir","dir":true},{"name":"test","dir":false}]

string(%d) "%s/tmp/same_test/测试"
string(%d) "%s/tmp/same_test/测试"
string(%d) "%s/tmp/same_test"
string(19) "测试文件内容2"
测试文件内容2

[{"name":"dir","dir":true},{"name":"test","dir":false},{"name":"测试","dir":false}]
false
false