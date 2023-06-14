<?php

namespace GTools\Dpd\Soap\Types;

class DPDAppServiceException
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
     * @return $this
     */
    public function setMessage(string $message) : \GTools\Dpd\Soap\Types\DPDAppServiceException
    {
        $this->message = $message;
        return $this;
    }


}

