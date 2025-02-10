<?php

declare(strict_types=1);

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace Unit\Core\Dto;

use OxidProfessionalServices\EasyCredit\Core\Dto\EasyCreditStorage;
use PHPUnit\Framework\TestCase;

final class EasyCreditStorageTest extends TestCase
{
    /**
     * Verifies that the return value of the getStorageExpiredTimeRange function is correct.
     */
    public function testGetStorageExpiredTimeRange()
    {
        $easyCreditStorage = new EasyCreditStorage('tbVorgangskennung', 'fachlicheVorgangskennung', 'authorizationHash', 100.0);
        $this->assertEquals(60 * 29, $this->invokeMethod($easyCreditStorage, 'getStorageExpiredTimeRange'));
    }

    /**
     * Invoke protected/private method of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return   mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
    public function testHasExpiredAfterCreation(): void
    {
        $oTest = new EasyCreditStorage(
            'TEST',
            'TEST',
            'TEST',
            450.0
        );

        $this->assertFalse($oTest->hasExpired());
    }

    /**
     * Verify that hasExpired correctly returns true if and only if
     * the last update occurred longer than the storage expiration range ago.
     */
    public function testHasExpired(): void
    {
        $easyCreditStorage = new EasyCreditStorage('tbVorgangskennung', 'fachlicheVorgangskennung', 'authorizationHash', 100.0);
        $lastUpdateProperty = (new \ReflectionClass($easyCreditStorage))->getProperty('lastUpdate');
        $lastUpdateProperty->setAccessible(true);

        // Test when lastUpdate is exactly at the expired time
        $lastUpdateProperty->setValue($easyCreditStorage, time() - $this->invokeMethod($easyCreditStorage, 'getStorageExpiredTimeRange'));
        $this->assertFalse($easyCreditStorage->hasExpired());

        // Test when lastUpdate is just before the expired time
        $lastUpdateProperty->setValue($easyCreditStorage, time() - $this->invokeMethod($easyCreditStorage, 'getStorageExpiredTimeRange') + 1);
        $this->assertFalse($easyCreditStorage->hasExpired());

        // Test when lastUpdate is just after the expired time
        $lastUpdateProperty->setValue($easyCreditStorage, time() - $this->invokeMethod($easyCreditStorage, 'getStorageExpiredTimeRange') - 1);
        $this->assertTrue($easyCreditStorage->hasExpired());

        // Test when lastUpdate is not set
        $lastUpdateProperty->setValue($easyCreditStorage, null);
        $this->assertTrue($easyCreditStorage->hasExpired());
    }
    public function testUserSetLastNameSuccessful()
    {
        $easyCreditStorage = new EasyCreditStorage(
            'TEST',
            'TEST',
            'TEST',
            450.0
        );
        $easyCreditStorage->setInterestAmount(100.01);
        $easyCreditStorage->setAllgemeineVorgangsdaten('TEST');
        $easyCreditStorage->setRatenplanTxt('TEST');
        $easyCreditStorage->setTilgungsplanTxt('TEST');
        $this->assertEquals(100.01, $easyCreditStorage->getInterestAmount());
        $this->assertEquals('TEST', $easyCreditStorage->getAllgemeineVorgangsdaten());
        $this->assertEquals('TEST', $easyCreditStorage->getRatenplanTxt());
        $this->assertEquals('TEST', $easyCreditStorage->getTilgungsplanTxt());
        $this->assertEquals(450.0, $easyCreditStorage->getAuthorizedAmount());
        $this->assertEquals('TEST', $easyCreditStorage->getAuthorizationHash());
        $this->assertEquals('TEST', $easyCreditStorage->getTbVorgangskennung());
        $this->assertEquals('TEST', $easyCreditStorage->getFachlicheVorgangskennung());
    }
}
