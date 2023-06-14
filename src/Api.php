<?php

namespace GTools\Dpd;

use Phpro\SoapClient\Soap\ClassMap\ClassMap;
use Phpro\SoapClient\Soap\ClassMap\ClassMapCollection;
use Phpro\SoapClient\Soap\Driver\ExtSoap\ExtSoapEngineFactory;
use Phpro\SoapClient\Soap\Driver\ExtSoap\ExtSoapOptions;
use Phpro\SoapClient\Soap\Handler\HttPlugHandle;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use GTools\Dpd\Request\CollectionOrderRequest;
use GTools\Dpd\Request\FindPostalCodeRequest;
use GTools\Dpd\Request\GenerateLabelsRequest;
use GTools\Dpd\Request\GeneratePackageNumbersRequest;
use GTools\Dpd\Request\GenerateProtocolRequest;
use GTools\Dpd\Request\GetCourierAvailabilityRequest;
use GTools\Dpd\Request\GetParcelTrackingRequest;
use GTools\Dpd\Response\CollectionOrderResponse;
use GTools\Dpd\Response\FindPostalCodeResponse;
use GTools\Dpd\Response\GenerateLabelsResponse;
use GTools\Dpd\Response\GeneratePackageNumbersResponse;
use GTools\Dpd\Response\GenerateProtocolResponse;
use GTools\Dpd\Response\GetCourierAvailabilityResponse;
use GTools\Dpd\Response\GetParcelTrackingResponse;
use GTools\Dpd\Soap\Client\AppServicesClient;
use GTools\Dpd\Soap\Client\InfoServicesClient;
use GTools\Dpd\Soap\Client\PackageServicesClient;
use GTools\Dpd\Soap\Types\AuthDataV1;

class Api
{
    const PACKAGESERVICE_SANDBOX_WSDL_URL = 'http://dpdservicesdemo.dpd.com.pl/DPDPackageObjServicesService/DPDPackageObjServices?wsdl';
    const PACKAGESERVICE_PRODUCTION_WSDL_URL = 'http://dpdservices.dpd.com.pl/DPDPackageObjServicesService/DPDPackageObjServices?wsdl';
    const APPSERVICE_SANDBOX_WSDL_URL = 'http://dpdappservicesdemo.dpd.com.pl/DPDCRXmlServicesService/DPDCRXmlServices?wsdl';
    const APPSERVICE_PRODUCTION_WSDL_URL = 'http://dpdappservices.dpd.com.pl/DPDCRXmlServicesService/DPDCRXmlServices?wsdl';
    const INFOSERVICE_SANDBOX_WSDL_URL = null;
    const INFOSERVICE_PRODUCTION_WSDL_URL = 'https://dpdinfoservices.dpd.com.pl/DPDInfoServicesObjEventsService/DPDInfoServicesObjEvents?wsdl';

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $masterFid;

    /**
     * @var bool
     */
    private $sandboxMode = false;

    /**
     * @var PackageServicesClient
     */
    private $packageServicesClient;

    /**
     * @var AppServicesClient
     */
    private $appServicesClient;

    /**
     * @var InfoServicesClient
     */
    private $infoServicesClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Api constructor.
     *
     * @param string $login
     * @param string $password
     * @param int    $masterFid
     */
    public function __construct(string $login, string $password, int $masterFid)
    {
        $this->login = $login;
        $this->password = $password;
        $this->masterFid = $masterFid;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return int
     */
    public function getMasterFid(): int
    {
        return $this->masterFid;
    }

    /**
     * @param int $masterFid
     */
    public function setMasterFid(int $masterFid)
    {
        $this->masterFid = $masterFid;
    }

    /**
     * @return bool
     */
    public function isSandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    /**
     * @param bool $sandboxMode
     */
    public function setSandboxMode(bool $sandboxMode)
    {
        $this->sandboxMode = $sandboxMode;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $clientClass
     *
     * @return string
     */
    private function getWsdl($clientClass)
    {
        if ($this->sandboxMode) {
            switch ($clientClass) {
                case PackageServicesClient::class:
                    return self::PACKAGESERVICE_SANDBOX_WSDL_URL;
                case AppServicesClient::class:
                    return self::APPSERVICE_SANDBOX_WSDL_URL;
                case InfoServicesClient::class:
                    //InfoServices endpoint has no sandbox mode - using production instead
                    return self::INFOSERVICE_PRODUCTION_WSDL_URL;
            }
        }

        switch ($clientClass) {
            case PackageServicesClient::class:
                return self::PACKAGESERVICE_PRODUCTION_WSDL_URL;
            case AppServicesClient::class:
                return self::APPSERVICE_PRODUCTION_WSDL_URL;
            case InfoServicesClient::class:
                return self::INFOSERVICE_PRODUCTION_WSDL_URL;
        }
    }

    /**
     * @return PackageServicesClient
     */
    private function obtainPackageServiceClient()
    {
        if ($this->packageServicesClient === null) {
            $this->packageServicesClient = $this->obtainClient(PackageServicesClient::class);
        }

        return $this->packageServicesClient;
    }

    /**
     * @return AppServicesClient
     */
    private function obtainAppServiceClient()
    {
        if ($this->appServicesClient === null) {
            $this->appServicesClient = $this->obtainClient(AppServicesClient::class);
        }

        return $this->appServicesClient;
    }

    /**
     * @return InfoServicesClient
     */
    private function obtainInfoServiceClient()
    {
        if ($this->infoServicesClient === null) {
            $this->infoServicesClient = $this->obtainClient(InfoServicesClient::class);
        }

        return $this->infoServicesClient;
    }

    /**
     * @param $clientClass
     *
     * @return \Phpro\SoapClient\ClientInterface
     */
    private function obtainClient($clientClass)
    {
        $engine = ExtSoapEngineFactory::fromOptionsWithHandler(
            ExtSoapOptions::defaults(
                $this->getWsdl($clientClass),
                [
                    'cache_wsdl' => WSDL_CACHE_NONE,
                ])
                ->withClassMap($this->getClassMaps()),
            HttPlugHandle::createWithDefaultClient()
        );
        $eventDispatcher = new EventDispatcher();

        return new $clientClass($engine, $eventDispatcher);
    }

    private function getClassMaps()
    {
        return new ClassMapCollection([
            new ClassMap('generatePackagesNumbersV1', \GTools\Dpd\Soap\Types\GeneratePackagesNumbersV1Request::class),
            new ClassMap('openUMLFeV1', \GTools\Dpd\Soap\Types\OpenUMLFeV1::class),
            new ClassMap('packageOpenUMLFeV1', \GTools\Dpd\Soap\Types\PackageOpenUMLFeV1::class),
            new ClassMap('parcelOpenUMLFeV1', \GTools\Dpd\Soap\Types\ParcelOpenUMLFeV1::class),
            new ClassMap('packageAddressOpenUMLFeV1', \GTools\Dpd\Soap\Types\PackageAddressOpenUMLFeV1::class),
            new ClassMap('servicesOpenUMLFeV2', \GTools\Dpd\Soap\Types\ServicesOpenUMLFeV2::class),
            new ClassMap('serviceCarryInOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceCarryInOpenUMLFeV1::class),
            new ClassMap('serviceCODOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceCODOpenUMLFeV1::class),
            new ClassMap('serviceCUDOpenUMLeFV1', \GTools\Dpd\Soap\Types\ServiceCUDOpenUMLeFV1::class),
            new ClassMap('serviceDeclaredValueOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceDeclaredValueOpenUMLFeV1::class),
            new ClassMap('serviceDedicatedDeliveryOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceDedicatedDeliveryOpenUMLFeV1::class),
            new ClassMap('servicePalletOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServicePalletOpenUMLFeV1::class),
            new ClassMap('serviceDutyOpenUMLeFV1', \GTools\Dpd\Soap\Types\ServiceDutyOpenUMLeFV1::class),
            new ClassMap('serviceGuaranteeOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceGuaranteeOpenUMLFeV1::class),
            new ClassMap('serviceInPersOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceInPersOpenUMLFeV1::class),
            new ClassMap('servicePrivPersOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServicePrivPersOpenUMLFeV1::class),
            new ClassMap('serviceRODOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceRODOpenUMLFeV1::class),
            new ClassMap('serviceSelfColOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceSelfColOpenUMLFeV1::class),
            new ClassMap('serviceTiresOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceTiresOpenUMLFeV1::class),
            new ClassMap('serviceTiresExportOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceTiresExportOpenUMLFeV1::class),
            new ClassMap('authDataV1', \GTools\Dpd\Soap\Types\AuthDataV1::class),
            new ClassMap('generatePackagesNumbersV1Response', \GTools\Dpd\Soap\Types\GeneratePackagesNumbersV1Response::class),
            new ClassMap('packagesGenerationResponseV1', \GTools\Dpd\Soap\Types\PackagesGenerationResponseV1::class),
            new ClassMap('sessionPGRV1', \GTools\Dpd\Soap\Types\SessionPGRV1::class),
            new ClassMap('packagePGRV1', \GTools\Dpd\Soap\Types\PackagePGRV1::class),
            new ClassMap('invalidFieldPGRV1', \GTools\Dpd\Soap\Types\InvalidFieldPGRV1::class),
            new ClassMap('parcelPGRV1', \GTools\Dpd\Soap\Types\ParcelPGRV1::class),
            new ClassMap('DPDServiceException', \GTools\Dpd\Soap\Types\DPDServiceException::class),
            new ClassMap('packagesPickupCallV4', \GTools\Dpd\Soap\Types\PackagesPickupCallV4Request::class),
            new ClassMap('dpdPickupCallParamsV3', \GTools\Dpd\Soap\Types\DpdPickupCallParamsV3::class),
            new ClassMap('pickupCallSimplifiedDetailsDPPV1', \GTools\Dpd\Soap\Types\PickupCallSimplifiedDetailsDPPV1::class),
            new ClassMap('pickupPackagesParamsDPPV1', \GTools\Dpd\Soap\Types\PickupPackagesParamsDPPV1::class),
            new ClassMap('pickupCustomerDPPV1', \GTools\Dpd\Soap\Types\PickupCustomerDPPV1::class),
            new ClassMap('pickupPayerDPPV1', \GTools\Dpd\Soap\Types\PickupPayerDPPV1::class),
            new ClassMap('pickupSenderDPPV1', \GTools\Dpd\Soap\Types\PickupSenderDPPV1::class),
            new ClassMap('packagesPickupCallV4Response', \GTools\Dpd\Soap\Types\PackagesPickupCallV4Response::class),
            new ClassMap('packagesPickupCallResponseV3', \GTools\Dpd\Soap\Types\PackagesPickupCallResponseV3::class),
            new ClassMap('statusInfoPCRV2', \GTools\Dpd\Soap\Types\StatusInfoPCRV2::class),
            new ClassMap('errorDetailsPCRV2', \GTools\Dpd\Soap\Types\ErrorDetailsPCRV2::class),
            new ClassMap('packagesPickupCallV3', \GTools\Dpd\Soap\Types\PackagesPickupCallV3Request::class),
            new ClassMap('packagesPickupCallV3Response', \GTools\Dpd\Soap\Types\PackagesPickupCallV3Response::class),
            new ClassMap('getCourierOrderAvailabilityV1', \GTools\Dpd\Soap\Types\GetCourierOrderAvailabilityV1Request::class),
            new ClassMap('senderPlaceV1', \GTools\Dpd\Soap\Types\SenderPlaceV1::class),
            new ClassMap('getCourierOrderAvailabilityV1Response', \GTools\Dpd\Soap\Types\GetCourierOrderAvailabilityV1Response::class),
            new ClassMap('getCourierOrderAvailabilityResponseV1', \GTools\Dpd\Soap\Types\GetCourierOrderAvailabilityResponseV1::class),
            new ClassMap('courierOrderAvailabilityRangeV1', \GTools\Dpd\Soap\Types\CourierOrderAvailabilityRangeV1::class),
            new ClassMap('Exception', \GTools\Dpd\Soap\Types\Exception::class),
            new ClassMap('packagesPickupCallV2', \GTools\Dpd\Soap\Types\PackagesPickupCallV2Request::class),
            new ClassMap('dpdPickupCallParamsV2', \GTools\Dpd\Soap\Types\DpdPickupCallParamsV2::class),
            new ClassMap('packagesPickupCallV2Response', \GTools\Dpd\Soap\Types\PackagesPickupCallV2Response::class),
            new ClassMap('packagesPickupCallResponseV2', \GTools\Dpd\Soap\Types\PackagesPickupCallResponseV2::class),
            new ClassMap('generatePackagesNumbersV4', \GTools\Dpd\Soap\Types\GeneratePackagesNumbersV4Request::class),
            new ClassMap('openUMLFeV3', \GTools\Dpd\Soap\Types\OpenUMLFeV3::class),
            new ClassMap('packageOpenUMLFeV3', \GTools\Dpd\Soap\Types\PackageOpenUMLFeV3::class),
            new ClassMap('servicesOpenUMLFeV4', \GTools\Dpd\Soap\Types\ServicesOpenUMLFeV4::class),
            new ClassMap('serviceFlagOpenUMLF', \GTools\Dpd\Soap\Types\ServiceFlagOpenUMLF::class),
            new ClassMap('serviceDpdPickupOpenUMLFeV1', \GTools\Dpd\Soap\Types\ServiceDpdPickupOpenUMLFeV1::class),
            new ClassMap('serviceDutyOpenUMLeFV2', \GTools\Dpd\Soap\Types\ServiceDutyOpenUMLeFV2::class),
            new ClassMap('generatePackagesNumbersV4Response', \GTools\Dpd\Soap\Types\GeneratePackagesNumbersV4Response::class),
            new ClassMap('packagesGenerationResponseV2', \GTools\Dpd\Soap\Types\PackagesGenerationResponseV2::class),
            new ClassMap('sessionPGRV2', \GTools\Dpd\Soap\Types\SessionPGRV2::class),
            new ClassMap('packagePGRV2', \GTools\Dpd\Soap\Types\PackagePGRV2::class),
            new ClassMap('ValidationDetails', \GTools\Dpd\Soap\Types\ValidationDetails::class),
            new ClassMap('validationInfoPGRV2', \GTools\Dpd\Soap\Types\ValidationInfoPGRV2::class),
            new ClassMap('parcelPGRV2', \GTools\Dpd\Soap\Types\ParcelPGRV2::class),
            new ClassMap('packagesPickupCallV1', \GTools\Dpd\Soap\Types\PackagesPickupCallV1Request::class),
            new ClassMap('dpdPickupCallParamsV1', \GTools\Dpd\Soap\Types\DpdPickupCallParamsV1::class),
            new ClassMap('contactInfoDPPV1', \GTools\Dpd\Soap\Types\ContactInfoDPPV1::class),
            new ClassMap('pickupAddressDSPV1', \GTools\Dpd\Soap\Types\PickupAddressDSPV1::class),
            new ClassMap('protocolDPPV1', \GTools\Dpd\Soap\Types\ProtocolDPPV1::class),
            new ClassMap('packagesPickupCallV1Response', \GTools\Dpd\Soap\Types\PackagesPickupCallV1Response::class),
            new ClassMap('packagesPickupCallResponseV1', \GTools\Dpd\Soap\Types\PackagesPickupCallResponseV1::class),
            new ClassMap('protocolPCRV1', \GTools\Dpd\Soap\Types\ProtocolPCRV1::class),
            new ClassMap('statusInfoPCRV1', \GTools\Dpd\Soap\Types\StatusInfoPCRV1::class),
            new ClassMap('generatePackagesNumbersV2', \GTools\Dpd\Soap\Types\GeneratePackagesNumbersV2Request::class),
            new ClassMap('generatePackagesNumbersV2Response', \GTools\Dpd\Soap\Types\GeneratePackagesNumbersV2Response::class),
            new ClassMap('appendParcelsToPackageV1', \GTools\Dpd\Soap\Types\AppendParcelsToPackageV1Request::class),
            new ClassMap('parcelsAppendV1', \GTools\Dpd\Soap\Types\ParcelsAppendV1::class),
            new ClassMap('parcelsAppendSearchCriteriaPAV1', \GTools\Dpd\Soap\Types\ParcelsAppendSearchCriteriaPAV1::class),
            new ClassMap('parcelAppendPAV1', \GTools\Dpd\Soap\Types\ParcelAppendPAV1::class),
            new ClassMap('appendParcelsToPackageV1Response', \GTools\Dpd\Soap\Types\AppendParcelsToPackageV1Response::class),
            new ClassMap('parcelsAppendResponseV1', \GTools\Dpd\Soap\Types\ParcelsAppendResponseV1::class),
            new ClassMap('invalidFieldPAV1', \GTools\Dpd\Soap\Types\InvalidFieldPAV1::class),
            new ClassMap('parcelsAppendParcelPAV1', \GTools\Dpd\Soap\Types\ParcelsAppendParcelPAV1::class),
            new ClassMap('generatePackagesNumbersV3', \GTools\Dpd\Soap\Types\GeneratePackagesNumbersV3Request::class),
            new ClassMap('openUMLFeV2', \GTools\Dpd\Soap\Types\OpenUMLFeV2::class),
            new ClassMap('packageOpenUMLFeV2', \GTools\Dpd\Soap\Types\PackageOpenUMLFeV2::class),
            new ClassMap('servicesOpenUMLFeV3', \GTools\Dpd\Soap\Types\ServicesOpenUMLFeV3::class),
            new ClassMap('generatePackagesNumbersV3Response', \GTools\Dpd\Soap\Types\GeneratePackagesNumbersV3Response::class),
            new ClassMap('importDeliveryBusinessEventV1', \GTools\Dpd\Soap\Types\ImportDeliveryBusinessEventV1Request::class),
            new ClassMap('dpdParcelBusinessEventV1', \GTools\Dpd\Soap\Types\DpdParcelBusinessEventV1::class),
            new ClassMap('dpdParcelBusinessEventDataV1', \GTools\Dpd\Soap\Types\DpdParcelBusinessEventDataV1::class),
            new ClassMap('importDeliveryBusinessEventV1Response', \GTools\Dpd\Soap\Types\ImportDeliveryBusinessEventV1Response::class),
            new ClassMap('importDeliveryBusinessEventResponseV1', \GTools\Dpd\Soap\Types\ImportDeliveryBusinessEventResponseV1::class),
            new ClassMap('DeniedAccessWSException', \GTools\Dpd\Soap\Types\DeniedAccessWSException::class),
            new ClassMap('SchemaValidationException', \GTools\Dpd\Soap\Types\SchemaValidationException::class),
            new ClassMap('generateSpedLabelsV1', \GTools\Dpd\Soap\Types\GenerateSpedLabelsV1Request::class),
            new ClassMap('dpdServicesParamsV1', \GTools\Dpd\Soap\Types\DpdServicesParamsV1::class),
            new ClassMap('sessionDSPV1', \GTools\Dpd\Soap\Types\SessionDSPV1::class),
            new ClassMap('packageDSPV1', \GTools\Dpd\Soap\Types\PackageDSPV1::class),
            new ClassMap('parcelDSPV1', \GTools\Dpd\Soap\Types\ParcelDSPV1::class),
            new ClassMap('generateSpedLabelsV1Response', \GTools\Dpd\Soap\Types\GenerateSpedLabelsV1Response::class),
            new ClassMap('documentGenerationResponseV1', \GTools\Dpd\Soap\Types\DocumentGenerationResponseV1::class),
            new ClassMap('sessionDGRV1', \GTools\Dpd\Soap\Types\SessionDGRV1::class),
            new ClassMap('packageDGRV1', \GTools\Dpd\Soap\Types\PackageDGRV1::class),
            new ClassMap('parcelDGRV1', \GTools\Dpd\Soap\Types\ParcelDGRV1::class),
            new ClassMap('statusInfoDGRV1', \GTools\Dpd\Soap\Types\StatusInfoDGRV1::class),
            new ClassMap('findPostalCodeV1', \GTools\Dpd\Soap\Types\FindPostalCodeV1Request::class),
            new ClassMap('postalCodeV1', \GTools\Dpd\Soap\Types\PostalCodeV1::class),
            new ClassMap('findPostalCodeV1Response', \GTools\Dpd\Soap\Types\FindPostalCodeV1Response::class),
            new ClassMap('findPostalCodeResponseV1', \GTools\Dpd\Soap\Types\FindPostalCodeResponseV1::class),
            new ClassMap('generateProtocolV1', \GTools\Dpd\Soap\Types\GenerateProtocolV1Request::class),
            new ClassMap('generateProtocolV1Response', \GTools\Dpd\Soap\Types\GenerateProtocolV1Response::class),
            new ClassMap('generateProtocolsWithDestinationsV2', \GTools\Dpd\Soap\Types\GenerateProtocolsWithDestinationsV2Request::class),
            new ClassMap('dpdServicesParamsV2', \GTools\Dpd\Soap\Types\DpdServicesParamsV2::class),
            new ClassMap('DeliveryDestinations', \GTools\Dpd\Soap\Types\DeliveryDestinations::class),
            new ClassMap('sessionDSPV2', \GTools\Dpd\Soap\Types\SessionDSPV2::class),
            new ClassMap('packageDSPV2', \GTools\Dpd\Soap\Types\PackageDSPV2::class),
            new ClassMap('parcelDSPV2', \GTools\Dpd\Soap\Types\ParcelDSPV2::class),
            new ClassMap('pickupAddressDSPV2', \GTools\Dpd\Soap\Types\PickupAddressDSPV2::class),
            new ClassMap('deliveryDestination', \GTools\Dpd\Soap\Types\DeliveryDestination::class),
            new ClassMap('DepotList', \GTools\Dpd\Soap\Types\DepotList::class),
            new ClassMap('protocolDepot', \GTools\Dpd\Soap\Types\ProtocolDepot::class),
            new ClassMap('generateProtocolsWithDestinationsV2Response', \GTools\Dpd\Soap\Types\GenerateProtocolsWithDestinationsV2Response::class),
            new ClassMap('documentGenerationResponseV2', \GTools\Dpd\Soap\Types\DocumentGenerationResponseV2::class),
            new ClassMap('DestinationDataList', \GTools\Dpd\Soap\Types\DestinationDataList::class),
            new ClassMap('destinationsData', \GTools\Dpd\Soap\Types\DestinationsData::class),
            new ClassMap('nonMatchingData', \GTools\Dpd\Soap\Types\NonMatchingData::class),
            new ClassMap('sessionDGRV2', \GTools\Dpd\Soap\Types\SessionDGRV2::class),
            new ClassMap('packageDGRV2', \GTools\Dpd\Soap\Types\PackageDGRV2::class),
            new ClassMap('parcelDGRV2', \GTools\Dpd\Soap\Types\ParcelDGRV2::class),
            new ClassMap('statusInfoDGRV2', \GTools\Dpd\Soap\Types\StatusInfoDGRV2::class),
            new ClassMap('generateSpedLabelsV4', \GTools\Dpd\Soap\Types\GenerateSpedLabelsV4Request::class),
            new ClassMap('generateSpedLabelsV4Response', \GTools\Dpd\Soap\Types\GenerateSpedLabelsV4Response::class),
            new ClassMap('generateProtocolsWithDestinationsV1', \GTools\Dpd\Soap\Types\GenerateProtocolsWithDestinationsV1Request::class),
            new ClassMap('generateProtocolsWithDestinationsV1Response', \GTools\Dpd\Soap\Types\GenerateProtocolsWithDestinationsV1Response::class),
            new ClassMap('generateProtocolV2', \GTools\Dpd\Soap\Types\GenerateProtocolV2Request::class),
            new ClassMap('generateProtocolV2Response', \GTools\Dpd\Soap\Types\GenerateProtocolV2Response::class),
            new ClassMap('generateSpedLabelsV3', \GTools\Dpd\Soap\Types\GenerateSpedLabelsV3Request::class),
            new ClassMap('generateSpedLabelsV3Response', \GTools\Dpd\Soap\Types\GenerateSpedLabelsV3Response::class),
            new ClassMap('generateSpedLabelsV2', \GTools\Dpd\Soap\Types\GenerateSpedLabelsV2Request::class),
            new ClassMap('generateSpedLabelsV2Response', \GTools\Dpd\Soap\Types\GenerateSpedLabelsV2Response::class),
            new ClassMap('importPackagesXV1', \GTools\Dpd\Soap\Types\ImportPackagesXV1Request::class),
            new ClassMap('importPackagesXV1Response', \GTools\Dpd\Soap\Types\ImportPackagesXV1Response::class),
            new ClassMap('getEventsForCustomerV4', \GTools\Dpd\Soap\Types\GetEventsForCustomerV4Request::class),
            new ClassMap('getEventsForCustomerV4Response', \GTools\Dpd\Soap\Types\GetEventsForCustomerV4Response::class),
            new ClassMap('customerEventsResponseV2', \GTools\Dpd\Soap\Types\CustomerEventsResponseV2::class),
            new ClassMap('customerEventV2', \GTools\Dpd\Soap\Types\CustomerEventV2::class),
            new ClassMap('customerEventDataV2', \GTools\Dpd\Soap\Types\CustomerEventDataV2::class),
            new ClassMap('getEventsForCustomerV3', \GTools\Dpd\Soap\Types\GetEventsForCustomerV3Request::class),
            new ClassMap('getEventsForCustomerV3Response', \GTools\Dpd\Soap\Types\GetEventsForCustomerV3Response::class),
            new ClassMap('getEventsForCustomerV2', \GTools\Dpd\Soap\Types\GetEventsForCustomerV2Request::class),
            new ClassMap('getEventsForCustomerV2Response', \GTools\Dpd\Soap\Types\GetEventsForCustomerV2Response::class),
            new ClassMap('customerEventsResponseV1', \GTools\Dpd\Soap\Types\CustomerEventsResponseV1::class),
            new ClassMap('customerEventV1', \GTools\Dpd\Soap\Types\CustomerEventV1::class),
            new ClassMap('getEventsForCustomerV1', \GTools\Dpd\Soap\Types\GetEventsForCustomerV1Request::class),
            new ClassMap('getEventsForCustomerV1Response', \GTools\Dpd\Soap\Types\GetEventsForCustomerV1Response::class),
            new ClassMap('getEventsForWaybillV1', \GTools\Dpd\Soap\Types\GetEventsForWaybillV1Request::class),
            new ClassMap('getEventsForWaybillV1Response', \GTools\Dpd\Soap\Types\GetEventsForWaybillV1Response::class),
            new ClassMap('customerEventsResponseV3', \GTools\Dpd\Soap\Types\CustomerEventsResponseV3::class),
            new ClassMap('customerEventV3', \GTools\Dpd\Soap\Types\CustomerEventV3::class),
            new ClassMap('customerEventDataV3', \GTools\Dpd\Soap\Types\CustomerEventDataV3::class),
            new ClassMap('markEventsAsProcessedV1Response', \GTools\Dpd\Soap\Types\MarkEventsAsProcessedV1Response::class),
        ]);
    }

    private function getAuthDataStruct() : AuthDataV1
    {
        $authData = new AuthDataV1();
        $authData->setLogin($this->login);
        $authData->setPassword($this->password);
        $authData->setMasterFid($this->masterFid);

        return $authData;
    }

    /**
     * @param FindPostalCodeRequest $request
     *
     * @return FindPostalCodeResponse
     */
    public function findPostalCode(FindPostalCodeRequest $request): FindPostalCodeResponse
    {
        $payload = $request->toPayload();
        $payload->setAuthData($this->getAuthDataStruct());
        $response = $this->obtainPackageServiceClient()->findPostalCodeV1($payload);

        return FindPostalCodeResponse::from($response);
    }

    /**
     * @param GeneratePackageNumbersRequest $request
     *
     * @return GeneratePackageNumbersResponse
     */
    public function generatePackageNumbers(GeneratePackageNumbersRequest $request): GeneratePackageNumbersResponse
    {
        $payload = $request->toPayload();
        $payload->setAuthData($this->getAuthDataStruct());
        $response = $this->obtainPackageServiceClient()->generatePackagesNumbersV4($payload);

        return GeneratePackageNumbersResponse::from($response);
    }

    /**
     * @param GenerateLabelsRequest $request
     *
     * @return GenerateLabelsResponse
     */
    public function generateLabels(GenerateLabelsRequest $request): GenerateLabelsResponse
    {
        $payload = $request->toPayload();
        $payload->setAuthData($this->getAuthDataStruct());
        $response = $this->obtainPackageServiceClient()->generateSpedLabelsV1($payload);

        return GenerateLabelsResponse::from($response);
    }

    /**
     * @param GenerateProtocolRequest $request
     *
     * @return GenerateProtocolResponse
     */
    public function generateProtocol(GenerateProtocolRequest $request): GenerateProtocolResponse
    {
        $payload = $request->toPayload();
        $payload->setAuthData($this->getAuthDataStruct());
        $response = $this->obtainPackageServiceClient()->generateProtocolV2($payload);

        return GenerateProtocolResponse::from($response);
    }

    /**
     * @param GetCourierAvailabilityRequest $request
     *
     * @return GetCourierAvailabilityResponse
     */
    public function getCourierAvailability(GetCourierAvailabilityRequest $request): GetCourierAvailabilityResponse
    {
        $payload = $request->toPayload();
        $payload->setAuthData($this->getAuthDataStruct());
        $response = $this->obtainPackageServiceClient()->getCourierOrderAvailabilityV1($payload);

        return GetCourierAvailabilityResponse::from($response);
    }

    /**
     * @param CollectionOrderRequest $request
     *
     * @return CollectionOrderResponse
     */
    public function collectionOrder(CollectionOrderRequest $request): CollectionOrderResponse
    {
        $payload = $request->toPayload();
        $payload->setAuthDataV1($this->getAuthDataStruct());
        $response = $this->obtainAppServiceClient()->importPackagesXV1($payload);

        return CollectionOrderResponse::from($response);
    }

    /**
     * @param GetParcelTrackingRequest $request
     *
     * @return GetParcelTrackingResponse
     */
    public function getParcelTracking(GetParcelTrackingRequest $request): GetParcelTrackingResponse
    {
        $payload = $request->toPayload();
        $payload->setAuthData($this->getAuthDataStruct());
        $response = $this->obtainInfoServiceClient()->getEventsForWaybillV1($payload);

        return GetParcelTrackingResponse::from($response);
    }
}
