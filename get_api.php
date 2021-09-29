<?php
header("Content-Type: application/json");
require_once __DIR__ . "/app.php";


$app = new CallInfo();
$data = $app->get_api();
print_r(json_encode($data));
?>