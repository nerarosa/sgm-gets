<?php
require "getvideos.php";

$allowed_domains = ['http://sexygirlmedia.blogspot.com','https://sexygirlmedia.blogspot.com','http://www.sexygirlmedia.com'];

header("Content-type: application/json; charset=utf-8");
//header("Content-type: text/html; charset=utf-8");
//header('Access-Control-Allow-Origin: http://sexygirlmedia.blogspot.com', false);
//header('Access-Control-Allow-Origin: https://sexygirlmedia.blogspot.com', false);

if (in_array($_SERVER['HTTP_ORIGIN'], $allowed_domains)) {
	header('Access-Control-Allow-Origin : ' . $_SERVER['HTTP_ORIGIN']);
}

header("Access-Control-Allow-Credentials: true ");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST");

$videos = new getvideos();
$videos->run();
