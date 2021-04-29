<?php

namespace OxidProfessionalServices\EasyCredit\Tests\Unit\Application\Core\Api;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidProfessionalServices\EasyCredit\Core\Api\EasyCreditResponseValidator;

/**
 * Class EasyCreditResponseValidatorTest
 */
class EasyCreditResponseValidatorTest extends UnitTestCase
{
    /**
     * Set up test environment
     *
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testValidateMissingScheme()
    {
        $validator = oxNew(EasyCreditResponseValidator::class, []);
        $this->assertNull($validator->validate(new \stdClass()));
    }

    public function testValidateWithValidationSchemeValid()
    {
        $scheme = [
            [
                "fieldname"     => "ergebnis",
                "required"      => true,
                "requiredValue" => "success"
            ]
        ];

        $response           = new \stdClass();
        $response->ergebnis = 'success';

        $validator = oxNew(EasyCreditResponseValidator::class, $scheme);
        $this->assertNull($validator->validate($response));
    }

    public function testValidateWithValidationSchemeInvalid()
    {
        $this->expectException(oxpsEasyCreditValidationException::class);
        $scheme = array(
            array(
                "fieldname"     => "ergebnis",
                "required"      => true,
                "requiredValue" => success
            )
        );

        $response           = new \stdClass();
        $response->ergebnis = 'failure';

        $validator = oxNew(EasyCreditResponseValidator::class, $scheme);
        $validator->validate($response);
    }
}