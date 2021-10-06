<?php
namespace messengers\telegram;
use web\WebClient;

define('TELEGRAM_API_URL', 'https://api.telegram.org');

final class BotConfig
{
    public $id;
    public function __construct(string $id) {
        if (!$id) {
            throw new \Exception();
        }
        $this->id = $id;
    }
}

final class TelegramWebClient extends WebClient
{
    private $botConfig;

    public function __construct(BotConfig $botConfig) {
        $this->botConfig = $botConfig;
    }

    public function getTelegramResponse($actionName, $data, $isThrowData = false)
    {
        $response = parent::getResponse("telegram-bot/1.0", $this->getApiUrl($actionName), 'POST', $isThrowData? $data :  json_encode($data), ['Content-Type:application/json']);
        return json_decode($response, true);
    }

    protected function getIsError($code)
    {
        return $code < 200 || $code > 204;
    }

    private function getApiUrl(string $actionName): string
    {
        return TELEGRAM_API_URL . "/bot" . $this->botConfig->id . "/${actionName}";
    }
}

