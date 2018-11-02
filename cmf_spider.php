<?php
require_once ('cmf_db.php');
require_once ('cmf_config.php');
require_once("guan_jian_zi.php");
$method = "GET";
$curl = curl_init();
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
//curl_setopt($curl, CURLOPT_FAILONERROR, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_ENCODING, "gzip");
//$i=0;
foreach ($keyword as $key => $keyword) {
//    URL为你的文章接口
    $url = API.$keyword."&pageToken=10";
    curl_setopt($curl, CURLOPT_URL, $url);
    $output = curl_exec($curl);
    $json_res = json_decode($output, 1)['data'];
    if(empty($json_res)){
        echo $keyword."无文章\n";
        continue;
    }
    foreach ($json_res as $key => $value) {
        if (isset($value['imageUrls'][0])) {
            $content = [
                "content" => $value['content'],
                "title" => $value['title'],
                "url" => $value['url'],
                "description" => $value['title'],
                "type" => $keyword,
                "pic" => $value['imageUrls'][0]
            ];
        } else {
            $content = [
                "content" => $value['content'],
                "title" => $value['title'],
                "url" => $value['url'],
                "description" => $value['title'],
                "type" => $keyword,
                "pic" => null
            ];
        }
        if (strlen($value['content']) <= 700) {
                continue;
            } else if (empty(Db::instance('spider')->select("news", ["id"], ["title" => $value['title'], "LIMIT" => 1])[0]))
                Db::instance('spider')->insert('news', $content);
    }
}
echo "爬取结束";
curl_close($curl);
exit();