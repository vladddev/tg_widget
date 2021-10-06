<?php
namespace controllers\send;

use messengers\Message;
use messengers\Recipient;
use messengers\telegram\BotConfig;
use messengers\telegram\TelegramWebClient;

require 'send.php';

final class Telegram extends Send
{
    private $telegramMessenger;

    protected function sendMessage(Message $message, Recipient $recipient)
    {
        $this->telegramMessenger->sendMessage($message, $recipient);
    }

    protected function prepareMessenger(string $amoDomain): string
    {
        $botConfig = new BotConfig(\dataAccess\getTelegramChatId($amoDomain));
        $this->telegramMessenger = new \messengers\telegram\Telegram(new TelegramWebClient($botConfig));
        return 'telegram';
    }
}
