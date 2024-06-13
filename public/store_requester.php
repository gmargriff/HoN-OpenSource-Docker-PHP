<?php
$enabled_skins = file_get_contents("public_docs/enable_skins.json");
$enabled_skins = json_decode($enabled_skins, true);
foreach(array_filter($enabled_skins, function($e) {
    return $e["enable"] == 1;
}) as $skin) {
    echo $skin["skin"] . "<br />";
}

die();
$myfile = fopen("public_docs/" . date("YmdHis") . "store" . "-request" . ".json", "w") or die("Unable to open file!");
$response = false;
try {
    $txt = array(
        "request" => $_REQUEST,
        "response" => $response
    );

    $txt = json_encode($txt);

    fwrite($myfile, $txt);

    $date = new DateTimeImmutable();
    $items = file_get_contents("public_docs/store_products_model.json");
    $items = json_decode($items, true);
    $items["timestamp"] = $date->getTimestamp();
    echo serialize($items);
} catch (Exception $e) {
    fwrite($myfile, "123");
}
fclose($myfile);
