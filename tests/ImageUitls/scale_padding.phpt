--TEST--
Utils test
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

use Minifw\Common\ImageUtils;
use Minifw\Common\FileUtils;

$path = dirname(dirname(__DIR__)) . '/tmp/image';

$img_list = [
    '001.jpg',
    '002.png',
    '003.gif',
];

$file = new \Minifw\Common\File(__DIR__ . '/image');
$file->copyDir($path);

foreach ($img_list as $img) {
    ImageUtils::imageScalePadding($path . '/' . $img, '_100_100_padding', 100, 100);

    $new_path = FileUtils::appentTail($path . '/' . $img, '_100_100_padding');
    $new_info = getimagesize($new_path);
    echo json_encode($new_info, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

$file = new \Minifw\Common\File($path);
$file->clearDir(true);
?>
--EXPECTF--
{"0":100,"1":100,"2":2,"3":"width=\"100\" height=\"100\"","bits":8,"channels":3,"mime":"image\/jpeg"}
{"0":100,"1":100,"2":3,"3":"width=\"100\" height=\"100\"","bits":8,"mime":"image\/png"}
{"0":100,"1":100,"2":1,"3":"width=\"100\" height=\"100\"","bits":7,"channels":3,"mime":"image\/gif"}
