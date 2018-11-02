<?php
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;

class RecController extends HomeBaseController
{
    public function get_category()
    {
        $categories = DB::name('portal_category')->where('status', 1)->field('id,name')->select();
        return json($categories);
    }

    public function recArticle()
    {
        $content = json_decode(file_get_contents('php://input'), true);
        if (!isset($content['articles'])) {
            echo "缺少参数";
            die();
        }
        Db::startTrans();
        $categories_old = DB::name('portal_category')->where('status', 1)->field('id,name')->select();
        $categories = [];
        foreach ($categories_old as $v) {
            $categories[$v['name']] = $v['id'];
        }
        $categories_old = null;
        foreach ($content['articles'] as $key => $value) {
            if (!isset($categories[$value['type']])) {
                echo $value['type'] . "\n";
                continue;
            }
            $category_id = $categories[$value['type']];
            $data_1 = [
                'post_title' => $value['title'],
                'post_keywords' => $value['keyword'],
                'post_content' => $value['content'],
                'post_excerpt' => $value['des'],
                'create_time' => time(),
                'update_time' => time(),
                'published_time' => time(),
                'user_id' => 1,
                'more' => "{\"thumbnail\":\"".$value['pic']."\",\"template\":\"\"}",
                'is_top' => 1,
                'recommended' => 1
            ];
            if ($value['pic'] != null) {
                $data_1['post_content'] = "<div align=\"center\"><img src=\"" . $value['pic'] . "\" style=\"width: 300px\"/></div><div>" . $value['content'] . "</div>";
                $data_1['thumbnail'] = $value['pic'];
            }
            //文章id
            $id = DB::name('portal_post')->insertGetId($data_1);
            $data_2 = [
                'post_id' => $id,
                'category_id' => $category_id
            ];
            $res = DB::name('portal_category_post')->insert($data_2);
            echo "插入成功<br/>\n";
            if ($id && $res) {
                continue;
            } else {
                Db::rollback();
                exit();
                echo "插入失败\n";
            }
        }
        Db::commit();
        $this->buildSitemap();
        echo "结束\n";
    }
//自动生成sitemap
    public function buildSitemap()
    {
        $url = "http://" . $_SERVER["SERVER_NAME"];
        $time = date("Y-m-d", time());
        $content = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
        xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">
        <!--首页-->
    <url>
        <loc>" . $url . "</loc>
        <priority>1.00</priority>
        <lastmod>" . $time . "</lastmod>
        <changefreq>always</changefreq>
    </url>";
        $categories = DB::name('portal_category')->where('status', 1)->field('id,name')->select();
        $content .= "<!--栏目-->";
        foreach ($categories as $value) {
            $content .= "<url>
        <loc>" . $url . "/portal/list/index/id/" . $value['id'] . ".html</loc>
        <priority>0.7</priority>
        <lastmod>" . $time . "</lastmod>
        <changefreq>daily</changefreq>
    </url>";
        }
        $content .= "<!--文章-->";
        $posts = DB::name('portal_post')->where('post_status', 1)->field('id,create_time')->limit(30)->select();
        foreach ($posts as $value) {
            $content .= "<url>
        <loc>" . $url . "/portal/article/index/id/" . $value['id'] . ".html</loc>
        <priority>0.5</priority>
        <lastmod>" . date("Y-m-d", $value['create_time']) . "</lastmod>
        <changefreq>weekly</changefreq>
    </url>";
        }
        $content .= "</urlset>";
        $file_url = dirname(dirname(dirname(dirname(__FILE__))));
        $sitemap = fopen($file_url . "/public/sitemap.xml", "w");
        fwrite($sitemap, $content);
        fclose($sitemap);
    }

}
