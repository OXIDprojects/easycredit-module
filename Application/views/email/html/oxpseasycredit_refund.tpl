[{assign var="shop" value=$oEmailView->getShop()}]
[{assign var="oViewConf" value=$oEmailView->getViewConfig()}]

[{capture assign="style"}]
    table.easycreditrefund th, table.easycreditrefund td {
        border: 1px solid #d4d4d4;
        font-size: 13px;
        padding: 5px;
        white-space: nowrap;
    }

    table.easycreditrefund {
        border-collapse: collapse;
    }
[{/capture}]

[{include file="email/html/header.tpl" title="OXPS_EASY_CREDIT_REFUND_MAIL_TITLE"|oxmultilangassign|cat:" #"|cat:$order->oxorder__oxordernr->value style=$style}]

    [{block name="oxpseasycredit_email_html_refund_intro"}]
        <p>
            [{if $isEasyCreditOwnerMail}]
                [{oxmultilang ident="OXPS_EASY_CREDIT_REFUND_MAIL_INTRO_OWNER"}]
            [{else}]
                [{oxmultilang ident="OXPS_EASY_CREDIT_REFUND_MAIL_SALUTATION"}]
                [{$order->oxorder__oxbillfname->getRawValue()}] [{$order->oxorder__oxbilllname->getRawValue()}],
            [{/if}]
        </p>
        [{if !$isEasyCreditOwnerMail}]
            <p>[{oxmultilang ident="OXPS_EASY_CREDIT_REFUND_MAIL_INTRO"}]</p>
        [{/if}]
    [{/block}]

    [{block name="oxpseasycredit_email_html_refund_details"}]
        <table class="easycreditrefund" border="0" cellspacing="0" cellpadding="0" width="100%">
            <tbody>
                <tr valign="top">
                    <th align="right" class="text-right">[{oxmultilang ident="ORDER_NUMBER" suffix="COLON"}]</th>
                    <td>[{$order->oxorder__oxordernr->value}]</td>
                </tr>
                <tr valign="top">
                    <th align="right" class="text-right">[{oxmultilang ident="OXPS_EASY_CREDIT_REFUND_MAIL_AMOUNT" suffix="COLON"}]</th>
                    <td>[{oxprice price=$easyCreditRefundedAmount currency=$currency}]</td>
                </tr>
                <tr valign="top">
                    <th align="right" class="text-right">[{oxmultilang ident="OXPS_EASY_CREDIT_REFUND_MAIL_ORDER_TOTAL" suffix="COLON"}]</th>
                    <td>[{oxprice price=$order->oxorder__oxtotalordersum->value currency=$currency}]</td>
                </tr>
            </tbody>
        </table>
        <br/>
    [{/block}]

    [{block name="oxpseasycredit_email_html_refund_note"}]
        [{if !$isEasyCreditOwnerMail}]
            <p>[{oxmultilang ident="OXPS_EASY_CREDIT_REFUND_MAIL_NOTE"}]</p>
        [{/if}]
    [{/block}]

[{include file="email/html/footer.tpl"}]
