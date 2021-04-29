[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{oxscript include="js/libs/jquery.min.js"}]

<script type="text/javascript">
    window.onload = function () {
        top.oxid.admin.updateList('[{$sOxid}]')
    };
</script>

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="delivery_main">
    <input type="hidden" name="language" value="[{$actlang}]">
</form>

[{if $order}]

    <table width="100%" cellspacing="0" cellpadding="0" border="0">
        <tbody>
            <tr>
                <td class="edittext" valign="top">
                    <table cellspacing="5" cellpadding="0" border="0">
                        <tr>
                            <td class="edittext" colspan="2"><b>[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_INSTALMENTS_CAPTION"}]:</b></td>
                        </tr>
                        <tr>
                            <td class="edittext">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_INTERESTS_VALUE"}]:</td>
                            <td class="edittext">
                                <b>[{$order->getFInterestsValue()}] [{$currency}]</b>
                            </td>
                        </tr>
                        <tr>
                            <td class="edittext">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_TECHNICAL_PROCESS_ID"}]:</td>
                            <td class="edittext">
                                <b>[{$order->oxorder__ecredtechnicalid->value}]</b>
                            </td>
                        </tr>
                        <tr>
                            <td class="edittext">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_TECHNICAL_FUNCTIONAL_ID"}]:</td>
                            <td class="edittext">
                                <b>[{$order->oxorder__ecredfunctionalid->value}]</b>
                            </td>
                        </tr>
                        <tr>
                            <td class="edittext">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_PAYMENT_STATUS"}]:</td>
                            <td class="edittext">[{$order->oxorder__ecredpaymentstatus->value}]</td>
                        </tr>
                        <tr>
                            <td class="edittext">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_ECREDCONFIRMRESPONSE"}]:</td>
                            <td class="edittext">
                                <textarea rows="10" cols="60">[{$confirmationresponse}]</textarea>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
[{else}]
    <div class="messagebox">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_TAB_ONLY_FOR_EASYCREDIT_PAYMENTS"}]</div>
[{/if}]

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]