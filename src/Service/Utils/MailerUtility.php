<?php

namespace App\Service\Utils;

class MailerUtility
{
    public function __construct(
        protected string $sender,
    ) {
        //
    }

    public function getSender(): string
    {
        return $this->sender;
    }
}