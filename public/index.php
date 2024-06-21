<?PHP
require_once("./require.php");

use Brick\Math\BigInteger;
use Classes\SRPClient;
use \RedBeanPHP\R as R;


function generateRandomString($length = 22)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ^#[]~!.,=-_?';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function checkChars($str)
{
    $upperCase = preg_match('/[A-Z]/', $str);
    $lowerCase = preg_match('/[a-z]/', $str);
    $specialChar = preg_match('/[^A-Za-z0-9]/', $str);
    $numericVal = preg_match('/[0-9]/', $str);

    return [
        'Uppercase' => $upperCase,
        'Lowercase' => $lowerCase,
        'Special Characters' => $specialChar,
        'Numeric Values' => $numericVal,
    ];
}

$errors = [];
$success = false;

if (@$_POST["username"] && @$_POST["password"] && @$_POST["password_confirmation"]) {
    try {
        if ($_POST["password"] != $_POST["password_confirmation"]) {
            array_push($errors, "Password and confirmation does not match.");
        }

        if (strlen($_POST["password"]) < 6) {
            array_push($errors, "Password must have at least six characters.");
        }

        $strong_password = checkChars($_POST["password"]);
        if (!$strong_password["Uppercase"] || !$strong_password["Lowercase"] || (!$strong_password["Special Characters"] && !$strong_password["Numeric Values"])) {
            array_push($errors, "Password must have at least one uppercase, one lowercase and a number or special character.");
        }

        $player = R::findOne("players", " username = ?", [$_POST["username"]]);
        if ($player) {
            array_push($errors, "Player already registered.");
        }

        if (!$errors) {
            $client = SRPClient::create(
                BigInteger::fromBase($env->S2_N, 16),
                BigInteger::fromBase($env->S2_G, 16),
                BigInteger::fromBase($env->S2_K, 16)
            )->setSize(512)->setHasher("sha256");

            $salt2 = generateRandomString();
            $verifier = $client->hash_password($_POST["password"], $salt2, $env->S2_SRP_MAGIC1, $env->S2_SRP_MAGIC2);
            $player = R::dispense('players');
            $player->username = $_POST["username"];
            $player->salt = $salt2;
            $player->verifier = $verifier;
            $success = R::store($player);
        }
    } catch (Throwable $e) {
        array_push($errors, $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoN OpenSource</title>
    <link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
    <link rel="manifest" href="/icons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/icons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        body * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none !important;
        }

        .wrapper {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        #background {
            position: absolute;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            z-index: 1;
            left: 0;
            top: 0;
        }

        .form {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            width: 100%;
            padding: 5vh 0 12vh;
        }

        .form .logo {
            max-width: 100%;
            width: 800px;
        }

        .form form .input-control {
            position: relative;
        }

        .form form .input-control img {
            height: 28px;
            position: absolute;
            top: 0;
            right: -5px;
        }

        .form form .input-control img:first-of-type {
            right: unset;
            top: 0;
            left: -5px;
            transform: rotate(180deg);
        }

        .form form input {
            height: 28px;
            background: rgba(0, 0, 0, .9);
            border: 1px solid #4a4141;
            width: 300px;
            padding: 12px;
            color: white;
            margin-bottom: 14px;
            max-width: 90vw;
        }

        .form form {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .form form button {
            color: #9b7d75;
            text-shadow: 1px 1px 0 #31160d;
            background: rgb(93, 70, 57);
            background: linear-gradient(180deg, rgba(93, 70, 57, 1) 0%, rgba(38, 26, 23, 1) 100%);
            border: 1px solid #160f0c;
            font-weight: bold;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 2px;
            transition: .3s;
        }

        .form form .buttons {
            text-align: center;
        }

        .form form button:hover {
            filter: brightness(.8);
        }

        .form form a button {
            background: #465d39;
            background: linear-gradient(180deg, #465d39 0%, #0c2f0c 100%);
            border-color: #0c2f0c;
            color: #7dbb46;
            margin-bottom: 8px;
        }

        .error {
            background: #630000;
            padding: 8px 28px;
            border: 1px solid #791313;
            color: #d74545;
            border-radius: 2px;
            font-size: 11px;
            text-align: center;
            margin-bottom: 14px;
        }

        .success {
            background: #185705;
            padding: 8px 28px;
            border: 1px solid #225f0f;
            color: #61c343;
            border-radius: 2px;
            font-size: 11px;
            text-align: center;
            margin-bottom: 14px;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .drag {
            width: 100%;
            height: 20px;
            background: transparent;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 5;
            user-select: none;
            -webkit-user-select: none;
            -webkit-app-region: drag;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <video autoplay muted loop id="background">
            <source src="/public_docs/login_1080.webm" type="video/webm">
        </video>
        <div class="form">
            <img src="/public_docs/logo.png" alt="Heroes of Newerth" class="logo" />
            <form method="POST">
                <?php if (!$success) { ?>
                    <div class="input-control">
                        <img src="/public_docs/border.png" alt="border" />
                        <input type="text" name="username" placeholder="Username" required oninput="to_lowercase(this)" />
                        <img src="/public_docs/border.png" alt="border" />
                    </div>
                    <div class="input-control">
                        <img src="/public_docs/border.png" alt="border" />
                        <input type="password" name="password" placeholder="Password" required />
                        <img src="/public_docs/border.png" alt="border" />
                    </div>
                    <div class="input-control">
                        <img src="/public_docs/border.png" alt="border" />
                        <input type="password" name="password_confirmation" placeholder="Confirm password" required />
                        <img src="/public_docs/border.png" alt="border" />
                    </div>
                    <?php if ($errors) { ?>
                        <div class="error">
                            <ul>
                                <?php foreach ($errors as $err) { ?>
                                    <li><?= $err; ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <div class="buttons">
                        <a href="/hon_client.zip" title="Download"><button type="button">Download game</button></a>
                        <button type="submit">Register account</button>
                    </div>
                <?php } else { ?>
                    <div class="success">
                        <ul>
                            <li><?= $_POST["username"]; ?> successfully registered (<?= $success; ?>).</li>
                        </ul>
                    </div>
                    <div class="buttons">
                        <a href="/hon_client.zip" title="Download"><button type="button">Download game</button></a>
                    </div>
                <?php } ?>
            </form>
        </div>
</body>
<script>
    function to_lowercase(e) {
        e.value = e.value.toLowerCase();
    }
</script>

</html>