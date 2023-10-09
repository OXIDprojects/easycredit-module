[{if $sPaymentID == "easycreditinstallment"}]
    <div class="well well-sm">
        [{include file="page/checkout/inc/payment_easycreditinstallment.tpl"}]
    </div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
