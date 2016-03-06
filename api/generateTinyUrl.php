<?php
require_once "../config.php";
require_once "../ShortUrl.php";

$parsedURL = parse_url($_SERVER['REQUEST_URI']);

$longUrl = explode('long_url=', $parsedURL['query']);
if (empty($longUrl[1])) {
  throw new \Exception("No URL was supplied.");
}
$shortUrl = new ShortUrl();
// encode the url to short string
$string = $shortUrl->urlToShortString($longUrl[1]);

if (!empty($string)) {
  //prefix the string with the shorturl defined
  $url = SHORTURL_PREFIX . $string;
  echo json_encode(array(
    'status' => 'success',
    'longUrl' => $_REQUEST["long_url"],
    'shortUrl' => $url,
  ), JSON_UNESCAPED_SLASHES);
}
else {
  echo json_encode(array(
    'status' => 'Failed', 
    'Error' => 'URL cannot be shortened.'
  ));
}
