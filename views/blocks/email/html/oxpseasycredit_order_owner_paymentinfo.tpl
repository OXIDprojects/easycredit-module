[{$smarty.block.parent}]
[{block name="email_html_order_owner_easycredit_paymentplan"}]
    [{if $payment->oxuserpayments__oxpaymentsid->value == "easycreditinstallment"}]
        [{assign var="instalmentPaymentText" value=$order->getTilgungsplanTxt()}]
        [{if $instalmentPaymentText }]
            <h3 class="underline">[{oxmultilang ident="OXPS_EASY_CREDIT_INSTALMENT_PLAN"}]</h3>
            <p>[{$instalmentPaymentText}]</p>
            <br>
        [{/if}]
    [{/if}]
[{/block}]