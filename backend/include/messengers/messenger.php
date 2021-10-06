<?php
namespace messengers;

class Message
{
    public $text;
}

class Recipient
{
    public $id;
}

abstract class Messenger
{
    public abstract function sendMessage(Message $message, Recipient $recipient);
    public abstract function setWebHook(string $url);
    public abstract function deleteWebHook();
}
