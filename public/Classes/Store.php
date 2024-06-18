<?PHP

namespace Classes;

use \RedBeanPHP\R as R;
use Exception;

    # Item types
    #     Chat Name Colour       =>   "cc"
    #     Chat Symbol            =>   "cs"
    #     Account Icon           =>   "ai"
    #     Alternative Avatar     =>   "aa"
    #     Announcer Voice        =>   "av"
    #     Taunt                  =>   "t"
    #     Courier                =>   "c"
    #     Hero                   =>   "h"
    #     Early-Access Product   =>   "eap"
    #     Status                 =>   "s"
    #     Miscellaneous          =>   "m"
    #     Ward                   =>   "w"
    #     Enhancement            =>   "en"
    #     Coupon                 =>   "cp"
    #     Mastery                =>   "ma"
    #     Creep                  =>   "cr"
    #     Building               =>   "bu"
    #     Taunt Badge            =>   "tb"
    #     Teleportation Effect   =>   "te"
    #     Selection Circle       =>   "sc"
    #     Bundle                 =>   string.Empty

class Store
{
    /****
     * Get product list to show in store
     * 
     * Params:
     * 
     * $cookie - Player cookie to check session
     * 
     * $hosttime - Request hostTime parameter to fill response
     * 
     * Return: int 0 || 1
     *  */
    public function get_product_list(string $cookie, string $hosttime): array
    {
        // currency 0 = gold, 1 = silver
        try {
            // Check player session  
            $player = R::findOne("players", " cookie = ?", [$cookie]);
            if (!$player) {
                echo serialize(["Invalid user session"]);
                die();
            }

            // Get list of user's enabled skin
            $playerskins = R::find("playerskins", " player = ?", [$player->username]);
            $playerskins = json_decode(json_encode($playerskins), true);

            // Get store's product list
            $response = file_get_contents("public_docs/store_products_model.json");
            $response = json_decode($response, true);

            // Split productCodes into iterable array
            $product_codes = explode("|", $response["productCodes"]);

            // Reset already owned values in response
            $response["productAlreadyOwned"] = "";

            // Loop through each product code checking if it's enabled
            // and add result to productAlreadyOwned
            foreach ($product_codes as $p_code) {
                $response["productAlreadyOwned"] .= $this->is_enabled($p_code, $playerskins) . "|";
            }

            // Remove last | from response if it exists
            $response["productAlreadyOwned"] = trim($response["productAlreadyOwned"], "|");

            // Get player current currencies
            $response["totalMMPoints"] = $player->mmpoints;
            $response["totalPoints"] = $player->points;

            // Send back hosttime and current timestamp 
            $response["timestamp"] = time();
            $response["requestHostTime"] = $hosttime;

            return $response;
        } catch (Exception $e) {
            echo serialize([$e->getMessage()]);
            die();
        }
    }

    /****
     * Buy product using selected currency
     * 
     * Params:
     * 
     * $product_id - Product to be added to account
     * 
     * $currency - Currency to remove from account
     * 
     * $cookie - Player cookie to check session
     * 
     * $hosttime - Request hostTime parameter to fill response
     * 
     * Return: int 0 || 1
     *  */
    public function add_product_to_account(int $product_id, int $currency, string $cookie, string $hosttime): bool
    {
        try {
            // Check player session  
            $player = R::findOne("players", " cookie = ?", [$cookie]);
            if (!$player) {
                echo serialize(["Invalid user session"]);
                die();
            }

            // Get store's product list
            $list = file_get_contents("public_docs/store_products_model.json");
            $list = json_decode($list, true);

            // Split productIDs into iterable array
            // then get only the key_value from selected id
            $product_ids = explode("|", $list["productIDs"]);
            $filtered_id = array_search($product_id, $product_ids);

            // Split product prices into iterable array
            // based on currency to be used
            // currency 0 = gold, 1 = silver
            $product_prices = $currency == 1 ? explode("|", $list["premium_mmp_cost"]) : explode("|", $list["productPrices"]);

            // Get product price for current product
            $price = $product_prices[$filtered_id];

            // Check if player have enough currency to buy product
            $player_currency = $currency == 1 ? $player->mmpoints : $player->points;

            if ($price > $player_currency) {
                echo serialize(["Player does not have enough currency"]);
                die();
            }

            // Split productCodes into iterable array
            $product_codes = explode("|", $list["productCodes"]);

            // Get product code for current product
            $product_code = $product_codes[$filtered_id];

            // Remove currency from player account
            if($currency == 1) {
                $player->mmpoints = $player->mmpoints - $price;
            } else {
                $player->points = $player->points - $price;
            }
            R::store($player);

            // Add product to player account
            $add_product = R::dispense("playerskins");
            $add_product->player = $player->username;
            $add_product->code = $product_code;
            R::store($add_product);

            return true;
        } catch (Exception $e) {
            echo serialize([$e->getMessage()]);
            die();
        }
    }

    /****
     * Check if product is already owned by player
     * 
     * Params:
     * 
     * $product - Product Code
     * 
     * $enabled_list - List of codes that player already own
     * 
     * Return: int 0 || 1
     *  */
    private function is_enabled(string $product, array $enabled_list): int
    {
        foreach ($enabled_list as $element) {
            if (in_array($product, $element))
                return 1;
        }
        return 0;
    }
}
