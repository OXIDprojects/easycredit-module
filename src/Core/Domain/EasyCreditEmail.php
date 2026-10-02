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
 * @copyright (C) OXID eSales AG 2003-2026
 */

namespace OxidSolutionCatalysts\EasyCredit\Core\Domain;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\Event\AdminModeChangedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Confirmation mails for reversals (refunds) and order cancellations triggered in the backend.
 * Rendering and recipient handling only; whether a mail is sent at all is decided by
 * Core\Helper\EasyCreditRefundMailService, which is the single caller.
 *
 * @mixin \OxidEsales\Eshop\Core\Email
 */
class EasyCreditEmail extends EasyCreditEmail_parent
{
    /**
     * easyCredit is a German product and the module ships German texts only, so the mails are
     * always rendered in the shop language with this abbreviation.
     */
    const EASYCREDIT_MAIL_LANGUAGE = 'de';

    /**
     * Refund confirmation - HTML
     *
     * @var string
     */
    protected $easyCreditRefundTplHtml = '@osceasycredit/frontend/tpl/email/html/oxpseasycredit_refund';

    /**
     * Refund confirmation - Plain
     *
     * @var string
     */
    protected $easyCreditRefundTplPlain = '@osceasycredit/frontend/tpl/email/plain/oxpseasycredit_refund';

    /**
     * Cancellation confirmation - HTML
     *
     * @var string
     */
    protected $easyCreditCancelTplHtml = '@osceasycredit/frontend/tpl/email/html/oxpseasycredit_cancel';

    /**
     * Cancellation confirmation - Plain
     *
     * @var string
     */
    protected $easyCreditCancelTplPlain = '@osceasycredit/frontend/tpl/email/plain/oxpseasycredit_cancel';

    /**
     * @param Order  $order
     * @param float  $refundedAmount amount easyCredit accepted as reversed
     * @param string $currency       currency code of the refunded amount
     *
     * @return bool
     */
    public function sendEasyCreditRefundMailToCustomer(Order $order, float $refundedAmount, string $currency): bool
    {
        return $this->sendEasyCreditRefundMail($order, $refundedAmount, $currency, false);
    }

    /**
     * @param Order  $order
     * @param float  $refundedAmount amount easyCredit accepted as reversed
     * @param string $currency       currency code of the refunded amount
     *
     * @return bool
     */
    public function sendEasyCreditRefundMailToOwner(Order $order, float $refundedAmount, string $currency): bool
    {
        return $this->sendEasyCreditRefundMail($order, $refundedAmount, $currency, true);
    }

    /**
     * @param Order      $order
     * @param float|null $refundedAmount amount reversed along with the cancellation, null if
     *                                   nothing was reversed
     * @param string     $currency       currency code of the refunded amount
     *
     * @return bool
     */
    public function sendEasyCreditCancelMailToCustomer(Order $order, ?float $refundedAmount, string $currency): bool
    {
        return $this->sendEasyCreditCancelMail($order, $refundedAmount, $currency, false);
    }

    /**
     * @param Order      $order
     * @param float|null $refundedAmount amount reversed along with the cancellation, null if
     *                                   nothing was reversed
     * @param string     $currency       currency code of the refunded amount
     *
     * @return bool
     */
    public function sendEasyCreditCancelMailToOwner(Order $order, ?float $refundedAmount, string $currency): bool
    {
        return $this->sendEasyCreditCancelMail($order, $refundedAmount, $currency, true);
    }

    /**
     * @param Order  $order
     * @param float  $refundedAmount
     * @param string $currency
     * @param bool   $toOwner send to the shop owner instead of the customer
     *
     * @return bool
     */
    protected function sendEasyCreditRefundMail(Order $order, float $refundedAmount, string $currency, bool $toOwner): bool
    {
        return $this->sendEasyCreditOrderMail(
            $order,
            $toOwner,
            $this->easyCreditRefundTplHtml,
            $this->easyCreditRefundTplPlain,
            $toOwner ? 'OXPS_EASY_CREDIT_REFUND_MAIL_SUBJECT_OWNER' : 'OXPS_EASY_CREDIT_REFUND_MAIL_SUBJECT',
            [
                'easyCreditRefundedAmount' => $refundedAmount,
                'easyCreditCurrencyCode'   => $currency,
            ]
        );
    }

    /**
     * @param Order      $order
     * @param float|null $refundedAmount
     * @param string     $currency
     * @param bool       $toOwner send to the shop owner instead of the customer
     *
     * @return bool
     */
    protected function sendEasyCreditCancelMail(Order $order, ?float $refundedAmount, string $currency, bool $toOwner): bool
    {
        return $this->sendEasyCreditOrderMail(
            $order,
            $toOwner,
            $this->easyCreditCancelTplHtml,
            $this->easyCreditCancelTplPlain,
            $toOwner ? 'OXPS_EASY_CREDIT_CANCEL_MAIL_SUBJECT_OWNER' : 'OXPS_EASY_CREDIT_CANCEL_MAIL_SUBJECT',
            [
                'easyCreditRefundedAmount' => $refundedAmount,
                'easyCreditCurrencyCode'   => $currency,
            ]
        );
    }

    /**
     * @param Order                $order
     * @param bool                 $toOwner
     * @param string               $htmlTemplate
     * @param string               $plainTemplate
     * @param string               $subjectIdent  language ident, receives the order number
     * @param array<string, mixed> $viewData      additional template variables
     *
     * @return bool
     */
    protected function sendEasyCreditOrderMail(
        Order $order,
        bool $toOwner,
        string $htmlTemplate,
        string $plainTemplate,
        string $subjectIdent,
        array $viewData
    ): bool {
        $mailLanguage = $this->getEasyCreditMailLanguage($order);

        $shop = $this->getShop($mailLanguage);
        $this->setMailParams($shop);

        $this->setViewData('order', $order);
        $this->setViewData('currency', $order->getOrderCurrency());
        $this->setViewData('isEasyCreditOwnerMail', $toOwner);
        foreach ($viewData as $name => $value) {
            $this->setViewData($name, $value);
        }

        $renderer = $this->getRenderer();

        // Process view data array through oxOutput processor
        $this->processViewArray();

        $lang = Registry::getLang();
        $previousTplLanguage = (int)$lang->getTplLanguage();
        $previousBaseLanguage = (int)$lang->getBaseLanguage();
        $lang->setTplLanguage($mailLanguage);
        $lang->setBaseLanguage($mailLanguage);

        // These mails are triggered from the backend, but they use frontend templates and
        // frontend language files. Rendering them in admin mode leaves core idents unresolved
        // and looks the frontend templates up below the admin theme, so switch the admin mode
        // off around the rendering and restore it afterwards.
        $wasAdmin = $this->switchEasyCreditAdminMode(false);

        try {
            $this->setBody($renderer->renderTemplate($htmlTemplate, $this->getViewData()));
            $this->setAltBody($renderer->renderTemplate($plainTemplate, $this->getViewData()));

            // the subject ident lives in the frontend language files, so it belongs into the
            // same window as the templates
            $subject = (string)$lang->translateString($subjectIdent);
            $this->setSubject(sprintf($subject, $this->getEasyCreditFieldAsString($order, 'oxordernr')));
        } finally {
            // A failing template must not leave the shop behind in frontend mode or in another
            // language: the admin page that triggered the mail is rendered after this.
            $this->switchEasyCreditAdminMode($wasAdmin);
            $lang->setTplLanguage($previousTplLanguage);
            $lang->setBaseLanguage($previousBaseLanguage);
        }

        if ($toOwner) {
            $this->setRecipient(
                $this->getEasyCreditFieldAsString($shop, 'oxowneremail'),
                $shop->oxshops__oxname->getRawValue()
            );

            return $this->send();
        }

        $fullName = $order->oxorder__oxbillfname->getRawValue()
            . ' ' . $order->oxorder__oxbilllname->getRawValue();

        $this->setRecipient($this->getEasyCreditFieldAsString($order, 'oxbillemail'), $fullName);
        $this->setReplyTo(
            $this->getEasyCreditFieldAsString($shop, 'oxorderemail'),
            $shop->oxshops__oxname->getRawValue()
        );

        return $this->send();
    }

    /**
     * Id of the German shop language. Falls back to the language the order was placed in if
     * the shop has no German language.
     *
     * @param Order $order
     *
     * @return int
     */
    protected function getEasyCreditMailLanguage(Order $order): int
    {
        $languageId = array_search(self::EASYCREDIT_MAIL_LANGUAGE, Registry::getLang()->getLanguageIds(), true);
        if ($languageId !== false) {
            return (int)$languageId;
        }

        $orderLanguage = $order->getFieldData('oxlang');

        return is_numeric($orderLanguage) ? (int)$orderLanguage : 0;
    }

    /**
     * getFieldData() is untyped, so anything that is not a plain value yields an empty string
     * instead of being cast.
     *
     * @param \OxidEsales\Eshop\Core\Model\BaseModel $model
     * @param string                                 $field
     *
     * @return string
     */
    protected function getEasyCreditFieldAsString($model, string $field): string
    {
        $value = $model->getFieldData($field);

        return is_scalar($value) ? (string)$value : '';
    }

    /**
     * Switches the admin mode and returns the mode that was active before.
     *
     * Setting the config flag alone is not enough: the template engine resolves its namespaced
     * template directories once and keeps them, so "@__main__" would still point at the admin
     * theme and a frontend template including "email/html/header.html.twig" would not be found.
     * The AdminModeChangedEvent makes the engine reload those directories. The core switches the
     * mode the same way (Core\Email::switchToShopMode()), but its helpers are private.
     *
     * @param bool $isAdmin mode to switch to
     *
     * @return bool mode that was active before
     */
    protected function switchEasyCreditAdminMode(bool $isAdmin): bool
    {
        $config = Registry::getConfig();
        $wasAdmin = (bool)$config->isAdmin();

        if ($wasAdmin === $isAdmin) {
            return $wasAdmin;
        }

        $config->setAdminMode($isAdmin);

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = ContainerFactory::getInstance()
            ->getContainer()
            ->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new AdminModeChangedEvent());

        return $wasAdmin;
    }
}
