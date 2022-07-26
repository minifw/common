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
    ImageUtils::imageRoundCorner($path . '/' . $img, '_r2', 200, 2);

    $new_path = FileUtils::appentTail($path . '/' . $img, '_r2');
    $new_info = getimagesize($new_path);
    echo json_encode($new_info, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    ImageUtils::imageRoundCorner($path . '/' . $img, '_r0', 200, 0);

    $new_path = FileUtils::appentTail($path . '/' . $img, '_r0');
    $new_info = getimagesize($new_path);
    echo json_encode($new_info, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

$file = new \Minifw\Common\File($path);
$file->clearDir(true);
?>
--EXPECTF--
{"0":960,"1":646,"2":2,"3":"width=\"960\" height=\"646\"","bits":8,"channels":3,"mime":"image\/jpeg"}
{"0":960,"1":646,"2":2,"3":"width=\"960\" height=\"646\"","bits":8,"channels":3,"mime":"image\/jpeg"}
{"0":830,"1":720,"2":3,"3":"width=\"830\" height=\"720\"","bits":8,"mime":"image\/png"}
{"0":830,"1":720,"2":3,"3":"width=\"830\" height=\"720\"","bits":8,"mime":"image\/png"}
{"0":588,"1":720,"2":1,"3":"width=\"588\" height=\"720\"","bits":8,"channels":3,"mime":"image\/gif"}
{"0":588,"1":720,"2":1,"3":"width=\"588\" height=\"720\"","bits":8,"channels":3,"mime":"image\/gif"}
