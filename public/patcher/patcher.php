<?php
$patcher = file_get_contents(__DIR__ . "/patcher.json");
$patcher = json_decode($patcher, true);
echo serialize($patcher);
