<?PHP

namespace Classes;

use Brick\Math\BigInteger;
use Exception;
use Windwalker\SRP\SRPClient as BaseClient;


class SRPClient extends BaseClient
{
    public function deriveSession(BigInteger $a, BigInteger $B, BigInteger $s, string $Username, BigInteger $x, bool $debug = false)
    {
        $N = $this->getPrime();
        $g = $this->getGenerator();
        $A = $this->generatePublic($a);

        if ($B->mod($N)->isEqualTo(0)) {
            throw new Exception("The server sent an invalid public ephemeral");
        }

        $u = $this->generateCommonSecret($A, $B);
        $S = $this->generatePreMasterSecret($a, $B, $x, $u);
        $K = $this->hash($S);

        $g = $this->pad_g(strlen($N->toBytes()) - 1, $g->toBytes());

        $M1 = $this->hash($this->hash($N)->xor($this->hash($g)), $this->hash($Username), $s, $A, $B, $K);

        if ($debug) {
            $debug = [
                "description" => "Client compute",
                "u" => $u->toBase(16),
                "S" => $S->toBase(16),
                "x" => $x->toBase(16),
                "K" => $K->toBase(16),
                "M1" => $M1->toBase(16),
                "hasher" => $this->getHasher()
            ];

            echo "<pre>";
            print_r($debug);
            echo "</pre>";
        }

        return [
            "Key" => $K->toBase(16),
            "Proof" => $M1->toBase(16)
        ];
    }

    public function pad_g($padLenth, $gb)
    {
        $missing_size = $padLenth - strlen($gb);
        for ($i = 0; $i < $missing_size; $i++) {
            $gb = "\x00" . $gb;
        }
        return $gb;
    }

    public function calc_K(BigInteger $a, BigInteger $A, BigInteger $B, BigInteger $x): BigInteger
    {
        $u = $this->generateCommonSecret($A, $B);
        $S = $this->generatePreMasterSecret($a, $B, $x, $u);
        $K = $this->hash($S);

        return $K;
    }

    public function hash_password($password, $salt2, $S2_SRP_MAGIC1, $S2_SRP_MAGIC2)
    {
        return hash("sha256", md5(md5($password) . $salt2 . $S2_SRP_MAGIC1) . $S2_SRP_MAGIC2);
    }
}
