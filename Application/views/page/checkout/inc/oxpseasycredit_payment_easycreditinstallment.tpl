[{assign var="easyCreditInstallmentIsPossible" value=$oView->isEasyCreditInstallmentPossible() }]
[{assign var="isEasyCreditAPIV3" value=$oView->isEasyCreditAPIV3() }]
<dl>
    <dt>
        <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{$sPaymentID}]" [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value && $easyCreditInstallmentIsPossible}]checked[{/if}][{if !$easyCreditInstallmentIsPossible}] disabled[{/if}]>
        <label for="payment_[{$sPaymentID}]"[{if !$easyCreditInstallmentIsPossible}] class="easycreditdisabled"[{/if}]><b>[{$paymentmethod->oxpayments__oxdesc->value}]</b></label>
        [{if !$easyCreditInstallmentIsPossible}]
            <div class="col-lg-offset-3">
                <img class="payment-logo-easycredit" src="[{$oViewConf->getModuleUrl('oxpseasycredit')}]out/pictures/eclogo.png" alt="Easy Credit">
                [{assign var="errorMsgs" value=$oView->getErrorMessages()}]
                [{foreach from=$errorMsgs item=errorMsg}]
                    <div>[{$errorMsg}]</div>
                [{/foreach}]
            </div>
        [{/if}]
    </dt>
    <dd class="payment-option[{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]activePayment[{/if}]">
        [{if $easyCreditInstallmentIsPossible}]
            <div class="col-lg-offset-3">
                <img class="payment-logo-easycredit" src="[{$oViewConf->getModuleUrl('oxpseasycredit')}]out/pictures/eclogo.png" alt="Easy Credit">
            </div>

            [{foreach from=$paymentmethod->getDynValues() item=value name=PaymentDynValues}]
                <div class="form-group">
                    <label class="control-label col-lg-3" for="[{$sPaymentID}]_[{$smarty.foreach.PaymentDynValues.iteration}]">[{$value->name}]</label>
                    <div class="col-lg-9">
                        <input id="[{$sPaymentID}]_[{$smarty.foreach.PaymentDynValues.iteration}]" type="text" class="form-control textbox" size="20" maxlength="64" name="dynvalue[[{$value->name}]]" value="[{$value->value}]">
                    </div>
                </div>
            [{/foreach}]

            [{block name="checkout_payment_longdesc"}]
                [{if $paymentmethod->oxpayments__oxlongdesc->value|strip_tags|trim}]
                    <div class="clearfix"></div>
                    <div class="alert alert-info col-lg-offset-3 desc">
                        [{$paymentmethod->oxpayments__oxlongdesc->value}]
                    </div>
                [{/if}]
            [{/block}]

            [{block name="checkout_payment_easycreditagreement"}]

                <div class="clearfix"></div>

                [{if $oView->isProfileDataMissing()}]
                    <div class="row easycredit-payment-missing-profile" style="margin-top:1em;margin-bottom:1em;">
                        <div class="col-lg-9 col-lg-offset-3 offset-lg-3">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">[{oxmultilang ident="OXPS_EASY_CREDIT_PAYMENT_PROFILEDATA_CAPTION"}]</h3>
                                </div>
                                <div class="panel-body">

                                    [{if !$oView->hasSalutation()}]
                                        <div class="form-group">
                                            <label class="control-label col-lg-3" for="invadr_oxuser__oxfname">[{oxmultilang ident="TITLE"}]</label>
                                            <div class="col-lg-9">
                                                [{include file="form/fieldset/salutation.tpl" name="ecred[oxuser__oxsal]" class="form-control selectpicker"}]
                                            </div>
                                        </div>
                                    [{/if}]
                                </div>
                            </div>
                        </div>
                    </div>
                [{/if}]

                <div class="form-group">
                    <div class="col-lg-9 col-lg-offset-3 offset-lg-3">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="easycreditinstallmentagreement" id="easycredit_installment_agreement" value=""> [{$oView->getInstallmentAgreementTxt()}]
                                <div id="easycredit_installment_agreement_error" style="display:none;" class="text-danger">[{oxmultilang ident="OXPS_EASY_CREDIT_AGREEMENT_ERROR" }]</div>
                                <div id="is_easycredit_api_version_3" style="display:none;">{{ isEasyCreditAPIV3 }}</div>
                            </label>
                        </div>
                    </div>
                </div>

                [{capture assign="easycreditInstallmentAgreementValidationJS"}]
                    [{strip}]
                        $("#paymentNextStepBottom").click(function(event){
                            $("#easycredit_installment_agreement_error").hide();
                            var success = true;
                            if ($('#is_easycredit_api_version_3').value() ||
                               ($('#easycreditinstallmentagreement').is(':visible') && $('#easycreditinstallmentagreement').is(':not(:checked)'))
                            )
                            {
                                event.preventDefault();
                                $("#easycredit_installment_agreement_error").show();
                            }
                            return true;
                        });
                        $(".payment-optionactivePayment").show();
                    [{/strip}]
                [{/capture}]
                [{oxscript add=$easycreditInstallmentAgreementValidationJS}]

            [{/block}]
        [{/if}]
    </dd>
</dl>
