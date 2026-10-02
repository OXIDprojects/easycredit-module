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

namespace OxidSolutionCatalysts\EasyCredit\Tests\Unit\Core\Helper;

use OxidEsales\Eshop\Application\Model\Order;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\EasyCredit\Core\Helper\EasyCreditRefundMailService;
use OxidSolutionCatalysts\EasyCredit\Service\EasyCreditModuleSettings;
use ReflectionMethod;

/**
 * Which recipients the configured modes expand to and when a mail is delivered at all. The
 * sending itself needs a shop context and is covered by the mailer in Core\Domain\EasyCreditEmail.
 */
class EasyCreditRefundMailServiceTest extends TestCase
{
    public function recipientModeProvider(): array
    {
        return [
            'no mail'                  => [EasyCreditRefundMailService::MAIL_RECIPIENT_NONE, []],
            'customer only'            => [
                EasyCreditRefundMailService::MAIL_RECIPIENT_CUSTOMER,
                [EasyCreditRefundMailService::MAIL_RECIPIENT_CUSTOMER],
            ],
            'owner only'               => [
                EasyCreditRefundMailService::MAIL_RECIPIENT_OWNER,
                [EasyCreditRefundMailService::MAIL_RECIPIENT_OWNER],
            ],
            'both, customer first'     => [
                EasyCreditRefundMailService::MAIL_RECIPIENT_BOTH,
                [EasyCreditRefundMailService::MAIL_RECIPIENT_CUSTOMER, EasyCreditRefundMailService::MAIL_RECIPIENT_OWNER],
            ],
            'unknown value is no mail' => ['7', []],
            'empty value is no mail'   => ['', []],
        ];
    }

    /**
     * @dataProvider recipientModeProvider
     */
    public function testResolveRecipients(string $mode, array $expected): void
    {
        $service = new EasyCreditRefundMailService();
        $method = new ReflectionMethod($service, 'resolveRecipients');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($service, $mode));
    }

    /**
     * The refund mail must stay silent for the cancellation context: the cancel flow sends its
     * own mail. No mailer is touched in that case.
     */
    public function testRefundMailIsSuppressedForTheCancelContext(): void
    {
        $service = $this->getMockBuilder(EasyCreditRefundMailService::class)
            ->onlyMethods(['deliver', 'getRecipientMode'])
            ->getMock();
        $service->expects($this->never())->method('getRecipientMode');
        $service->expects($this->never())->method('deliver');

        $service->sendRefundMail(
            oxNew(Order::class),
            19.90,
            'EUR',
            EasyCreditRefundMailService::REFUND_CONTEXT_CANCEL
        );
    }

    public function deliveryProvider(): array
    {
        return [
            'refund mail off'         => ['sendRefundMail', 'getOxpsECRefundMailRecipient', '0', 0],
            'refund mail to customer' => ['sendRefundMail', 'getOxpsECRefundMailRecipient', '1', 1],
            'refund mail to both'     => ['sendRefundMail', 'getOxpsECRefundMailRecipient', '3', 2],
            'refund mail not set up'  => ['sendRefundMail', 'getOxpsECRefundMailRecipient', null, 0],
            'cancel mail off'         => ['sendCancelMail', 'getOxpsECCancelMailRecipient', '0', 0],
            'cancel mail to owner'    => ['sendCancelMail', 'getOxpsECCancelMailRecipient', '2', 1],
            'cancel mail to both'     => ['sendCancelMail', 'getOxpsECCancelMailRecipient', '3', 2],
        ];
    }

    /**
     * Each event reads its own setting and sends one mail per configured recipient. A setting
     * that cannot be read (null here) means no mail.
     *
     * @dataProvider deliveryProvider
     */
    public function testMailsAreDeliveredForTheConfiguredRecipients(
        string $method,
        string $setting,
        ?string $value,
        int $expectedMails
    ): void {
        $settings = $this->getMockBuilder(EasyCreditModuleSettings::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOxpsECRefundMailRecipient', 'getOxpsECCancelMailRecipient'])
            ->getMock();
        // the other event stays off
        $otherSetting = $setting === 'getOxpsECRefundMailRecipient'
            ? 'getOxpsECCancelMailRecipient'
            : 'getOxpsECRefundMailRecipient';
        $settings->method($otherSetting)->willReturn('0');
        if ($value === null) {
            $settings->method($setting)->willThrowException(new \RuntimeException('setting not installed'));
        } else {
            $settings->method($setting)->willReturn($value);
        }

        $service = $this->getMockBuilder(EasyCreditRefundMailService::class)
            ->onlyMethods(['deliver', 'getModuleSettings'])
            ->getMock();
        $service->method('getModuleSettings')->willReturn($settings);
        $service->expects($this->exactly($expectedMails))->method('deliver');

        $service->$method(oxNew(Order::class), 19.90, 'EUR');
    }
}
