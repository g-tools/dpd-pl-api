<?php

namespace GTools\Dpd\Request;

use GTools\Dpd\Objects\Enum\TrackingEventsCount;
use GTools\Dpd\Soap\Types\EventsSelectTypeEnum;
use GTools\Dpd\Soap\Types\FindPostalCodeV1Request;
use GTools\Dpd\Soap\Types\GetEventsForWaybillV1Request;
use GTools\Dpd\Soap\Types\PostalCodeV1;

class GetParcelTrackingRequest
{
    /**
     * @var string
     */
    private $waybill;

    /**
     * @var TrackingEventsCount
     */
    private $trackingEventsCount;

    /**
     * @var string
     */
    private $language;

    /**
     * GetParcelTrackingRequest constructor.
     * @param string $waybill
     * @param TrackingEventsCount $trackingEventsCount
     * @param string $language
     */
    public function __construct($waybill, ?TrackingEventsCount $trackingEventsCount, $language)
    {
        $this->waybill = $waybill;
        $this->trackingEventsCount = $trackingEventsCount ?: TrackingEventsCount::ALL();
        $this->language = $language;
    }

    public static function fromWaybill($waybill, TrackingEventsCount $eventsCount = null, $language = 'PL'): GetParcelTrackingRequest
    {
        return new static($waybill, $eventsCount, $language);
    }

    /**
     * @return GetEventsForWaybillV1Request
     */
    public function toPayload(): GetEventsForWaybillV1Request
    {
        $request = new GetEventsForWaybillV1Request();
        $request->setEventsSelectType(new EventsSelectTypeEnum((string) $this->trackingEventsCount));
        $request->setLanguage($this->language);
        $request->setWaybill($this->waybill);

        return $request;
    }
}
