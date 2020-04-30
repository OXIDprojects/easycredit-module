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
 * Class oxpsEasyCreditInitializeRequestBuilderTest
 */
class oxpsEasyCreditInitializeRequestBuilderTest extends OxidTestCase
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

    public function testGetInitializationDataWithBasketItems()
    {
        $articleIds = array('1000', '2000');
        $articles   = array(
            oxNew('oxArticle'),
            oxNew('oxArticle')
        );

        $basketContents = array();
        foreach ($articles as $i => $article) {
            $id = $articleIds[$i];
            $article->setId($id);

            $basketItem = $this->getMock('oxbasketitem', array('getArticle'));
            $basketItem->expects($this->any())->method('getArticle')->willReturn($article);
            $basketContents[$id] = $basketItem;
        }

        $basket = $this->getMock('oxBasket', array('getBasketArticles', 'getContents'));
        $basket->expects($this->any())->method('getBasketArticles')->willReturn($basketContents);
        $basket->expects($this->any())->method('getContents')->willReturn($basketContents);

        $user = oxNew('oxUser');

        $rb = oxNew('oxpsEasyCreditInitializeRequestBuilder');
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = oxRegistry::getConfig();

        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected = array(
            'integrationsart'         => 'PAYMENT_PAGE',
            'laufzeit'                => 36,
            'ruecksprungadressen'     => array(
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=oxpsEasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ),
            'kontakt'                 => array(
                'email' => null
            ),
            'risikorelevanteAngaben'  => array(
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ),
            'technischeShopparameter' => array(
                'shopSystemHersteller' => 'OXID eShop '
            ),
            'warenkorbinfos'          => array(
                0 => array(
                    'produktbezeichnung' => null,
                    'menge'              => 0.0,
                    'preis'              => '',
                    'hersteller'         => '',
                    'produktkategorie'   => 'Bindungen',
                    'artikelnummern'     => array(
                        0 => array(
                            'nummerntyp' => 'GTIN',
                            'nummer'     => null
                        )
                    )
                ),
                1 => array(
                    'produktbezeichnung' => null,
                    'menge'              => 0.0,
                    'preis'              => '',
                    'hersteller'         => '',
                    'produktkategorie'   => 'Bindungen',
                    'artikelnummern'     => array(
                        0 => array(
                            'nummerntyp' => 'GTIN',
                            'nummer'     => null
                        )
                    )
                )
            )
        );
        $this->assertEquals($expected, $rb->getInitializationData());
    }

    public function testGetInitializationDataWithRegisteredUserWithGroups()
    {
        $basket = oxNew('oxBasket');

        $groupIds = array('dummy', 'oxidnotyetordered');
        $groups   = array();
        foreach ($groupIds as $groupId) {
            $group = oxNew('oxGroups');
            $group->setId($groupId);
            $groups[$groupId] = $group;
        }

        $user = $this->getMock('oxUser', array('getUserGroups'));
        $user->expects($this->any())->method('getUserGroups')->willReturn($groups);
        $user->oxuser__oxpassword = new oxField('password');

        $rb = oxNew('oxpsEasyCreditInitializeRequestBuilder');
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = oxRegistry::getConfig();

        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected = array(
            'integrationsart'         => 'PAYMENT_PAGE',
            'laufzeit'                => 36,
            'ruecksprungadressen'     => array(
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=oxpsEasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ),
            'kontakt'                 => array(
                'email' => null
            ),
            'risikorelevanteAngaben'  => array(
                'bestellungErfolgtUeberLogin' => true,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ),
            'technischeShopparameter' => array(
                'shopSystemHersteller' => 'OXID eShop '
            )
        );
        $this->assertEquals($expected, $rb->getInitializationData());
    }

    public function testGetInitializationDataWithSalutationMapping()
    {
        $basket = oxNew('oxBasket');

        $user                = $this->getMock('oxUser', array('getUserGroups'));
        $user->oxuser__oxsal = new oxField('MRS');

        $rb = oxNew('oxpsEasyCreditInitializeRequestBuilder');
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = oxRegistry::getConfig();

        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected = array(
            'integrationsart'         => 'PAYMENT_PAGE',
            'laufzeit'                => 36,
            'ruecksprungadressen'     => array(
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=oxpsEasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ),
            'personendaten'           => array(
                'anrede' => 'FRAU'
            ),
            'kontakt'                 => array(
                'email' => null
            ),
            'risikorelevanteAngaben'  => array(
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ),
            'technischeShopparameter' => array(
                'shopSystemHersteller' => 'OXID eShop '
            )
        );
        $this->assertEquals($expected, $rb->getInitializationData());
    }

    public function testGetInitializationDataWithBirthday()
    {
        $basket = oxNew('oxBasket');

        $user                      = $this->getMock('oxUser', array('getUserGroups'));
        $user->oxuser__oxbirthdate = new oxField('1985-07-13');

        $rb = oxNew('oxpsEasyCreditInitializeRequestBuilder');
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = oxRegistry::getConfig();

        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected = array(
            'integrationsart'         => 'PAYMENT_PAGE',
            'laufzeit'                => 36,
            'ruecksprungadressen'     => array(
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=oxpsEasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ),
            'personendaten'           => array(
                'geburtsdatum' => '1985-07-13'
            ),
            'kontakt'                 => array(
                'email' => null
            ),
            'risikorelevanteAngaben'  => array(
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ),
            'technischeShopparameter' => array(
                'shopSystemHersteller' => 'OXID eShop '
            )
        );
        $this->assertEquals($expected, $rb->getInitializationData());
    }

    public function testGetInitializationDataWithInvalidBirthday()
    {
        $basket = oxNew('oxBasket');

        $user                      = $this->getMock('oxUser', array('getUserGroups'));
        $user->oxuser__oxbirthdate = new oxField('12345');

        $rb = oxNew('oxpsEasyCreditInitializeRequestBuilder');
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = oxRegistry::getConfig();

        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected = array(
            'integrationsart'         => 'PAYMENT_PAGE',
            'laufzeit'                => 36,
            'ruecksprungadressen'     => array(
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=oxpsEasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ),
            'kontakt'                 => array(
                'email' => null
            ),
            'risikorelevanteAngaben'  => array(
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ),
            'technischeShopparameter' => array(
                'shopSystemHersteller' => 'OXID eShop '
            )
        );
        $this->assertEquals($expected, $rb->getInitializationData());
    }

    public function testGetInitializationDataWithDeliveryAddress()
    {
        $basket = oxNew('oxBasket');

        $user = $this->getMock('oxUser', array('getUserGroups'));

        $deliveryAddress = oxNew('oxaddress');

        $rb = oxNew('oxpsEasyCreditInitializeRequestBuilder');
        $rb->setBasket($basket);
        $rb->setUser($user);
        $rb->setShippingAddress($deliveryAddress);

        $config = oxRegistry::getConfig();

        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected = array(
            'integrationsart'         => 'PAYMENT_PAGE',
            'laufzeit'                => 36,
            'ruecksprungadressen'     => array(
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=oxpsEasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ),
            'kontakt'                 => array(
                'email' => null
            ),
            'risikorelevanteAngaben'  => array(
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ),
            'technischeShopparameter' => array(
                'shopSystemHersteller' => 'OXID eShop '
            )
        );
        $this->assertEquals($expected, $rb->getInitializationData());
    }

    public function testGetInitializationDataWithCountry()
    {
        $basket = oxNew('oxBasket');

        $user                         = $this->getMock('oxUser', array('getUserGroups'));
        $user->oxuser__oxcountryid = new oxField('a7c40f631fc920687.20179984');

        $rb = oxNew('oxpsEasyCreditInitializeRequestBuilder');
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = oxRegistry::getConfig();

        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected = array(
            'integrationsart'         => 'PAYMENT_PAGE',
            'laufzeit'                => 36,
            'ruecksprungadressen'     => array(
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=oxpsEasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ),
            'kontakt'                 => array(
                'email' => null
            ),
            'risikorelevanteAngaben'  => array(
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ),
            'technischeShopparameter' => array(
                'shopSystemHersteller' => 'OXID eShop '
            ),
            'rechnungsadresse' => array(
                'land' => 'DE'
            ),
            'lieferadresse' => array(
                'land' => 'DE'
            )
        );
        $this->assertEquals($expected, $rb->getInitializationData());
    }

    public function testGetInitializationDataWithValidPhoneNumber()
    {
        $basket = oxNew('oxBasket');

        $user                         = $this->getMock('oxUser', array('getUserGroups'));
        $user->oxuser__oxfon = new oxField('+49 123-1234');

        $rb = oxNew('oxpsEasyCreditInitializeRequestBuilder');
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = oxRegistry::getConfig();

        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected = array(
            'integrationsart'         => 'PAYMENT_PAGE',
            'laufzeit'                => 36,
            'ruecksprungadressen'     => array(
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=oxpsEasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ),
            'kontakt'                 => array(
                'email' => null,
                'mobilfunknummer' => '+49 123-1234',
                'pruefungMobilfunknummerUebergehen' => true
            ),
            'risikorelevanteAngaben'  => array(
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ),
            'technischeShopparameter' => array(
                'shopSystemHersteller' => 'OXID eShop '
            ),
            'weitereKaeuferangaben' => array(
                'telefonnummer' => '+49 123-1234'
            )
        );
        $this->assertEquals($expected, $rb->getInitializationData());
    }

    public function testGetInitializationDataWithDeps()
    {
        $manufacturer = oxNew('oxmanufacturer');
        $manufacturer->setId('1000');
        $manufacturer->oxmanufacturer__oxtitle = new oxField('testmanufacturer');

        $category = oxNew('oxcategory');
        $category->setId('1000');
        $category->oxcategories__oxtitle = new oxField('testcategory');

        $unitPrice = oxNew('oxprice');
        $unitPrice->setPrice(250.72);

        $articleIds = array('1000', '2000');

        $basketContents = array();
        foreach ($articleIds as $i => $articleId) {
            $article = $this->getMock('oxarticle', array('getManufacturer', 'getCategory'));
            $article->expects($this->any())->method('getManufacturer')->willReturn($manufacturer);
            $article->expects($this->any())->method('getCategory')->willReturn($category);
            $article->setId($articleId);

            $basketItem = $this->getMock('oxbasketitem', array('getArticle', 'getUnitPrice'));
            $basketItem->expects($this->any())->method('getArticle')->willReturn($article);
            $basketItem->expects($this->any())->method('getUnitPrice')->willReturn($unitPrice);
            $basketContents[$articleId] = $basketItem;
        }

        $basket = $this->getMock('oxBasket', array('getBasketArticles', 'getContents'));
        $basket->expects($this->any())->method('getBasketArticles')->willReturn($basketContents);
        $basket->expects($this->any())->method('getContents')->willReturn($basketContents);

        $user = oxNew('oxUser');

        $rb = oxNew('oxpsEasyCreditInitializeRequestBuilder');
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = oxRegistry::getConfig();
        
        $sslShopUrl = oxpsEasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected = array(
            'integrationsart'         => 'PAYMENT_PAGE',
            'laufzeit'                => 36,
            'ruecksprungadressen'     => array(
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=oxpsEasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ),
            'kontakt'                 => array(
                'email' => null
            ),
            'risikorelevanteAngaben'  => array(
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ),
            'technischeShopparameter' => array(
                'shopSystemHersteller' => 'OXID eShop '
            ),
            'warenkorbinfos'          => array(
                0 => array(
                    'produktbezeichnung' => null,
                    'menge'              => 0.0,
                    'preis' => 250.72,
                    'hersteller' => null,
                    'produktkategorie' => 'testcategory',
                    'artikelnummern'     => array(
                        0 => array(
                            'nummerntyp' => 'GTIN',
                            'nummer'     => null
                        )
                    )
                ),
                1 => array(
                    'produktbezeichnung' => null,
                    'menge'              => 0.0,
                    'preis' => 250.72,
                    'hersteller' => null,
                    'produktkategorie' => 'testcategory',
                    'artikelnummern'     => array(
                        0 => array(
                            'nummerntyp' => 'GTIN',
                            'nummer'     => null
                        )
                    )
                )
            )
        );
        $this->assertEquals($expected, $rb->getInitializationData());
    }
}
