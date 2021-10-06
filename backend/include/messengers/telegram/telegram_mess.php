<?php
namespace messengers\telegram;
require 'webclient.php';

use messengers\Message;
use messengers\Messenger;
use messengers\Recipient;


final class Telegram extends Messenger
{
    private $webClient;

    public function __construct(TelegramWebClient $webClient) {
        $this->webClient = $webClient;
    }

    public function sendMessage(Message $message, Recipient $recipient)
    {
        $this->webClient->getTelegramResponse('sendMessage', $this->createMessageData($message, $recipient), true);
    }

    private function createMessageData(Message $message, Recipient $recipient): string
    {
        $chatId = intval($recipient->id);
        $text = $message->text;

        return "{\"chat_id\": ${chatId}, \"text\": \"${text}\"}";
    }

    public function setWebHook(string $url)
    {
        $this->webClient->getTelegramResponse('setWebhook', ['url' => $url]);
    }

    public function deleteWebHook()
    {
        $this->webClient->getTelegramResponse('deleteWebhook', []);
    }
}
