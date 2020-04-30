<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @category      module
 * @package       easycredit
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

/**
 * Class oxpsEasyCreditResponseValidatorTest
 */
class oxpsEasyCreditResponseValidatorTest extends OxidTestCase
{
    /**
     * Set up test environment
     *
     * @return null
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Tear down test environment
     *
     * @return null
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    public function testValidateMissingScheme()
    {
        $validator = oxNew('oxpsEasyCreditResponseValidator', array());
        $this->assertNull($validator->validate(new stdClass()));
    }

    public function testValidateWithValidationSchemeValid()
    {
        $scheme = array(
            array(
                "fieldname"     => "ergebnis",
                "required"      => true,
                "requiredValue" => "success"
            )
        );

        $response           = new stdClass();
        $response->ergebnis = 'success';

        $validator = oxNew('oxpsEasyCreditResponseValidator', $scheme);
        $this->assertNull($validator->validate($response));
    }

    /**
     * @expectedException oxpsEasyCreditValidationException
     */
    public function testValidateWithValidationSchemeInvalid()
    {
        $scheme = array(
            array(
                "fieldname"     => "ergebnis",
                "required"      => true,
                "requiredValue" => success
            )
        );

        $response           = new stdClass();
        $response->ergebnis = 'failure';

        $validator = oxNew('oxpsEasyCreditResponseValidator', $scheme);
        $validator->validate($response);
    }
}