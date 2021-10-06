<?php
namespace controllers\hooks;

use controllers\AppController;
use messengers\Message;
use messengers\Recipient;

final class ChatAction
{
    public $type;
    public $chatId;
    public $chatName;
    public $messengerName;
}

abstract class Hooks extends AppController
{
    protected function processPost($params)
    {
        $this->prepareMessenger($params[2]);
        $actionData = $this->getDataByMessenger($params);
        if (!empty($actionData->chatId)) {
            $this->processChatAction($actionData, htmlspecialchars($params[2]));
        }
    }

    final protected function processGet($params){}
    final protected function processDelete($params){}

    protected abstract function getDataByMessenger(array $params): ChatAction;
    protected abstract function sendMessage(Message $message, Recipient $recipient);
    protected abstract function prepareMessenger(string $amoDomain);

    private function processChatAction(ChatAction $action, string $amoDomain)
    {
        switch ($action->type) {
            case 'createChat':
                $this->createChatProcessing($action, $amoDomain);
                break;
            case 'leaveChat':
                $this->leaveChatProcessing($action);
        }
    }

    private function createChatProcessing(ChatAction $action, string $amoDomain)
    {
        $this->sendHelloMessage($action, $amoDomain);
        $this->addChatToDb($amoDomain, $action);
    }

    private function sendHelloMessage(ChatAction $action, string $amoDomain)
    {
        $message = new Message();
        $message->text = "Теперь вы будете получать уведомления из вашего AMO домена: ${amoDomain}";

        $recipient = new Recipient();
        $recipient->id = $action->chatId;

        $this->sendMessage($message, $recipient);
    }

    private function addChatToDb(string $amoDomain, ChatAction $action)
    {
        \dataAccess\saveNewChat($amoDomain, $action->chatId, $action->chatName, $action->messengerName);
    }

    private function leaveChatProcessing(ChatAction $action)
    {

    }

    protected function getIsAuthDisable(): bool
    {
        return true;
    }

    protected function getIsCheckCORS(): bool
    {
        return false;
    }
}
