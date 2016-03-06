<?php
require_once "ShortUrl.php";

if (empty($_POST["url"])) {
  throw new \Exception("No URL was supplied.");
}

$shortUrl = new ShortUrl();
// encode the url to short string
$string = $shortUrl->urlToShortString($_POST["url"]);

//prefix the string with the shorturl defined
$url = SHORTURL_PREFIX . $string;

echo json_encode(array('shortUrl' => $url));