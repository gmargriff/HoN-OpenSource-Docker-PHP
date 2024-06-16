<?php

namespace Classes;

use Ramsey\Uuid\Uuid;

class ServerListResponse
{
    public string $acc_key;
    public string $acc_key_hash;
    public int $vested_threshold = 5;

    function __construct(string $cookie, string $S2_CHAT_SALT)
    {
        $this->acc_key = Uuid::uuid4()->toString();
        $this->acc_key_hash = $this->computeMatchServerChatAuthenticationHash($this->acc_key, $cookie, $S2_CHAT_SALT);
    }

    public function computeChatServerCookieHash(int $accountID, string $remoteIPAddress, string $cookie, string $S2_CHAT_SALT): string
    {
        $chat_server_cookie = $accountID . $remoteIPAddress . $cookie . $S2_CHAT_SALT;
        $chat_server_cookie_hash = bin2hex(hash('sha1', $chat_server_cookie, true));
        return $chat_server_cookie_hash;
    }

    public function computeMatchServerChatAuthenticationHash(string $key, string $cookie, string $S2_CHAT_SALT): string
    {
        return bin2hex(hash('sha1', $key . $cookie . $S2_CHAT_SALT, true));
    }
}

class ServerForResponse
{
    public string $server_id;
    public string $ip;
    public string $port;
    public string $location;

    function __construct(string $id, string $ip, string $port, string $location)
    {
        $this->server_id = $id;
        $this->ip = $ip;
        $this->port = $port;
        $this->location = $location;
    }
}

class ServerForJoin extends ServerForResponse
{
    public string $class = "1";
}

class ServerForCreate extends ServerForResponse
{
    public string $c_state = "1";
}

class ServerForJoinListResponse extends ServerListResponse
{
    public string $cookie;
    public array $server_list;

    function __construct(string $cookie, string $S2_CHAT_SALT)
    {
        parent::__construct($cookie, $S2_CHAT_SALT);
        $this->server_list = [];
    }
}

class ServerForCreateListResponse extends ServerListResponse
{
    public string $cookie;
    public array $server_list;

    function __construct(string $cookie, string $S2_CHAT_SALT)
    {
        parent::__construct($cookie, $S2_CHAT_SALT);
        $this->server_list = [];
    }
}
