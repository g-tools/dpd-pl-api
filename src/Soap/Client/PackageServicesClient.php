<?php

namespace GTools\Dpd\Soap\Client;

class PackageServicesClient extends \Phpro\SoapClient\Client
{
    //    public function appendParcelsToPackageV1(\GTools\Dpd\Soap\Types\AppendParcelsToPackageV1Request $appendParcelsToPackageV1) : \GTools\Dpd\Soap\Types\appendParcelsToPackageV1Response
//    {
//        return $this->call('appendParcelsToPackageV1', $appendParcelsToPackageV1);
//    }

    public function findPostalCodeV1(\GTools\Dpd\Soap\Types\FindPostalCodeV1Request $findPostalCodeV1) : \GTools\Dpd\Soap\Types\findPostalCodeV1Response
    {
        return $this->call('findPostalCodeV1', $findPostalCodeV1);
    }

//    public function generatePackagesNumbersV1(\GTools\Dpd\Soap\Types\GeneratePackagesNumbersV1Request $generatePackagesNumbersV1) : \GTools\Dpd\Soap\Types\generatePackagesNumbersV1Response
//    {
//        return $this->call('generatePackagesNumbersV1', $generatePackagesNumbersV1);
//    }

//    public function generatePackagesNumbersV2(\GTools\Dpd\Soap\Types\GeneratePackagesNumbersV2Request $generatePackagesNumbersV2) : \GTools\Dpd\Soap\Types\generatePackagesNumbersV2Response
//    {
//        return $this->call('generatePackagesNumbersV2', $generatePackagesNumbersV2);
//    }

//    public function generatePackagesNumbersV3(\GTools\Dpd\Soap\Types\GeneratePackagesNumbersV3Request $generatePackagesNumbersV3) : \GTools\Dpd\Soap\Types\generatePackagesNumbersV3Response
//    {
//        return $this->call('generatePackagesNumbersV3', $generatePackagesNumbersV3);
//    }

    public function generatePackagesNumbersV4(\GTools\Dpd\Soap\Types\GeneratePackagesNumbersV4Request $generatePackagesNumbersV4) : \GTools\Dpd\Soap\Types\generatePackagesNumbersV4Response
    {
        return $this->call('generatePackagesNumbersV4', $generatePackagesNumbersV4);
    }

//    public function generateProtocolV1(\GTools\Dpd\Soap\Types\GenerateProtocolV1Request $generateProtocolV1) : \GTools\Dpd\Soap\Types\generateProtocolV1Response
//    {
//        return $this->call('generateProtocolV1', $generateProtocolV1);
//    }

//    public function generateProtocolsWithDestinationsV1(\GTools\Dpd\Soap\Types\GenerateProtocolsWithDestinationsV1Request $generateProtocolsWithDestinationsV1) : \GTools\Dpd\Soap\Types\generateProtocolsWithDestinationsV1Response
//    {
//        return $this->call('generateProtocolsWithDestinationsV1', $generateProtocolsWithDestinationsV1);
//    }

    public function generateProtocolV2(\GTools\Dpd\Soap\Types\GenerateProtocolV2Request $generateProtocolV2) : \GTools\Dpd\Soap\Types\generateProtocolV2Response
    {
        return $this->call('generateProtocolV2', $generateProtocolV2);
    }

//    public function generateProtocolsWithDestinationsV2(\GTools\Dpd\Soap\Types\GenerateProtocolsWithDestinationsV2Request $generateProtocolsWithDestinationsV2) : \GTools\Dpd\Soap\Types\generateProtocolsWithDestinationsV2Response
//    {
//        return $this->call('generateProtocolsWithDestinationsV2', $generateProtocolsWithDestinationsV2);
//    }

    public function generateSpedLabelsV1(\GTools\Dpd\Soap\Types\GenerateSpedLabelsV1Request $generateSpedLabelsV1) : \GTools\Dpd\Soap\Types\generateSpedLabelsV1Response
    {
        return $this->call('generateSpedLabelsV1', $generateSpedLabelsV1);
    }

//    public function generateSpedLabelsV2(\GTools\Dpd\Soap\Types\GenerateSpedLabelsV2Request $generateSpedLabelsV2) : \GTools\Dpd\Soap\Types\generateSpedLabelsV2Response
//    {
//        return $this->call('generateSpedLabelsV2', $generateSpedLabelsV2);
//    }

//    public function generateSpedLabelsV3(\GTools\Dpd\Soap\Types\GenerateSpedLabelsV3Request $generateSpedLabelsV3) : \GTools\Dpd\Soap\Types\generateSpedLabelsV3Response
//    {
//        return $this->call('generateSpedLabelsV3', $generateSpedLabelsV3);
//    }

//    public function generateSpedLabelsV4(\GTools\Dpd\Soap\Types\GenerateSpedLabelsV4Request $generateSpedLabelsV4) : \GTools\Dpd\Soap\Types\generateSpedLabelsV4Response
//    {
//        return $this->call('generateSpedLabelsV4', $generateSpedLabelsV4);
//    }

    public function packagesPickupCallV1(\GTools\Dpd\Soap\Types\PackagesPickupCallV1Request $packagesPickupCallV1) : \GTools\Dpd\Soap\Types\packagesPickupCallV1Response
    {
        return $this->call('packagesPickupCallV1', $packagesPickupCallV1);
    }

//    public function packagesPickupCallV2(\GTools\Dpd\Soap\Types\PackagesPickupCallV2Request $packagesPickupCallV2) : \GTools\Dpd\Soap\Types\packagesPickupCallV2Response
//    {
//        return $this->call('packagesPickupCallV2', $packagesPickupCallV2);
//    }

//    public function packagesPickupCallV3(\GTools\Dpd\Soap\Types\PackagesPickupCallV3Request $packagesPickupCallV3) : \GTools\Dpd\Soap\Types\packagesPickupCallV3Response
//    {
//        return $this->call('packagesPickupCallV3', $packagesPickupCallV3);
//    }

    public function getCourierOrderAvailabilityV1(\GTools\Dpd\Soap\Types\GetCourierOrderAvailabilityV1Request $getCourierOrderAvailabilityV1) : \GTools\Dpd\Soap\Types\getCourierOrderAvailabilityV1Response
    {
        return $this->call('getCourierOrderAvailabilityV1', $getCourierOrderAvailabilityV1);
    }

//    public function packagesPickupCallV4(\GTools\Dpd\Soap\Types\PackagesPickupCallV4Request $packagesPickupCallV4) : \GTools\Dpd\Soap\Types\packagesPickupCallV4Response
//    {
//        return $this->call('packagesPickupCallV4', $packagesPickupCallV4);
//    }

//    public function importDeliveryBusinessEventV1(\GTools\Dpd\Soap\Types\ImportDeliveryBusinessEventV1Request $importDeliveryBusinessEventV1) : \GTools\Dpd\Soap\Types\importDeliveryBusinessEventV1Response
//    {
//        return $this->call('importDeliveryBusinessEventV1', $importDeliveryBusinessEventV1);
//    }
}
