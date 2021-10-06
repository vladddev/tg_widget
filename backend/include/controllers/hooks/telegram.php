<?php
namespace controllers\hooks;
use messengers\Message;
use messengers\Recipient;
use messengers\telegram\BotConfig;
use messengers\telegram\TelegramWebClient;

require 'hooks.php';

final class Telegram extends Hooks
{
    private $telegramMessenger;

    protected function getDataByMessenger(array $params): ChatAction
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode($params['body'], true);
            if(isset($data['message'])) {
                $chatAction = new ChatAction();
                $chatAction->type = (($data['message']['text'] ?? '') === '/start') ? 'createChat' : '';
                $chatAction->chatId = $data['message']['chat']['id'];
                $chatAction->messengerName = 'telegram';
                $chatAction->chatName = $data['message']['chat']['title'] ?? $data['message']['chat']['first_name'];

                return $chatAction;
            }
        }
        return new ChatAction();
    }

    protected function sendMessage(Message $message, Recipient $recipient)
    {
        $this->telegramMessenger->sendMessage($message, $recipient);
    }

    protected function prepareMessenger(string $amoDomain)
    {
        $botConfig = new BotConfig(\dataAccess\getTelegramChatId($amoDomain));
        $this->telegramMessenger = new \messengers\telegram\Telegram(new TelegramWebClient($botConfig));
    }
}
