<?php

namespace GTools\Dpd\Soap\Client;

use Phpro\SoapClient\Client;
use GTools\Dpd\Soap\Types\GetEventsForCustomerV4Request;
use GTools\Dpd\Soap\Types\GetEventsForCustomerV4Response;
use GTools\Dpd\Soap\Types\GetEventsForWaybillV1Request;
use GTools\Dpd\Soap\Types\GetEventsForWaybillV1Response;
use GTools\Dpd\Soap\Types\MarkEventsAsProcessedV1Request;
use GTools\Dpd\Soap\Types\MarkEventsAsProcessedV1Response;

class InfoServicesClient extends Client
{

    public function markEventsAsProcessedV1(MarkEventsAsProcessedV1Request $markEventsAsProcessedV1) : MarkEventsAsProcessedV1Response
    {
        return $this->call('markEventsAsProcessedV1', $markEventsAsProcessedV1);
    }

    public function getEventsForWaybillV1(GetEventsForWaybillV1Request $getEventsForWaybillV1) : GetEventsForWaybillV1Response
    {
        return $this->call('getEventsForWaybillV1', $getEventsForWaybillV1);
    }

    public function getEventsForCustomerV4(GetEventsForCustomerV4Request $getEventsForCustomerV4) : GetEventsForCustomerV4Response
    {
        return $this->call('getEventsForCustomerV4', $getEventsForCustomerV4);
    }

}

