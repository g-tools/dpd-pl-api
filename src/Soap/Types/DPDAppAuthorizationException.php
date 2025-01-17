<?php

namespace GTools\Dpd\Soap\Types;

class DPDAppAuthorizationException
{

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $message;

    /**
     * @return string
     */
    public function getLogin() : string
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return $this
     */
    public function setLogin(string $login) : \GTools\Dpd\Soap\Types\DPDAppAuthorizationException
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message) : \GTools\Dpd\Soap\Types\DPDAppAuthorizationException
    {
        $this->message = $message;
        return $this;
    }


}

