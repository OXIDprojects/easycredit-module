[{$smarty.block.parent}]

[{assign var="payment" value=$oView->getPayment()}]
[{if $payment->getId() == "easycreditinstallment"}]
    <div class="easycredit_tilgungsplan panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title">[{oxmultilang ident="OXPS_EASY_CREDIT_INSTALMENT_PLAN"}]</h3>
        </div>

        <div class="panel-body">
            [{assign var="instalmentPaymentText" value=$oView->getTilgungsplanText()}]
            [{if $instalmentPaymentText }]
                <p>[{$instalmentPaymentText}]</p>
                [{assign var="urlVorvertraglicheInformationen" value=$oView->getUrlVorvertraglicheInformationen()}]
                [{if $urlVorvertraglicheInformationen }]
                    <p><a class="easycredit-precontract-info" target="_blank" title="[{oxmultilang ident="OXPS_EASY_CREDIT_PRECONTRACT_INFORMATION"}]" href="[{$urlVorvertraglicheInformationen}]">[{oxmultilang ident="OXPS_EASY_CREDIT_PRECONTRACT_INFORMATION"}]</a></p>
                [{/if}]
            [{else}]
                <p>[{oxmultilang ident="OXPS_EASY_CREDIT_INSTALMENT_NO_PLAN"}]</p>
            [{/if}]
        </div>
    </div>
[{/if}]