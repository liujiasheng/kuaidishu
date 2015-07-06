<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-10-22
 * Time: 上午10:37
 */
$url = 'http://www.kw8888.com/Enterprise/Detail_13.htm';
$html = file_get_contents( $url );

$dom = new DOMDocument();
$dom->loadHTML($html);

error_log($html);