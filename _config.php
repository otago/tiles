<?php


$base_url = str_replace(BASE_PATH, "", dirname(__FILE__));
$base_url_unix = str_replace('\\','/',$base_url);
$base_url_clean = ltrim($base_url_unix, '/');

define("TILEWORKINGFOLDER", $base_url_clean);

