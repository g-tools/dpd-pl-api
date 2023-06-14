<?php

namespace GTools\Dpd\Response;


use GTools\Dpd\Objects\CollectionOrderedPackage;
use GTools\Dpd\Response\Deserializer\XmlCollectionOrderResponseDeserializer;
use GTools\Dpd\Soap\Types\ImportPackagesXV1Response;

class CollectionOrderResponse
{
    /**
     * @var CollectionOrderedPackage[]
     */
    private $collectionOrderedPackages;

    /**
     * CollectionOrderResponse constructor.
     * @param CollectionOrderedPackage[] $collectionOrderedPackages
     */
    public function __construct(array $collectionOrderedPackages)
    {
        $this->collectionOrderedPackages = $collectionOrderedPackages;
    }

    public static function from(ImportPackagesXV1Response $response)
    {
        $deserializer = new XmlCollectionOrderResponseDeserializer();
        return new self($deserializer->deserialize($response->getReturn()));
    }

    /**
     * @return CollectionOrderedPackage[]
     */
    public function getCollectionOrderedPackages(): array
    {
        return $this->collectionOrderedPackages;
    }


}