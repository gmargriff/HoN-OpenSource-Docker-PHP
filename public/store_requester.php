<?php

use Classes\Store;

require_once("./require.php");

try {
    $request_code = isset($_REQUEST["request_code"]) ? $_REQUEST["request_code"] : 0;
    $response = "";

    switch ($request_code) {
        case 1:
            $store = new Store;
            $product_list = $store->get_product_list($_REQUEST["cookie"], $_REQUEST["hostTime"]);
            $response = serialize($product_list);
            echo $response;
            break;
        case 4:
            $store = new Store;
            if ($store->add_product_to_account($_REQUEST["product_id"], $_REQUEST["currency"], $_REQUEST["cookie"], $_REQUEST["hostTime"])) {
                $product_list = $store->get_product_list($_REQUEST["cookie"], $_REQUEST["hostTime"]);
                $response = serialize($product_list);
                echo $response;
            }
            break;
        default:
            $response = serialize("Unknown request_code");
    }

    if ($env->ENVIRONMENT == "debug") {
        $log = array(
            "request" => $_REQUEST,
            "response" => unserialize($response)
        );

        file_put_contents("public_docs/"  . date("YmdHis") . "store" . "-request" . ".json", json_encode($log));
    }
} catch (Exception $e) {
    if ($env->ENVIRONMENT == "debug") {
        $log = array(
            "request" => $_REQUEST,
            "error" => $e->getMessage()
        );
        file_put_contents("public_docs/"  . date("YmdHis") . "store" . "-request" . ".json", json_encode($log));
    }
    echo serialize([$e->getMessage()]);
}
