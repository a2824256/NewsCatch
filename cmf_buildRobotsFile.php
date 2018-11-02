<?php
require_once ('cmf_db.php');

$tem = Db::instance('spider')->select("robots", [
    "content"
], [
    "id" => 1,
])[0];

$res = Db::instance('spider')->select("websites", [
    "url"
], [
    "update_robot" => 1,
]);
echo "自动更新文章站点\n";
var_dump($res);
echo "\n";
//wwwroot目录
$dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
//扫描文件夹
$files = scandir($dir);
$count = 0;
$state = false;
foreach ($files as $key => $file) {
    $file_name_arr = explode(".", $file);
    if ($file_name_arr[0] == 'www') {
        foreach ($res as $key => $value) {
            if ($file == $value["url"]) {
                echo $value["url"] . "\n";
                $state = true;
            }
        }
        if ($state) {
            $url = $dir . '/' . $file . '/public/' . ROBOT_FILE;
            $obj_file = fopen($url, "w");
            fwrite($obj_file, str_replace("xxxx", $file, $tem["content"]));
            fclose($obj_file);
            $count++;
            echo $count . " " . $url . "写入成功\n";
        }
        $state = false;
    }
}