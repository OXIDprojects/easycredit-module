<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;
use OxidProfessionalServices\EasyCredit\Core\Exception\EasyCreditException;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;

/**
 * Order admin class for easyCredit
 *
 * Implements register "easyCredit-Ratenkauf" in admin | order management | orders
 */
class EasyCreditOrderEasyCreditController extends \OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController
{
    /**
     * @var Order order
     */
    private $order = false;

    /**
     * Valid reasons for reversal.
     *
     * @var string[]
     */
    protected $allowedReversalReasons = [
        "WIDERRUF_VOLLSTAENDIG",
        "WIDERRUF_TEILWEISE",
        "RUECKGABE_GARANTIE_GEWAEHRLEISTUNG",
        "MINDERUNG_GARANTIE_GEWAEHRLEISTUNG",
    ];

    /**
     * Render method
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $this->_aViewData["sOxid"] = $this->getEditObjectId();

        if ($this->hasEasyCreditPayment()) {
            $this->_aViewData['order']                = $this->getOrder();
            $this->_aViewData['currency']             = $this->getOrder()->getOrderCurrency()->name;
            $this->_aViewData['confirmationresponse'] = $this->getEasyCreditConfirmationResponse();
            try {
                $this->_aViewData['deliverystate'] = $this->getEasyCreditDeliveryState();
                $this->_aViewData['ecorderdata']   = $this->getEasyCreditOrderData();
            } catch (\Exception $e) {
                EasyCreditDicFactory::getDic()->getLogging()->log($e->getMessage());
                $this->_aViewData['invalidECIdentifier'] = 1;
            }

        }

        return "oxpseasycredit_order_easycredit.tpl";
    }

    /**
     * Returns true if orders was payed with ratenkauf by easyCredit
     *
     * @return bool
     */
    public function hasEasyCreditPayment()
    {
        /** @var $order Order */
        $order = $this->getOrder();
        return $order && EasyCreditHelper::isEasyCreditInstallmentById($order->getFieldData('oxpaymenttype'));
    }

    /**
     * Initiate reversal process. Validate data and send if valid
     */
    public function sendReversal()
    {
        // Validierung Input and send reversalt to ec api
        try {
            $request = Registry::getRequest()->getRequestParameter('reversal');
            $this->validateInput($request);
            $this->sendReversalToEc($request);
            $reversalSuccess = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ADMIN_REVERSAL_SUCCESS');
            $this->addTplParam('reversalsuccess', $reversalSuccess);
        } catch (EasyCreditException $e) {
            if (0 < $e->getCode()) {
                $reversalError = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ADMIN_REVERSAL_ERROR_AMOUNT');

            } else {
                $reversalError = Registry::getLang()->translateString('OXPS_EASY_CREDIT_ADMIN_REVERSAL_ERROR_COMMON');
            }
            $this->addTplParam('reversalerror', $reversalError);
        }
    }

    /**
     * Returns current edited order object
     *
     * @return Order
     */
    protected function getOrder()
    {
        $soxId = $this->getEditObjectId();
        if ($this->order === false && $soxId != '-1') {
            $this->order = oxNew(Order::class);
            $this->order->load($soxId);
        }

        return $this->order;
    }

    /**
     * Returns acknowlegement of easycredit of order corresponded payment process
     * Response is pretty painted for user
     *
     * @return string
     */
    protected function getEasyCreditConfirmationResponse()
    {
        $response = $this->getOrder()->oxorder__ecredconfirmresponse->value;
        if ($response) {
            $response = unserialize(base64_decode($response));
            if (is_object($response)) {
                $response = json_encode($response, JSON_PRETTY_PRINT);
            }
        }
        return $response;
    }

    /**
     * Load order state from ec trading api.
     *
     * @return array|string
     */
    protected function getEasyCreditDeliveryState()
    {
        $service = $this->getService();

        return $service->getOrderState();
    }

    /**
     * Load all order related data from ec trading api and prepare for frontend output.
     *
     * @return mixed
     * @throws EasyCreditException
     */
    protected function getEasyCreditOrderData()
    {
        $orderData = $this->getService()->getOrderData();
        if (1 > count($orderData)) {
            throw new EasyCreditException(
                "No data found for order with identifier {$this->order->getFieldData('ecredfunctionalid')}"
            );
        }
        $dataobject = clone($orderData[0]);
        /** @var Language $lang */
        $lang                                 = Registry::getLang();
        $dataobject->bestellwertAktuell       = $lang->formatCurrency($dataobject->bestellwertAktuell);
        $dataobject->bestellwertUrspruenglich = $lang->formatCurrency($dataobject->bestellwertUrspruenglich);
        $dataobject->widerrufenerBetrag       = $lang->formatCurrency($dataobject->widerrufenerBetrag);
        $date                                 = new \DateTime($dataobject->bestelldatum);
        $dataobject->bestelldatum             = $date->format('d.m.Y');

        if ($dataobject->rueckabwicklungEingegebenAm) {
            $date                                    = new \DateTime($dataobject->rueckabwicklungEingegebenAm);
            $dataobject->rueckabwicklungEingegebenAm = $date->format('d.m.Y');
        }

        if ($dataobject->rueckabwicklunngGebuchtAm) {
            $date                                  = new \DateTime($dataobject->rueckabwicklunngGebuchtAm);
            $dataobject->rueckabwicklunngGebuchtAm = $date->format('d.m.Y');
        }

        return $dataobject;
    }

    /**
     * Get the service layer for trading api access.
     *
     * @return mixed|EasyCreditTradingApiAccess
     */
    protected function getService()
    {
        $service = oxNew(EasyCreditTradingApiAccess::class, $this->getOrder());

        return $service;
    }

    /**
     * Input validation for reversal process.
     * Is valid order,
     * is valid amount
     * is valid reason
     *
     * @param array $request
     *
     * @throws EasyCreditException
     */
    protected function validateInput(array $request)
    {
        if (false !== $this->getOrder()) {
            # ist request tecid = ordertecid
            $fIdRequest = $request['functionalid'];
            $fIdOrder   = $this->order->getFieldData('ecredfunctionalid');
            if ($fIdOrder != $fIdRequest) {
                throw new EasyCreditException("Functional ID mismatch");
            }
            # match reversal amount to max open amount
            $service               = $this->getService();
            $orderData             = $service->getOrderData();
            $maxReversalAmount     = (float) $orderData[0]->bestellwertAktuell;
            $requestReversalAmount = (float) $request['amount'];
            if ($requestReversalAmount > $maxReversalAmount || 0 >= $requestReversalAmount) {
                throw new EasyCreditException("Requested reversal greater than actual amount", 10);
            }
            if (false === array_search($request['reason'], $this->allowedReversalReasons)) {
                throw new EasyCreditException("Requested reversal reason invalid");
            }

        } else {
            throw new EasyCreditException("No order given");
        };
    }

    /**
     * Send reversal call to ex trading api.
     */
    private function sendReversalToEc(array $request)
    {
        $amount = (float) $request['amount'];
        $reason = $request['reason'];
        $service = $this->getService();
        $service->sendReversal($amount, $reason);
    }
}
