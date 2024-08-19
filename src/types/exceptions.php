<?php
namespace PassNinja\Exceptions;

class PassNinjaException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
        $this->message = $message;
    }
}


class PassNinjaInvalidArgumentsException extends PassNinjaException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
