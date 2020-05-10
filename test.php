<?php

include 'vendor/autoload.php';
include "MySQLDB.php";

use Symfony\Component\DomCrawler\Crawler;

class Parse
{
    ////64391
    private $DB = null;

    function __construct()
    {
        $this->DB = new MySQLDB();

        $prods = $this->DB->query("SELECT products.id,products.url FROM products,products_prices WHERE products_prices.price > 499 AND products.id = products_prices.product_id ")->doQuery();
        $prods_count = count($prods);

        foreach ($prods as $k => $prod) {
            if($k < 700){
                continue;
            }
            $price = $this->getProductPrice($prod['url']);
            $arr = array(
                "price" => $price,
                "product_id" => $prod['id']
            );
            $this->DB->insert($arr,"products_prices");
            echo $prods_count."\t".$k."\n";
        }
    }

    function getProductPrice($url)
    {
        $html = $this->getSslPage($url);
        $Crawler = new Crawler($html);
        $price_node = $Crawler->filter(".sel-product-tile-price");
        if ($price_node->count() > 0) {
            $price = $price_node->text();
            return $this->moneyFilter($price);
        } else {
            return 0;
        }
    }

    function moneyFilter($str)
    {
        $arr = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $out = "";
        for ($i = 0; $i < strlen($str); $i++) {
            if (in_array($str[$i], $arr)) {
                $out .= $str[$i];
            }
        }
        return intval($out);
    }

    function getSslPage($url)
    {
        $header = array('Accept-Language: en-us,en;q=0.7,bn-bn;q=0.3', 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5');
        $ch = curl_init();//start curl
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);            //curl Targeted URL
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.8 [en] (Windows NT 5.1; U)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        $httpResponse = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $result;
    }
}

$p = new Parse();