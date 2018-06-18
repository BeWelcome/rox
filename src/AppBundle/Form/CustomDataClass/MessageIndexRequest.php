<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 26.05.2018
 * Time: 17:27.
 */

namespace AppBundle\Form\CustomDataClass;

class MessageIndexRequest
{
    private $messages;

    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
