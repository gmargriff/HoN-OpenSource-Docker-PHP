<?PHP

namespace Classes;

use Brick\Math\BigInteger;
use Exception;
use Windwalker\SRP\SRPServer as BaseServer;


class SRPServer extends BaseServer
{
    public function deriveSession(BigInteger $serverSecretEphemeral, BigInteger $clientPublicEphemeral, BigInteger $salt, string $username, BigInteger $verifier, BigInteger $clientSessionProof, bool $debug = false)
    {
        $N = $this->getPrime();
        $g = $this->getGenerator();


        if ($clientPublicEphemeral->mod($N)->isEqualTo(0)) {
            throw new Exception("The client sent an invalid public ephemeral");
        }

        $b = $serverSecretEphemeral;
        $A = $clientPublicEphemeral;
        $s = $salt;
        $I = $username;
        $v = $verifier;

        $B = $this->generatePublic($serverSecretEphemeral, $v);
        $u = $this->generateCommonSecret($A, $B);
        $S = $this->generatePreMasterSecret($A, $b, $verifier, $u);
        $K = $this->hash($S);

        $g = $this->pad_g(strlen($N->toBytes()) - 1, $g->toBytes());

        $M1 = $this->hash($this->hash($N)->xor($this->hash($g)), $this->hash($I), $s, $A, $B, $K);

        $expected = $clientSessionProof;
        $actual = $M1;
        if ($actual != $expected) {
            throw new Exception("Client provided session proof is invalid");
        }

        $P = $this->hash($A, $M1, $K);

        if ($debug) {
            $debug = [
                "description" => "Server compute",
                "B" => $B->toBase(16),
                "u" => $u->toBase(16),
                "S" => $S->toBase(16),
                "K" => $K->toBase(16),
                "M1" => $M1->toBase(16),
                "P" => $P->toBase(16),
                "hasher" => $this->getHasher()
            ];

            echo "<pre>";
            print_r($debug);
            echo "</pre>";
        }
        return [
            "Key" => $K->toBase(16),
            "Proof" => $P->toBase(16)
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
}
