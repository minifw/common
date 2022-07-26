--TEST--
Utils test
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';

use Minifw\Common\FileUtils;
use Minifw\Common\ImageUtils;

$path = dirname(dirname(__DIR__)) . '/tmp/image';

$img_list = [
    '001.jpg',
    '002.png',
    '003.gif',
];

$file = new \Minifw\Common\File(__DIR__ . '/image');
$file->copyDir($path);

foreach ($img_list as $img) {
    ImageUtils::imageScale($path . '/' . $img, '_0_0', 0, 0);

    $new_path = FileUtils::appentTail($path . '/' . $img, '_0_0');
    $new_info = getimagesize($new_path);
    echo json_encode($new_info, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    ImageUtils::imageScale($path . '/' . $img, '_100_0', 100, 0);

    $new_path = FileUtils::appentTail($path . '/' . $img, '_100_0');
    $new_info = getimagesize($new_path);
    echo json_encode($new_info, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    ImageUtils::imageScale($path . '/' . $img, '_0_100', 0, 100);

    $new_path = FileUtils::appentTail($path . '/' . $img, '_0_100');
    $new_info = getimagesize($new_path);
    echo json_encode($new_info, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    ImageUtils::imageScale($path . '/' . $img, '_100_100', 100, 100);

    $new_path = FileUtils::appentTail($path . '/' . $img, '_100_100');
    $new_info = getimagesize($new_path);
    echo json_encode($new_info, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

$file = new \Minifw\Common\File($path);
$file->clearDir(true);
?>
--EXPECTF--
{"0":960,"1":646,"2":2,"3":"width=\"960\" height=\"646\"","bits":8,"channels":3,"mime":"image\/jpeg"}
{"0":100,"1":67,"2":2,"3":"width=\"100\" height=\"67\"","bits":8,"channels":3,"mime":"image\/jpeg"}
{"0":148,"1":100,"2":2,"3":"width=\"148\" height=\"100\"","bits":8,"channels":3,"mime":"image\/jpeg"}
{"0":100,"1":67,"2":2,"3":"width=\"100\" height=\"67\"","bits":8,"channels":3,"mime":"image\/jpeg"}
{"0":830,"1":720,"2":3,"3":"width=\"830\" height=\"720\"","bits":8,"mime":"image\/png"}
{"0":100,"1":86,"2":3,"3":"width=\"100\" height=\"86\"","bits":8,"mime":"image\/png"}
{"0":115,"1":100,"2":3,"3":"width=\"115\" height=\"100\"","bits":8,"mime":"image\/png"}
{"0":100,"1":86,"2":3,"3":"width=\"100\" height=\"86\"","bits":8,"mime":"image\/png"}
{"0":588,"1":720,"2":1,"3":"width=\"588\" height=\"720\"","bits":8,"channels":3,"mime":"image\/gif"}
{"0":100,"1":122,"2":1,"3":"width=\"100\" height=\"122\"","bits":7,"channels":3,"mime":"image\/gif"}
{"0":81,"1":100,"2":1,"3":"width=\"81\" height=\"100\"","bits":7,"channels":3,"mime":"image\/gif"}
{"0":81,"1":100,"2":1,"3":"width=\"81\" height=\"100\"","bits":7,"channels":3,"mime":"image\/gif"}