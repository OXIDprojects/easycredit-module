<?php

declare(strict_types=1);

class_alias(
    \OxidEsales\Eshop\Application\Controller\PaymentController::class,
    \OxidSolutionCatalysts\EasyCredit\Controller\EasyCreditPaymentController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\OrderController::class,
    \OxidSolutionCatalysts\EasyCredit\Controller\EasyCreditOrderController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\Admin\OrderAddress::class,
    \OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderAddressController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\Admin\OrderArticle::class,
    \OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderArticleController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class,
    \OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderOverviewController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class,
    \OxidSolutionCatalysts\EasyCredit\Controller\Admin\EasyCreditOrderListController_parent::class
);

class_alias(
    \OxidEsales\Eshop\Core\Session::class,
    \OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditSession_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Payment::class,
    \OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditPayment_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Basket::class,
    \OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditBasket_parent::class
);

class_alias(
    \OxidEsales\Eshop\Application\Model\Order::class,
    \OxidSolutionCatalysts\EasyCredit\Core\Domain\EasyCreditOrder_parent::class
);

class_alias(
    \OxidEsales\Eshop\Core\ViewConfig::class,
    \OxidSolutionCatalysts\EasyCredit\Core\EasyCreditViewConfig_parent::class
);