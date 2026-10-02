<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2021
 */
namespace OxidProfessionalServices\EasyCredit\Application\Controller\Admin;


use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Application\Model\EasyCreditTradingApiAccess;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditHelper;
use OxidProfessionalServices\EasyCredit\Core\Helper\EasyCreditRefundMailService;
use Throwable;

/**
 * Class EasyCreditOrderListController
 *
 * @package OxidProfessionalServices\EasyCredit\Application\Controller\Admin
 */
class EasyCreditOrderListController extends EasyCreditOrderListController_parent
{
    /**
     * Render method
     *
     * @return string
     */
    public function render()
    {
        $template = parent::render();
        $this->addTplParam('ecorders', Registry::getRequest()->getRequestParameter('ecorders'));

        return $template;
    }

    /**
     * Prepares SQL where query according SQL condition array and attaches it to SQL end.
     * For each search value if german umlauts exist, adds them
     * and replaced by spec. char to query
     *
     * @param array  $whereQuery SQL condition array
     * @param string $fullQuery  SQL query string
     *
     * @return string
     * @deprecated underscore prefix violates PSR12, will be renamed to "prepareWhereQuery" in next major
     */
    protected function _prepareWhereQuery($whereQuery, $fullQuery) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $query = parent::_prepareWhereQuery($whereQuery, $fullQuery);
        $orders = Registry::getRequest()->getRequestParameter('ecorders');
        switch ($orders) {
            case 'only':
                $query .= " and ( `$this->_sListClass`.`ecredfunctionalid` IS NOT NULL ) ";
                break;
            case 'not':
                $query .= " and ( `$this->_sListClass`.`ecredfunctionalid` IS NULL ) ";
                break;
            default:
        }

        return $query;
    }

    /**
     * Cancels an order in the backend and, when the merchant asked for it, reverses the order
     * value that is still open at easyCredit. The confirmation mail goes out afterwards and
     * states the refunded amount if one was refunded, so the customer gets one mail for the
     * whole event.
     *
     * Orders of other payment methods and orders that cannot be loaded are passed straight
     * through to the parent implementation.
     *
     * @return void
     */
    public function cancelOrder()
    {
        $order = $this->loadEasyCreditOrderForCancel();

        parent::cancelOrder();

        if ($order === null) {
            return;
        }

        $refundedAmount = $this->easyCreditRefundOnCancel($order);

        $currency = $order->getFieldData('oxcurrency');
        $this->getEasyCreditRefundMailService()->sendCancelMail(
            $order,
            $refundedAmount,
            is_scalar($currency) ? (string)$currency : ''
        );
    }

    /**
     * The order that is going to be cancelled, if it was paid with easyCredit.
     *
     * @return Order|null
     */
    protected function loadEasyCreditOrderForCancel()
    {
        $orderId = $this->getEditObjectId();
        if (!$orderId) {
            return null;
        }

        $order = oxNew(Order::class);
        if (!$order->load($orderId)) {
            return null;
        }

        $paymentType = (string)$order->getFieldData('oxpaymenttype');
        $isEasyCreditPayment = EasyCreditHelper::isEasyCreditInstallmentById($paymentType)
            || EasyCreditHelper::isEasyCreditInvoiceById($paymentType);
        if (!$isEasyCreditPayment || empty($order->getFieldData('ecredfunctionalid'))) {
            return null;
        }

        return $order;
    }

    /**
     * Reverses the order value that is still open at easyCredit, if the merchant switched that
     * on (module setting oxpsECAutomatedRefundOnCancel, off by default).
     *
     * Only the current order value is reversed: a cancellation cancels the whole order, and an
     * amount that was reversed before must not be sent a second time. Nothing left means
     * nothing happens.
     *
     * A reversal that does not work out must never undo the cancellation: the order is
     * cancelled at this point, so the problem is logged, reported to the merchant and left at
     * that - the "easyCredit information" tab is still there to reverse by hand.
     *
     * @param Order $order
     *
     * @return float|null amount easyCredit accepted as reversed, null when nothing was reversed
     */
    protected function easyCreditRefundOnCancel(Order $order)
    {
        // Reading the setting happens inside the try as well: a cancellation is a routine
        // backend action and must not end in the maintenance screen.
        try {
            if (!$this->isEasyCreditAutomatedRefundOnCancel()) {
                return null;
            }

            $tradingApiService = $this->getEasyCreditTradingApiService($order);
            $refundableAmount = round($tradingApiService->getCurrentOrderValue(), 2);
            if ($refundableAmount <= 0.0) {
                return null;
            }

            $response = $tradingApiService->sendReversal(
                $refundableAmount,
                EasyCreditTradingApiAccess::REVERSAL_REASON_FULL
            );
            if (!EasyCreditHelper::isAcceptedResponse($response)) {
                $this->onEasyCreditRefundOnCancelFailed($order, 'status code ' . ($response->statusCode ?? 'unknown'));

                return null;
            }

            return $refundableAmount;
        } catch (Throwable $throwable) {
            $this->onEasyCreditRefundOnCancelFailed($order, $throwable->getMessage());

            return null;
        }
    }

    /**
     * @return bool
     */
    protected function isEasyCreditAutomatedRefundOnCancel()
    {
        return (bool)Registry::getConfig()->getConfigParam('oxpsECAutomatedRefundOnCancel');
    }

    /**
     * @param Order $order
     *
     * @return EasyCreditTradingApiAccess
     */
    protected function getEasyCreditTradingApiService(Order $order)
    {
        return oxNew(EasyCreditTradingApiAccess::class, $order);
    }

    /**
     * @return EasyCreditRefundMailService
     */
    protected function getEasyCreditRefundMailService()
    {
        return oxNew(EasyCreditRefundMailService::class);
    }

    /**
     * @param Order  $order
     * @param string $reason
     *
     * @return void
     */
    protected function onEasyCreditRefundOnCancelFailed(Order $order, string $reason)
    {
        try {
            Registry::getLogger()->warning(sprintf(
                'easyCredit automated reversal on cancellation of order %s failed: %s',
                (string)$order->getFieldData('oxordernr'),
                $reason
            ));
            Registry::getUtilsView()->addErrorToDisplay('OXPS_EASY_CREDIT_ADMIN_CANCEL_REFUND_FAILED');
        } catch (Throwable $throwable) {
            // reporting must not break the cancellation either
        }
    }
}
