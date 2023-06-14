<?php

namespace GTools\DpdTests\Api;

use PHPUnit\Framework\TestCase;
use GTools\Dpd\Api;

abstract class ApiIntegrationTestCase extends TestCase
{
    /**
     * @var Api
     */
    protected static $api;

    public static function setUpBeforeClass()
    {
        self::$api = new Api('test', 'thetu4Ee', 1495);
        static::$api->setSandboxMode(true);
    }

}
