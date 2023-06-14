<?php

namespace GTools\DpdTests\Objects\Enum;

use PHPUnit\Framework\TestCase;
use GTools\Dpd\Objects\Enum\TrackingEventsCount;

class TrackingEventsCountTest extends TestCase
{
    /**
     * @dataProvider knownTrackingEventsCounts
     */
    public function testCreation($trackingEventCountName)
    {
        $trackingEventCount = TrackingEventsCount::$trackingEventCountName();
        self::assertEquals($trackingEventCountName, (string)$trackingEventCount);
    }

    public function knownTrackingEventsCounts()
    {
        return [
            ['ALL'],
            ['ONLY_LAST'],
        ];
    }
}
