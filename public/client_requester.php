<?php

require_once "./require.php";

use \RedBeanPHP\R as R;

use Brick\Math\BigInteger;
use Classes\Guides;
use Classes\SimpleStats;
use Classes\SRPClient;
use Classes\SRPServer;

$server = SRPServer::create(
    BigInteger::fromBase($env->S2_N, 16),
    BigInteger::fromBase($env->S2_G, 16),
    BigInteger::fromBase($env->S2_K, 16)
)->setSize(512)->setHasher('sha256');

$client = SRPClient::create(
    BigInteger::fromBase($env->S2_N, 16),
    BigInteger::fromBase($env->S2_G, 16),
    BigInteger::fromBase($env->S2_K, 16)
)->setSize(512)->setHasher("sha256");


if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

if (isset($_REQUEST['f'])) {
    $response = "";
    if ($_REQUEST['f'] == "get_account_all_hero_stats") {
        $response = file_get_contents("./public_docs/user_all_hero_stats.json");
        $response = json_decode($response, true);
        echo serialize($response);
        die();
    } else if ($_REQUEST['f'] == "show_stats") {
        $stats = file_get_contents("public_docs/user_model_stats.json");
        $stats = json_decode($stats, true);
        $stats["nickname"] = $_REQUEST["nickname"];
        $stats["name"] = $_REQUEST["nickname"]; // Clan name
        $stats["last_activity"] = date("d/m/Y");
        echo serialize($stats);
    } else if ($_REQUEST['f'] == "show_simple_stats") {
        $user_stats = new SimpleStats();
        $user_stats->nickname = "[" . $_REQUEST["nickname"] . "]" . $_REQUEST["nickname"];
        $user_stats = json_decode(json_encode($user_stats), true);
        $user_stats["0"] = 1;
        $response = serialize($user_stats);
        echo $response;
    } else if ($_REQUEST['f'] == "get_products") {
        $response = file_get_contents("public_docs/store_products_model.json");
        $response = json_decode($response, true);
        echo serialize($response);
    } else if ($_REQUEST['f'] == "pre_auth") {
        // Check if client sent required information
        $identity = $_REQUEST["login"] ? $_REQUEST["login"] : false;
        $A = $_REQUEST["A"] ? $_REQUEST["A"] : false;
        if (!$identity || !$A) {
            throw new Exception("Missing client information");
        }

        // Check if player exists
        $player = R::findOne("players", " username = ?", [$identity]);
        if (!$player) {
            throw new Exception("Player not found");
        }

        // Convert client A to BigInteger
        $A = BigInteger::fromBase($A, 16);

        // Generate a 256-sized authentication salt
        $authenticationSessionSalt = bin2hex(random_bytes(512 / 2));

        // Get password hash from database (p)
        $hash_password = $player->verifier;

        // Generate a hehashed password (x)
        $x = $client->generatePasswordHash(BigInteger::fromBase($authenticationSessionSalt, 16), $identity, $hash_password);

        // Generate a verifier (v) derived from the hehashed password (x)
        $verifier = $client->generateVerifier($x);

        // Generate secret (b) and public (B) ephemeral server values
        $b = $server->generateRandomSecret();
        $B = $server->generatePublic($b, $verifier);

        // Generate a persistent file for current user to request on next step
        $persistence = [
            "file_name" => "user_login_$identity.json",
            "b" => $b->toBase(16),
            "B" => $B->toBase(16),
            "A" => $A->toBase(16),
            "salt" => $authenticationSessionSalt,
            "verifier" => $verifier->toBase(16)
        ];

        file_put_contents("public_docs/" . $persistence["file_name"], json_encode($persistence));

        // Create server response
        $server_response = [
            "salt" => $authenticationSessionSalt,
            "B" => $B->toBase(16),
            "salt2" => $player->salt
        ];

        // Send the client the serialized response
        echo serialize($server_response);
    } else if ($_REQUEST['f'] == "srpAuth") {
        // Check if client sent required information
        $identity = $_REQUEST["login"] ? $_REQUEST["login"] : false;
        $clientM1 = $_REQUEST["proof"] ? $_REQUEST["proof"] : false;
        if (!$identity || !$clientM1) {
            throw new Exception("Missing client information");
        }

        // Check if player exists
        $player = R::findOne("players", " username = ?", [$identity]);
        if (!$player) {
            throw new Exception("Player not found");
        }

        // Recover pre_auth information
        $pre_auth = file_get_contents("public_docs/user_login_$identity.json");
        $pre_auth = json_decode($pre_auth);

        // Remove pre_auth file to avoid leaks
        unlink("public_docs/user_login_$identity.json");

        // Check if pre_auth is valid
        if (!isset($pre_auth)) {
            throw new Exception("Invalid pre_auth information");
        }

        // Check if pre_auth info is valid and convert to BigInteger
        $pre_auth_info = ["b", "B", "A", "salt", "verifier"];
        foreach ($pre_auth_info as $info) {
            if (!isset($pre_auth->$info)) {
                throw new Exception("Invalid pre_auth information");
            }

            $pre_auth->$info = BigInteger::fromBase($pre_auth->$info, 16);
        }
        // Derive server session from client information
        $serverSession = (object) $server->deriveSession($pre_auth->b, $pre_auth->A, $pre_auth->salt, $identity, $pre_auth->verifier, BigInteger::fromBase($clientM1, 16));

        $user_model = file_get_contents("public_docs/user_model.json");
        $user_model = json_decode($user_model, true);
        $user_model["proof"] = $serverSession->Proof;
        $user_model["nickname"] = $player->username;
        $user_model["identities"][0][0] = $player->username;

        // Enable alt avatars
        $alt_avatars = R::find("playerskins", " player = ?", [$player->username]);
        foreach ($alt_avatars as $skin) {
            array_push($user_model["my_upgrades"], $skin->code);
        }

        echo serialize($user_model);
    } else if ($_REQUEST['f'] == "get_guide_list_filtered") {
        $guides = new Guides;
        $filtered = $guides->get_guide_list_filtered($_REQUEST['hero'], $_REQUEST['hosttime']);
        echo serialize($filtered);
    } else if ($_REQUEST['f'] == "get_guide") {
        $guides = new Guides;
        $filtered = $guides->get_guide($_REQUEST['gid'], $_REQUEST['hero'], $_REQUEST['hosttime']);
        echo serialize($filtered);
    }

    $request_id = isset($_REQUEST["f"]) ? $_REQUEST["f"] : "unknown";

    $write = array(
        "request" => $_REQUEST,
        "response" => $response
    );

    file_put_contents("./public_docs/" . date("YmdHis") . "$request_id-request.json", json_encode($write));
}
