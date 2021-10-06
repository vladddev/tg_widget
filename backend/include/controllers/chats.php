<?php
namespace controllers\Chats;

use controllers\AppController;

final class Chats extends AppController
{
    protected function processGet($params)
    {
        header('Content-type: application/json');
        if (isset($params[3]) && $params[3] === 'get') {
            $chats = \dataAccess\getChats($this->auth->getDomain(), htmlspecialchars($params[4]));
            echo json_encode($chats);
        }
    }
    protected function processDelete($params){}
    protected function processPost($params){}
}