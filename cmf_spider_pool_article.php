<?php
require_once ('cmf_db.php');
require_once(__DIR__ . '/cmf_tyc.php');
require_once(__DIR__ . '/guan_jian_zi.php');
use Beanbun\Lib\Db;

function removeBold($content)
{
    $content = strtolower($content);
    $content = preg_replace('/<[^>\x22]*?strong.*?>/', '', $content);
    $content = preg_replace('/font-size:\d{1,}(%|px|em|ch)/', '', $content);
    return $content;
}

function get_category($host)
{
    $method = "GET";
    $url = "http://" . $host . "/portal/rec/get_category";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, "gzip");
    $res = json_decode(curl_exec($curl), 1);
    if (!empty($res)){
        return array_column($res, 'name');
    }
}

//function randomInsert($insert, $txt, $times = 3)
//{
//
//    preg_match_all("/[\x01-\x7f]|[\xe0-\xef][\x80-\xbf]{2}/", $txt, $match);
//    $delay = array();
//    $add = 0;
//    foreach ($match[0] as $k => $v) {
//        if ($v == '<') $add = 1;
//        if ($add == 1) $delay[] = $k;
//        if ($v == '>') $add = 0;
//    }
//
//    $str_arr = $match[0];
//    $len = count($str_arr);
//
//    foreach ($insert as $k => $v) {
//        for ($i = 0; $i < $times; $i++) {
//            $insertk = insertK($len - 1, $delay);
//            $str_arr[$insertk] .= $insert[$k];
//        }
//    }
//    return join('', $str_arr);
//}

function insertK($count, &$delay)
{
    $insertk = rand(0, $count);
    if (in_array($insertk, $delay)) {
        $insertk = insertK($count, $delay);
    }
    $delay[] = $insertk;
    return $insertk;
}

Db::instance('spider')->insert("message", [
    "mes" => "推送服务启动",
    "time" => date("Y-m-d H:i:s"),
]);
echo "启动\n";
$res = Db::instance('spider')->select("news", [
    "id",
    "title",
    "content",
    "type",
    "pic"
], [
    "used" => 0,
    "ORDER" => ["id" => "DESC"],
]);
if (empty($res)) {
    echo "res为空，自动退出";
    exit();
}
$websites = Db::instance('spider')->select("websites", [
    "url",
    "news_id",
    "keyword",
    "update_article",
    "update_sitemap"
]);

$pool = Db::instance('spider')->select("spider_pool", [
    "keyword",
    "url",
]);
var_dump($pool);
exit();
$spinner = new cmf_tyc();
foreach ($websites as $row) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
    curl_setopt($curl, CURLOPT_POST, 1);
    $category = get_category($row['url']);
    if(!is_array($category)||empty($category)){
        echo "请替换RecController.php\n";
        continue;
    }
    foreach ($res as $key => $value) {
        if (!in_array($value['type'], $category)) {
            continue;
        }
        $content = $value['content'];
        $id = rand(0,count($pool));
        $post_data['articles'][] = array(
            "title" => $value['title'],
            "content" => $spinner->replace(removeBold($content))."<br/>来源:<a href='"+$pool[$id]['url']+"'>"+$pool[$id]['keyword']+"</a>",
//            "content" => randomInsert([$row['keyword']], $spinner->replace(removeBold($content)), 3),
            "keyword" => $value['title'] . $row['keyword'],
            "des" => $value['title'],
            "type" => $value['type'],
            "pic" => $value['pic']
        );
    }
    $url = "http://" . $row['url'] . "/portal/Rec/recArticle";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
    if (curl_exec($curl) != true) {
        Db::instance('spider')->insert("message", [
            "mes" => $row['url'] . "文章插入失败",
            "time" => date("Y-m-d H:i:s"),
        ]);
    }
    $post_data = null;
    $url = null;
    $category = null;
    curl_close($curl);
    echo "\n" . $row['url'] . "\n";
}
$websites = null;
foreach ($res as $v) {
    Db::instance('spider')->update("news", [
        "used" => 1,
    ], [
        "id" => $v['id']
    ]);
}
$res = null;
Db::instance('spider')->insert("message", [
    "mes" => "执行结束",
    "time" => date("Y-m-d H:i:s"),
]);