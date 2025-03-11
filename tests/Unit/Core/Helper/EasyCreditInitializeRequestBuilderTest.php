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
 * @package       easycredit-module
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Core\Helper;

use OxidEsales\Eshop\Application\Model\Address;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Application\Model\Groups;
use OxidEsales\Eshop\Application\Model\Manufacturer;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Price;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditApiConfig;
use OxidSolutionCatalysts\EasyCredit\Model\EasyCreditTradingApiAccess;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidSolutionCatalysts\EasyCredit\Core\Helper\EasyCreditInitializeRequestBuilder;

/**
 * Class EasyCreditInitializeRequestBuilderTest
 */
class EasyCreditInitializeRequestBuilderTest extends TestCase
{
    private $shopkennung = null;

    /**
     * Set up test environment
     *
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->shopkennung = 'test';
    }

    /**
     * Tear down test environment
     *
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetInitializationDataWithBasketItems(): void
    {
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);
        
        $articleIds = ['1000', '2000'];
        $articles   = [
            oxNew(Article::class),
            oxNew(Article::class)
        ];

        $basketContents = [];
        foreach ($articles as $i => $article) {
            $id = $articleIds[$i];
            $article->setId($id);

            $basketItem = $this->getMockBuilder(BasketItem::class)
                ->disableOriginalConstructor()
                ->setMethods(['getArticle'])
                ->getMock();
            $basketItem->expects($this->any())->method('getArticle')->willReturn($article);
            $basketContents[$id] = $basketItem;
        }

        $basket = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getBasketArticles',
                'getContents',
            ])
            ->getMock();
        $basket->expects($this->any())->method('getBasketArticles')->willReturn($basketContents);
        $basket->expects($this->any())->method('getContents')->willReturn($basketContents);

        $user = oxNew(User::class);

        $rb = oxNew(EasyCreditInitializeRequestBuilder::class);
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = Registry::getConfig();

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected   = [
            'integrationsart'         => 'PAYMENT_PAGE',
            'shopKennung'             => $this->shopkennung,
            'laufzeit'                => 36,
            'ruecksprungadressen'     => [
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ],
            'kontakt'                 => [
                'email' => null
            ],
            'risikorelevanteAngaben'  => [
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ],
            'technischeShopparameter' => [
                'shopSystemHersteller' => 'OXID eShop '
            ],
            'warenkorbinfos'          => [
                0 => [
                    'produktbezeichnung' => null,
                    'menge'              => 0.0,
                    'preis'              => '',
                    'hersteller'         => '',
                    'produktkategorie'   => 'Koffer',
                    'artikelnummern'     => [
                        0 => [
                            'nummerntyp' => 'GTIN',
                            'nummer'     => null
                        ]
                    ]
                ],
                1 => [
                    'produktbezeichnung' => null,
                    'menge'              => 0.0,
                    'preis'              => '',
                    'hersteller'         => '',
                    'produktkategorie'   => 'Koffer',
                    'artikelnummern'     => [
                        0 => [
                            'nummerntyp' => 'GTIN',
                            'nummer'     => null
                        ]
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, $rb->getInitializationData());

        UtilsObject::resetClassInstances();
    }

    public function testGetInitializationDataWithRegisteredUserWithGroups(): void
    {
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);

        $basket = oxNew(Basket::class);

        $groupIds = ['dummy', 'oxidnotyetordered'];
        $groups   = [];
        foreach ($groupIds as $groupId) {
            $group = oxNew(Groups::class);
            $group->setId($groupId);
            $groups[$groupId] = $group;
        }

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserGroups'])
            ->getMock();
        $user->expects($this->any())->method('getUserGroups')->willReturn($groups);
        $user->oxuser__oxpassword = new Field('password');

        $rb = oxNew(EasyCreditInitializeRequestBuilder::class);
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = Registry::getConfig();

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected   = [
            'integrationsart'         => 'PAYMENT_PAGE',
            'shopKennung'             => $this->shopkennung,
            'laufzeit'                => 36,
            'ruecksprungadressen'     => [
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ],
            'kontakt'                 => [
                'email' => null
            ],
            'risikorelevanteAngaben'  => [
                'bestellungErfolgtUeberLogin' => true,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ],
            'technischeShopparameter' => [
                'shopSystemHersteller' => 'OXID eShop '
            ]
        ];
        $this->assertEquals($expected, $rb->getInitializationData());

        UtilsObject::resetClassInstances();
    }

    public function testGetInitializationDataWithSalutationMapping(): void
    {
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);

        $basket = oxNew(Basket::class);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserGroups'])
            ->getMock();
        $user->oxuser__oxsal = new Field('MRS');

        $rb = oxNew(EasyCreditInitializeRequestBuilder::class);
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = Registry::getConfig();

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected   = [
            'integrationsart'         => 'PAYMENT_PAGE',
            'shopKennung'             => $this->shopkennung,
            'laufzeit'                => 36,
            'ruecksprungadressen'     => [
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ],
            'personendaten'           => [
                'anrede' => 'FRAU'
            ],
            'kontakt'                 => [
                'email' => null
            ],
            'risikorelevanteAngaben'  => [
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ],
            'technischeShopparameter' => [
                'shopSystemHersteller' => 'OXID eShop '
            ]
        ];
        $this->assertEquals($expected, $rb->getInitializationData());

        UtilsObject::resetClassInstances();
    }

    public function testGetInitializationDataWithBirthday(): void
    {
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);

        $basket = oxNew(Basket::class);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserGroups'])
            ->getMock();
        $user->oxuser__oxbirthdate = new Field('1985-07-13');

        $rb = oxNew(EasyCreditInitializeRequestBuilder::class);
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = Registry::getConfig();

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected   = [
            'integrationsart'         => 'PAYMENT_PAGE',
            'shopKennung'             => $this->shopkennung,
            'laufzeit'                => 36,
            'ruecksprungadressen'     => [
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ],
            'personendaten'           => [
                'geburtsdatum' => '1985-07-13'
            ],
            'kontakt'                 => [
                'email' => null
            ],
            'risikorelevanteAngaben'  => [
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ],
            'technischeShopparameter' => [
                'shopSystemHersteller' => 'OXID eShop '
            ]
        ];
        $this->assertEquals($expected, $rb->getInitializationData());

        UtilsObject::resetClassInstances();
    }

    public function testGetInitializationDataWithInvalidBirthday(): void
    {
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);

        $basket = oxNew(Basket::class);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserGroups'])
            ->getMock();
        $user->oxuser__oxbirthdate = new Field('12345');

        $rb = oxNew(EasyCreditInitializeRequestBuilder::class);
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = Registry::getConfig();

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected   = [
            'integrationsart'         => 'PAYMENT_PAGE',
            'shopKennung'             => $this->shopkennung,
            'laufzeit'                => 36,
            'ruecksprungadressen'     => [
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ],
            'kontakt'                 => [
                'email' => null
            ],
            'risikorelevanteAngaben'  => [
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ],
            'technischeShopparameter' => [
                'shopSystemHersteller' => 'OXID eShop '
            ]
        ];
        $this->assertEquals($expected, $rb->getInitializationData());

        UtilsObject::resetClassInstances();
    }

    public function testGetInitializationDataWithDeliveryAddress(): void
    {
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);

        $basket = oxNew(Basket::class);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserGroups'])
            ->getMock();

        $deliveryAddress = oxNew(Address::class);

        $rb = oxNew(EasyCreditInitializeRequestBuilder::class);
        $rb->setBasket($basket);
        $rb->setUser($user);
        $rb->setShippingAddress($deliveryAddress);

        $config = Registry::getConfig();

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected   = [
            'integrationsart'         => 'PAYMENT_PAGE',
            'shopKennung'             => $this->shopkennung,
            'laufzeit'                => 36,
            'ruecksprungadressen'     => [
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ],
            'kontakt'                 => [
                'email' => null
            ],
            'risikorelevanteAngaben'  => [
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ],
            'technischeShopparameter' => [
                'shopSystemHersteller' => 'OXID eShop '
            ]
        ];
        $this->assertEquals($expected, $rb->getInitializationData());

        UtilsObject::resetClassInstances();
    }

    public function testGetInitializationDataWithCountry(): void
    {
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);

        $basket = oxNew(Basket::class);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserGroups'])
            ->getMock();
        $user->oxuser__oxcountryid = new Field('a7c40f631fc920687.20179984');

        $rb = oxNew(EasyCreditInitializeRequestBuilder::class);
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = Registry::getConfig();

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected   = [
            'integrationsart'         => 'PAYMENT_PAGE',
            'shopKennung'             => $this->shopkennung,
            'laufzeit'                => 36,
            'ruecksprungadressen'     => [
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ],
            'kontakt'                 => [
                'email' => null
            ],
            'risikorelevanteAngaben'  => [
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ],
            'technischeShopparameter' => [
                'shopSystemHersteller' => 'OXID eShop '
            ],
            'rechnungsadresse'        => [
                'land' => 'DE'
            ],
            'lieferadresse'           => [
                'land' => 'DE'
            ]
        ];
        $this->assertEquals($expected, $rb->getInitializationData());

        UtilsObject::resetClassInstances();
    }

    public function testGetInitializationDataWithValidPhoneNumber(): void
    {
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);

        $basket = oxNew(Basket::class);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserGroups'])
            ->getMock();
        $user->oxuser__oxfon = new Field('+49 123-1234');

        $rb = oxNew(EasyCreditInitializeRequestBuilder::class);
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = Registry::getConfig();

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected   = [
            'integrationsart'         => 'PAYMENT_PAGE',
            'shopKennung'             => $this->shopkennung,
            'laufzeit'                => 36,
            'ruecksprungadressen'     => [
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ],
            'kontakt'                 => [
                'email'                             => null,
                'mobilfunknummer'                   => '+49 123-1234',
                'pruefungMobilfunknummerUebergehen' => true
            ],
            'risikorelevanteAngaben'  => [
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ],
            'technischeShopparameter' => [
                'shopSystemHersteller' => 'OXID eShop '
            ],
            'weitereKaeuferangaben'   => [
                'telefonnummer' => '+49 123-1234'
            ]
        ];
        $this->assertEquals($expected, $rb->getInitializationData());

        UtilsObject::resetClassInstances();
    }

    public function testGetInitializationDataWithDeps(): void
    {
        $apiConfig = $this->getMockBuilder(EasyCreditApiConfig::class)->disableOriginalConstructor()->getMock();
        $apiConfig->method('getWebShopId')->willReturn($this->shopkennung);
        UtilsObject::setClassInstance(EasyCreditApiConfig::class, $apiConfig);

        $manufacturer = oxNew(Manufacturer::class);
        $manufacturer->setId('1000');
        $manufacturer->oxmanufacturer__oxtitle = new Field('testmanufacturer');

        $category = oxNew('oxcategory');
        $category->setId('1000');
        $category->oxcategories__oxtitle = new Field('testcategory');

        $unitPrice = oxNew(Price::class);
        $unitPrice->setPrice(250.72);

        $articleIds = ['1000', '2000'];

        $basketContents = [];
        foreach ($articleIds as $i => $articleId) {
            $article = $this->getMockBuilder(Article::class)
                ->disableOriginalConstructor()
                ->setMethods([
                    'getManufacturer',
                    'getCategory',
                ])
                ->getMock();
            $article->expects($this->any())->method('getManufacturer')->willReturn($manufacturer);
            $article->expects($this->any())->method('getCategory')->willReturn($category);
            $article->setId($articleId);

            $basketItem = $this->getMockBuilder(BasketItem::class)
                ->disableOriginalConstructor()
                ->setMethods([
                    'getArticle',
                    'getUnitPrice',
                ])
                ->getMock();
            $basketItem->expects($this->any())->method('getArticle')->willReturn($article);
            $basketItem->expects($this->any())->method('getUnitPrice')->willReturn($unitPrice);
            $basketContents[$articleId] = $basketItem;
        }

        $basket = $this->getMockBuilder(Basket::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getBasketArticles',
                'getContents',
            ])
            ->getMock();
        $basket->expects($this->any())->method('getBasketArticles')->willReturn($basketContents);
        $basket->expects($this->any())->method('getContents')->willReturn($basketContents);

        $user = oxNew(User::class);

        $rb = oxNew(EasyCreditInitializeRequestBuilder::class);
        $rb->setBasket($basket);
        $rb->setUser($user);

        $config = Registry::getConfig();

        $sslShopUrl = EasyCreditDicFactory::getDic()->getConfig()->getSslShopUrl();
        $expected   = [
            'integrationsart'         => 'PAYMENT_PAGE',
            'shopKennung'             => $this->shopkennung,
            'laufzeit'                => 36,
            'ruecksprungadressen'     => [
                'urlAbbruch'   => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment',
                'urlErfolg'    => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=EasyCreditDispatcher&fnc=getEasyCreditDetails',
                'urlAblehnung' => $sslShopUrl . 'index.php?lang=&sid=&shp=' . $config->getBaseShopId() . '&cl=payment'
            ],
            'kontakt'                 => [
                'email' => null
            ],
            'risikorelevanteAngaben'  => [
                'bestellungErfolgtUeberLogin' => false,
                'kundeSeit'                   => '',
                'anzahlBestellungen'          => 0,
                'kundenstatus'                => 'NEUKUNDE',
                'anzahlProdukteImWarenkorb'   => 0,
                'negativeZahlungsinformation' => 'KEINE_INFORMATION',
                'risikoartikelImWarenkorb'    => false,
                'logistikDienstleister'       => ''
            ],
            'technischeShopparameter' => [
                'shopSystemHersteller' => 'OXID eShop '
            ],
            'warenkorbinfos'          => [
                0 => [
                    'produktbezeichnung' => null,
                    'menge'              => 0.0,
                    'preis'              => 250.72,
                    'hersteller'         => null,
                    'produktkategorie'   => 'testcategory',
                    'artikelnummern'     => [
                        0 => [
                            'nummerntyp' => 'GTIN',
                            'nummer'     => null
                        ]
                    ]
                ],
                1 => [
                    'produktbezeichnung' => null,
                    'menge'              => 0.0,
                    'preis'              => 250.72,
                    'hersteller'         => null,
                    'produktkategorie'   => 'testcategory',
                    'artikelnummern'     => [
                        0 => [
                            'nummerntyp' => 'GTIN',
                            'nummer'     => null
                        ]
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, $rb->getInitializationData());

        UtilsObject::resetClassInstances();
    }
}
