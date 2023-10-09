<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>

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
[{if $reversalerror}]
    <div class="alert alert-danger">[{$reversalerror}]</div>
[{/if}]
[{if $reversalsuccess}]
    <div class="alert alert-success">[{$reversalsuccess}]</div>
[{/if}]

    <div class="row">
        <div class="col-12">
            <h3>TR Bestellinformationen</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="row">
                <div class="col-12 col-md-4">
                    [{oxmultilang ident="GENERAL_ORDERNUM"}]:
                </div>
                <div class="col-12 col-md-8">
                    [{$order->oxorder__oxordernr->value}]
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4">
                    [{oxmultilang ident="CUSTOMERNUM"}]:
                </div>
                <div class="col-12 col-md-8">
                    [{assign var="user" value=$order->getOrderUser()}]
                    [{$user->oxuser__oxcustnr->value}]
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-12 col-md-4">
                    [{oxmultilang ident="GENERAL_BILLADDRESS"}]:
                </div>
                <div class="col-12 col-md-8">
                    [{if $order->oxorder__oxbillcompany->value}][{oxmultilang ident="GENERAL_COMPANY"}] [{$order->oxorder__oxbillcompany->value}]
                <br>[{/if}]
                    [{if $order->oxorder__oxbilladdinfo->value}][{$order->oxorder__oxbilladdinfo->value}]<br>[{/if}]
                    <a class="jumplink"
                       href="[{$oViewConf->getSelfLink()}]cl=admin_user&oxid=[{$order->oxorder__oxuserid->value}]"
                       target="basefrm"
                       onclick="_homeExpActByName('admin_user');">[{$order->oxorder__oxbillsal->value|oxmultilangsal}]
                        [{$order->oxorder__oxbillfname->value}] [{$order->oxorder__oxbilllname->value}]</a><br>
                    [{$order->oxorder__oxbillstreet->value}] [{$order->oxorder__oxbillstreetnr->value}]<br>
                    [{$order->oxorder__oxbillstateid->value}]
                    [{$order->oxorder__oxbillzip->value}] [{$order->oxorder__oxbillcity->value}]<br>
                    [{$order->oxorder__oxbillcountry->value}]<br>
                    [{if $order->oxorder__oxbillcompany->value && $order->oxorder__oxbillustid->value}]
                <br>
                    [{oxmultilang ident="ORDER_OVERVIEW_VATID"}]:
                    [{$order->oxorder__oxbillustid->value}]<br>
                    [{include file="include/message_vat_check_failed.tpl"}]
                    [{/if}]
                    <br>
                    <a href="mailto:[{$order->oxorder__oxbillemail->value}]?subject=[{$actshop}] - [{oxmultilang ident="GENERAL_ORDERNUM"}] [{$order->oxorder__oxordernr->value}]"
                       class="edittext"><em>[{$order->oxorder__oxbillemail->value}]</em></a><br>
                    <br>

                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="row">
                <div class="col-12">
                    <b>[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_INSTALMENTS_CAPTION"}]</b>
                </div>
            </div>
            [{if 1 != $invalidECIdentifier}]
                <div class="row">
                    <div class="col-12 col-md-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_ORDER_DATE"}]:
                    </div>
                    <div class="col-12 col-md-8">
                        [{$ecorderdata->bestelldatum}]
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_TECHNICAL_FUNCTIONAL_ID"}]:
                    </div>
                    <div class="col-12 col-md-8">
                        [{$ecorderdata->vorgangskennungFachlich}]
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE"}]:
                    </div>
                    <div class="col-12 col-md-8">
                        [{$deliverystate}]
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_PAYMENT_STATUS"}]:
                    </div>
                    <div class="col-12 col-md-8">
                        [{$order->oxorder__ecredpaymentstatus->value}]
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_ORIGINAL_ORDER_VALUE"}]:
                    </div>
                    <div class="col-12 col-md-8">
                        [{$ecorderdata->bestellwertUrspruenglich}] [{$currency}]
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_ACTUAL_ORDER_VALUE"}]:
                    </div>
                    <div class="col-12 col-md-8">
                        [{$ecorderdata->bestellwertAktuell}] [{$currency}]
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL_VALUE"}]:
                    </div>
                    <div class="col-12 col-md-8">
                        [{$ecorderdata->widerrufenerBetrag}] [{$currency}]
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL_DATE"}]:
                    </div>
                    <div class="col-12 col-md-8">
                        [{$ecorderdata->rueckabwicklungEingegebenAm}]
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL_ACCOUNT_DATE"}]:
                    </div>
                    <div class="col-12 col-md-8">
                        [{$ecorderdata->rueckabwicklunngGebuchtAm}]
                    </div>
                </div>
            [{else}]
            <div class="col-12 col-md-4 alert alert-danger">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_NO_DATA_LOADED"}]</div>
            [{/if}]
        </div>
    </div>
<hr>
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="row">
                <div class="col-12 col-md-4">
                    [{oxmultilang ident="GENERAL_ITEM"}]:
                </div>
                <div class="col-12 col-md-8">
                    <table>
                        [{foreach from=$order->getOrderArticles() item=listitem}]
                        <tr>
                            <td valign="top" class="edittext">[{$listitem->oxorderarticles__oxamount->value}] *</td>
                            <td valign="top" class="edittext">&nbsp;[{$listitem->oxorderarticles__oxartnum->value}]</td>
                            <td valign="top" class="edittext">
                                &nbsp;[{$listitem->oxorderarticles__oxtitle->getRawValue()|oxtruncate:20:""|strip_tags}]
                                [{if $listitem->oxwrapping__oxname->value}]
                                &nbsp;([{$listitem->oxwrapping__oxname->value}])&nbsp;
                                [{/if}]
                            </td>
                            <td valign="top" class="edittext">
                                [{$listitem->oxorderarticles__oxselvariant->value}]
                            </td>
                            [{if $order->isNettoMode()}]
                            <td valign="top" class="edittext">&nbsp;&nbsp;[{$listitem->getNetPriceFormated()}]
                                [{$order->oxorder__oxcurrency->value}]
                            </td>
                            [{else}]
                            <td valign="top" class="edittext">&nbsp;&nbsp;[{$listitem->getTotalBrutPriceFormated()}]
                                [{$order->oxorder__oxcurrency->value}]
                            </td>
                            [{/if}]
                            [{if $listitem->getPersParams()}]
                            <td valign="top" class="edittext">
                                [{block name="admin_order_overview_persparams"}]
                                [{include file="include/persparams.tpl" persParams=$listitem->getPersParams()}]
                                [{/block}]
                            </td>
                            [{/if}]
                        </tr>
                        [{/foreach}]
                    </table>
                    <br>
                    [{if $order->oxorder__oxstorno->value}]
                    <span class="orderstorno">[{oxmultilang ident="ORDER_OVERVIEW_STORNO"}]</span><br><br>
                    [{/if}]
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4">
                    [{oxmultilang ident="GENERAL_ATALL"}]:
                </div>
                <div class="col-12 col-md-8">
                    [{include file="include/order_info.tpl" edit=$order}]
                    [{if $order->oxorder__ecredinterestsvalue->value }]
                    <table border="0" cellspacing="0" cellpadding="0" id="order.info">
                        <tr>
                            <td class="edittext" height="15">[{ oxmultilang
                                ident="OXPS_EASY_CREDIT_SUMTOTAL_INCLUDES_INTERESTS" }]&nbsp;&nbsp;
                            </td>
                            <td class="edittext" align="right"><b>[{ $order->getFInterestsValue() }]</b></td>
                            <td class="edittext">
                                &nbsp;<b>[{if $order->oxorder__oxcurrency->value}]
                                    [{$order->oxorder__oxcurrency->value}]
                                    [{else}]&euro;[{/if}]</b></td>
                        </tr>
                    </table>
                    [{/if}]
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">

            [{if 1 != $invalidECIdentifier}]
            <div class="row">
                <div class="col-12">
                    [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL"}]:
                </div>
            </div>
            <form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post">
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cur" value="[{$oActCur->id}]">
                <input type="hidden" name="cl" value="easycreditordereasycredit">
                <input type="hidden" name="fnc" value="sendReversal">
                <input type="hidden" name="oxid" value="[{$oxid}]">
                <input type="hidden" name="editval[oxorder__oxid]" value="[{$oxid}]">
                <div class="row">
                    <div class="col-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL_AMOUNT"}]:
                    </div>
                    <div class="col-8">
                        <input name="reversal[functionalid]" class="hidden" type="hidden"value="[{$ecorderdata->vorgangskennungFachlich}]">
                        <input name="reversal[amount]" class="form-control" type="number" step="0.01" min="0" max="[{$ecorderdata->bestellwertAktuell}]" value="0.00">
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON"}]:
                    </div>
                    <div class="col-8">
                        <select name="reversal[reason]" class="form-select">
                            <option value="WIDERRUF_VOLLSTAENDIG">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_FULL"}]</option>
                            <option value="WIDERRUF_TEILWEISE">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_PARTIAL"}]</option>
                            <option value="RUECKGABE_GARANTIE_GEWAEHRLEISTUNG">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_WARRANTY_FULL"}]</option>
                            <option value="MINDERUNG_GARANTIE_GEWAEHRLEISTUNG">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_REVERSAL_REASON_WARRANTY_PARTIAL"}]</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <input type="submit" value="Absenden">
                    </div>
                </div>
            </form>

            [{/if}]

        </div>
    </div>
    [{else}]
    <div class="messagebox">[{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_TAB_ONLY_FOR_EASYCREDIT_PAYMENTS"}]</div>
    [{/if}]

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]