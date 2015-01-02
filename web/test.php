<?php
$headers = getallheaders();
$headers['REMOTE_ADDR'] = $_SERVER["REMOTE_ADDR"];

header('Content-type: application/json');
exit(json_encode($headers, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
