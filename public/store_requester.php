<?php

use Classes\Store;

require_once("./require.php");

try {
    $request_code = isset($_REQUEST["request_code"]) ? $_REQUEST["request_code"] : 0;
    $response = "";
    $log = array(
        "request" => $_REQUEST
    );
    file_put_contents("public_docs/"  . date("YmdHis") . "store" . "-request_start" . ".json", json_encode($log));

    switch ($request_code) {
        case 1:
            $store = new Store;
            $product_list = $store->get_product_list($_REQUEST["cookie"], $_REQUEST["hostTime"]);
            $response = serialize($product_list);
            echo $response;
            break;
        case 4:
            $store = new Store;
            $new_data = $store->add_product_to_account($_REQUEST["product_id"], $_REQUEST["currency"], $_REQUEST["cookie"], $_REQUEST["hostTime"]);

            if ($new_data) {
                $res = [];
                $res['responseCode'] = 4;
                $res['error'] = '';
                $res['popupCode'] = 3;
                $res['errorCode'] = 0;
                $res['totalPoints'] = $new_data->points;
                $res['totalMMPoints'] = $new_data->mmpoints;
                $res['errorCode'] = 0;

                $res["requestHostTime"] = $_REQUEST["hostTime"];
                $res["tauntUnlocked"] = '1';

                $response = serialize($res);
                echo $response;
            }
            break;
        case 8:
            $store = new Store;
            $product_id = $store->get_aa_id_by_code($_REQUEST["hero_name"], $_REQUEST["avatar_code"]);
            $new_data = $store->add_product_to_account($product_id, $_REQUEST["currency"], $_REQUEST["cookie"], "0");
            if ($new_data) {
                $res = [];
                $res['responseCode'] = 8;
                $res['error'] = '';
                $res['popupCode'] = 3;
                $res['errorCode'] = 0;
                $res['totalPoints'] = $new_data->points;
                $res['totalMMPoints'] = $new_data->mmpoints;
                $res['errorCode'] = 0;

                $response = serialize($res);
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
        file_put_contents("public_docs/"  . date("YmdHis") . "store" . "-request_err" . ".json", json_encode($log));
    }
    echo serialize([$e->getMessage()]);
}
