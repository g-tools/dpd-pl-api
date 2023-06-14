<?php

namespace GTools\Dpd\Soap\Types;

class Exception
{
    /**
     * @var string
     */
    private $message;

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage(string $message) : Exception
    {
        $this->message = $message;

        return $this;
    }
}
