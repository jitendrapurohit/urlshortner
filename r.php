<?php
require_once "ShortUrl.php";

if (empty($_GET["c"])) {
  throw new \Exception("No Short String supplied.");
}

$string = $_GET["c"];

$shortUrl = new ShortUrl();
try {
  $url = $shortUrl->shortStringToUrl($string);
  header("Location: " . $url);
}
catch (\Exception $e) {
  echo 'Oops! An error occured.' . $e->getMessage();
  exit;
}
