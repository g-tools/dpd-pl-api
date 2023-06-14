<?php

namespace GTools\Dpd\Request;

use GTools\Dpd\Objects\Package;
use GTools\Dpd\Request\Serializer\XmlPackagesSerializer;
use GTools\Dpd\Soap\Types\ImportPackagesXV1Request;

class CollectionOrderRequest
{
    /**
     * @var Package[]
     */
    private $packages;

    /**
     * @var XmlPackagesSerializer
     */
    private $serializer;

    /**
     * GeneratePackageNumbersRequest constructor.
     *
     * @param $packages
     */
    protected function __construct(array $packages)
    {
        $this->packages = $packages;
        $this->serializer = new XmlPackagesSerializer();
    }

    /**
     * @param Package $package
     *
     * @return CollectionOrderRequest
     */
    public static function fromPackage(Package $package): CollectionOrderRequest
    {
        return new static([$package]);
    }

    /**
     * @param Package[] $packages
     *
     * @return CollectionOrderRequest
     */
    public static function fromPackages(array $packages): CollectionOrderRequest
    {
        return new static($packages);
    }

    public function toPayload(): ImportPackagesXV1Request
    {
        $request = new ImportPackagesXV1Request();

        $xml = $this->serializer->serialize($this->packages);
        $request->setOpenUMLFXV2($xml);

        return $request;
    }
}
