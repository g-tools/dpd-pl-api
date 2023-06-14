<?php

namespace T3ko\Dpd\Exception;

class ObjectException extends \Exception
{
    private $object;

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     */
    public function setObject($object): void
    {
        $this->object = $object;
    }

}
