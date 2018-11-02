<?php
$url = dirname(__FILE__);
$folders = scandir($url);
$id = 1;
foreach ($folders as $key => $folder) {
    $file_name_arr = explode(".", $folder);
    if ($file_name_arr[0] == 'www' && is_dir($url . '/' . $folder)) {
        copy($url . "/" . "index.html", $url . "/" . $folder . "/index.html");
        copy($url . "/" . "index.htm", $url . "/" . $folder . "/templets/default/index.htm");
        copy($url . "/" . "list_article.htm", $url . "/" . $folder . "/templets/default/list_article.htm");
        copy($url . "/" . "article_article.htm", $url . "/" . $folder . "/templets/default/article_article.htm");
        copy($url . "/" . "114.gif", $url . "/" . $folder . "/templets/default/web03/114.gif");
        echo $id . " " . $file_name_arr[1] . " ok\n";
        $id++;
    }
}