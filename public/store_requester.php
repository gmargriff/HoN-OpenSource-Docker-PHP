<?php
$response = file_get_contents("public_docs/store_products_model.json");
$response = json_decode($response, true);
$response["timestamp"] = time();
$response["requestHostTime"] = $_REQUEST["hostTime"];
$owned = explode("|", $response["productAlreadyOwned"]);
$response["productAlreadyOwned"] = "";
foreach ($owned as $o) {
    $response["productAlreadyOwned"] .= 1 . "|";
}
$response["productAlreadyOwned"] = trim($response["productAlreadyOwned"], "|");
$response = serialize($response);
echo $response;

$log = array(
    "request" => $_REQUEST,
    "response" => unserialize($response)
);

if ($env->ENVIRONMENT == "debug") {
    file_put_contents("public_docs/"  . date("YmdHis") . "store" . "-request" . ".json", json_encode($log));
}
