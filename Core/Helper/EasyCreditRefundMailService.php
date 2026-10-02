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

namespace OxidProfessionalServices\EasyCredit\Core\Helper;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Domain\EasyCreditEmail;
use Throwable;

/**
 * Sends the confirmation mails for reversals (refunds) and order cancellations that were
 * triggered in the backend.
 *
 * This class and Core\Domain\EasyCreditEmail are the only places holding the mail logic, so
 * that the same feature can be moved to the central payment base module later without
 * touching the trigger points. Everything the module-specific side has to do is to call
 * sendRefundMail() at the point where easyCredit accepted the reversal, and sendCancelMail()
 * when an order was cancelled.
 */
class EasyCreditRefundMailService
{
    const MAIL_RECIPIENT_NONE = '0';
    const MAIL_RECIPIENT_CUSTOMER = '1';
    const MAIL_RECIPIENT_OWNER = '2';
    const MAIL_RECIPIENT_BOTH = '3';

    const REFUND_CONTEXT_REFUND = 'refund';
    const REFUND_CONTEXT_CANCEL = 'cancel';

    const SETTING_REFUND_MAIL_RECIPIENT = 'oxpsECRefundMailRecipient';
    const SETTING_CANCEL_MAIL_RECIPIENT = 'oxpsECCancelMailRecipient';

    /**
     * Confirmation of a reversal. Does nothing when the merchant did not choose a recipient
     * for it, and nothing for the cancellation flow, which sends its own mail covering the
     * cancellation and the refunded amount together.
     *
     * @param Order  $order
     * @param float  $refundedAmount amount easyCredit accepted as reversed
     * @param string $currency       currency code of the refunded amount
     * @param string $context        one of the REFUND_CONTEXT_* values
     *
     * @return void
     */
    public function sendRefundMail(
        Order $order,
        float $refundedAmount,
        string $currency,
        string $context = self::REFUND_CONTEXT_REFUND
    ): void {
        if ($context === self::REFUND_CONTEXT_CANCEL) {
            return;
        }

        $recipients = $this->resolveRecipients($this->getRecipientMode(self::REFUND_CONTEXT_REFUND));

        foreach ($recipients as $recipient) {
            $this->deliver(
                $order,
                $recipient,
                'refund',
                static function ($mailer) use ($order, $refundedAmount, $currency, $recipient): bool {
                    /** @var EasyCreditEmail $mailer */
                    return $recipient === self::MAIL_RECIPIENT_OWNER
                        ? $mailer->sendEasyCreditRefundMailToOwner($order, $refundedAmount, $currency)
                        : $mailer->sendEasyCreditRefundMailToCustomer($order, $refundedAmount, $currency);
                }
            );
        }
    }

    /**
     * Confirmation of an order cancellation. The refunded amount is part of this mail when the
     * cancellation triggered a reversal easyCredit accepted, and omitted when no money was moved.
     *
     * @param Order      $order
     * @param float|null $refundedAmount null if the cancellation refunded nothing
     * @param string     $currency       currency code of the refunded amount
     *
     * @return void
     */
    public function sendCancelMail(Order $order, ?float $refundedAmount, string $currency): void
    {
        $recipients = $this->resolveRecipients($this->getRecipientMode(self::REFUND_CONTEXT_CANCEL));

        foreach ($recipients as $recipient) {
            $this->deliver(
                $order,
                $recipient,
                'cancel',
                static function ($mailer) use ($order, $refundedAmount, $currency, $recipient): bool {
                    /** @var EasyCreditEmail $mailer */
                    return $recipient === self::MAIL_RECIPIENT_OWNER
                        ? $mailer->sendEasyCreditCancelMailToOwner($order, $refundedAmount, $currency)
                        : $mailer->sendEasyCreditCancelMailToCustomer($order, $refundedAmount, $currency);
                }
            );
        }
    }

    /**
     * Configured recipients for an event. A setting that cannot be read - for instance because
     * the module configuration was not installed after an update - means "no mail" rather than
     * an error in the backend.
     *
     * @param string $event one of the REFUND_CONTEXT_* values
     *
     * @return string one of the MAIL_RECIPIENT_* modes
     */
    protected function getRecipientMode(string $event): string
    {
        try {
            $mode = Registry::getConfig()->getConfigParam(
                $event === self::REFUND_CONTEXT_CANCEL
                    ? self::SETTING_CANCEL_MAIL_RECIPIENT
                    : self::SETTING_REFUND_MAIL_RECIPIENT
            );
        } catch (Throwable $throwable) {
            return self::MAIL_RECIPIENT_NONE;
        }

        return is_scalar($mode) ? (string)$mode : self::MAIL_RECIPIENT_NONE;
    }

    /**
     * The single recipients a configured mode expands to, in sending order. Unknown or empty
     * values mean "no mail", so a broken setting can never start sending mail.
     *
     * @param string $mode one of the MAIL_RECIPIENT_* modes
     *
     * @return string[] MAIL_RECIPIENT_CUSTOMER / MAIL_RECIPIENT_OWNER, empty when no mail
     *                  should be sent
     */
    protected function resolveRecipients(string $mode): array
    {
        if ($mode === self::MAIL_RECIPIENT_CUSTOMER) {
            return [self::MAIL_RECIPIENT_CUSTOMER];
        }

        if ($mode === self::MAIL_RECIPIENT_OWNER) {
            return [self::MAIL_RECIPIENT_OWNER];
        }

        if ($mode === self::MAIL_RECIPIENT_BOTH) {
            return [self::MAIL_RECIPIENT_CUSTOMER, self::MAIL_RECIPIENT_OWNER];
        }

        return [];
    }

    /**
     * Sends one mail with a fresh mailer instance. A failing mailer must never abort the
     * backend action that triggered it: the reversal or cancellation has already happened at
     * this point, and letting a mail problem bubble up would leave the merchant with an error
     * page for an action that actually succeeded.
     *
     * @param Order    $order
     * @param string   $recipient one of the MAIL_RECIPIENT_* recipients
     * @param string   $type      refund|cancel, for the log entry
     * @param callable $send      receives the mailer, returns the send result
     *
     * @return void
     */
    protected function deliver(Order $order, string $recipient, string $type, callable $send): void
    {
        $recipientName = $recipient === self::MAIL_RECIPIENT_OWNER ? 'shop owner' : 'customer';

        try {
            $mailer = oxNew(Email::class);
            if (!$send($mailer)) {
                $this->logWarning(sprintf(
                    'easyCredit %s confirmation mail to %s for order %s was not sent',
                    $type,
                    $recipientName,
                    $this->orderNumber($order)
                ));
            }
        } catch (Throwable $throwable) {
            $this->logWarning(sprintf(
                'easyCredit %s confirmation mail to %s for order %s failed: %s',
                $type,
                $recipientName,
                $this->orderNumber($order),
                $throwable->getMessage()
            ));
        }
    }

    /**
     * Order number for log messages. getFieldData() is untyped, so anything that is not a plain
     * value is reported as an empty number instead of being cast.
     *
     * @param Order $order
     *
     * @return string
     */
    protected function orderNumber(Order $order): string
    {
        $orderNr = $order->getFieldData('oxordernr');

        return is_scalar($orderNr) ? (string)$orderNr : '';
    }

    /**
     * @param string $message
     *
     * @return void
     */
    protected function logWarning(string $message): void
    {
        try {
            Registry::getLogger()->warning($message);
        } catch (Throwable $throwable) {
            // logging must not break the backend action either
        }
    }
}
