<?php
function getRouteMapping(): array
{
    return [
        ['match' => "/^\/install/u", 'name' => '\install\Install', 'path' => "/include/controllers/install.php"],
        ['match' => "/^\/hooks\/telegram\/[^\/]{1,}$/u", 'name' => '\hooks\Telegram', 'path' => "/include/controllers/hooks/telegram.php"],
        ['match' => "/^\/send\/telegram/u", 'name' => '\send\Telegram', 'path' => "/include/controllers/send/telegram.php"],
        ['match' => "/^\/[^\/]{1,}\/[0-9]{1,}\/chats\/get\/telegram$/u", 'name' => '\chats\Chats', 'path' => "/include/controllers/chats.php"]
    ];
}
