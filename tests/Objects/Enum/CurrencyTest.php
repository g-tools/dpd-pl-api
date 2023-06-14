<?php

namespace GTools\DpdTests\Objects\Enum;

use PHPUnit\Framework\TestCase;
use GTools\Dpd\Objects\Enum\Currency;

class CurrencyTest extends TestCase
{
    /**
     * @dataProvider knownCurrencies
     */
    public function testCreation($currencyCode)
    {
        $currency = Currency::$currencyCode();
        self::assertEquals($currencyCode, (string)$currency);
    }

    public function knownCurrencies()
    {
        return [
            ['PLN'],
            ['EUR'],
            ['USD'],
            ['CHF'],
            ['SEK'],
            ['NOK'],
        ];
    }
}
