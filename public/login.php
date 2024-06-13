<?PHP
require_once("./require.php");

use Brick\Math\BigInteger;
use Classes\SRPClient;
use \RedBeanPHP\R as R;

$errors = [];
$success = false;

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

if (@$_POST["username"] && @$_POST["password"]) {
    try {
        if (strlen($_POST["password"]) < 6) {
            array_push($errors, "Password must have at least six characters.");
        }

        $strong_password = checkChars($_POST["password"]);
        if (!$strong_password["Uppercase"] || !$strong_password["Lowercase"] || (!$strong_password["Special Characters"] && !$strong_password["Numeric Values"])) {
            array_push($errors, "Password must have at least one uppercase, one lowercase and a number or special character.");
        }

        $player = R::findOne("players", " username = ?", [$_POST["username"]]);
        if (!$player) {
            array_push($errors, "Player not found.");
        }

        if (!$errors) {
            $client = SRPClient::create(
                BigInteger::fromBase($env->S2_N, 16),
                BigInteger::fromBase($env->S2_G, 16),
                BigInteger::fromBase($env->S2_K, 16)
            )->setSize(512)->setHasher("sha256");

            $salt2 = $player->salt;
            $verifier = $client->hash_password($_POST["password"], $salt2, $env->S2_SRP_MAGIC1, $env->S2_SRP_MAGIC2);
            if ($player->verifier != $verifier) {
                array_push($errors, "Password mismatch.");
            } else {
                $success = $player;
            }
        }
    } catch (Throwable $e) {
        array_push($errors, $e->getMessage());
    }

    if ($success) {
        echo json_encode($success);
    } else {
        echo json_encode($errors);
    }
}
