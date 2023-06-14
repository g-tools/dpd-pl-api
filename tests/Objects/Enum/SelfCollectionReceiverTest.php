<?php

namespace GTools\DpdTests\Objects\Enum;

use PHPUnit\Framework\TestCase;
use GTools\Dpd\Objects\Enum\SelfCollectionReceiver;

class SelfCollectionReceiverTest extends TestCase
{
    /**
     * @dataProvider knownSelfCollectionReceivers
     */
    public function testCreation($selfCollectionReceiver)
    {
        $selfCollectionReceiver = SelfCollectionReceiver::$selfCollectionReceiver();
        self::assertEquals($selfCollectionReceiver, (string)$selfCollectionReceiver);
    }

    public function knownSelfCollectionReceivers()
    {
        return [
            ['PRIV'],
            ['COMP'],
        ];
    }
}
