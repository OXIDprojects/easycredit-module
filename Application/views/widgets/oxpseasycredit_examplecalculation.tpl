[{if $oView->isAjax()}]
    [{if $oView->hasExampleCalculation()}]
        <div id="[{$oView->getViewParameter('placeholderId')}]" class="easycredit-widget">
            <span class="easycredit-suffix">[{oxmultilang ident="OXPS_EASY_CREDIT_FINANCE_FROM"}]</span>
            <span class="easycredit-rate">[{$oView->getExampleCalculationRate()}] € / Monat</span>
            <br>
            <a class="easycredit-link">[{oxmultilang ident="OXPS_EASY_CREDIT_MORE_INFO"}]</a>
        </div>

        <div id="easycredit-example-dialog" title="[{oxmultilang ident="OXPS_EASY_CREDIT_EXAMPLE_DIALOG_TITLE"}]">
        </div>
    [{/if}]
[{else}]
    [{oxstyle include=$oViewConf->getModuleUrl('oxpseasycredit','out/src/css/oxpseasycredit_style.css') priority=10}]
    [{if $oView->getUseOwnjQueryUI()}]
        [{oxstyle include=$oViewConf->getModuleUrl('oxpseasycredit','out/src/css/base/jquery-ui.css') priority=9}]
        [{oxscript include=$oViewConf->getModuleUrl('oxpseasycredit','out/src/css/base/jquery-ui.js') priority=9}]
    [{/if}]

    <div id="[{$oView->getViewParameter('placeholderId')}]"></div>
    [{capture assign="pageScript" priority=10}]
        $(function() {
            function openExampleCalculationDialog (easyCreditPopupUrl) {
                $.get('[{$oView->getPopupAjaxUrl()}]',
                    function (data) {
                        $('#easycredit-example-dialog').html(data);

                        $('#easycredit-example-dialog').dialog({
                            close: true,
                            modal: true,
                            width: 570,
                            height: 786
                        });
                    }
                )
            }

            $.get('[{$oView->getAjaxUrl()}]',
                function (data) {
                    $('#[{$oView->getViewParameter('placeholderId')}]').replaceWith(data);
                    $('#[{$oView->getViewParameter('placeholderId')}]').on('click', '.easycredit-link', openExampleCalculationDialog)
                }
            )
        });
    [{/capture}]
    [{oxscript add=$pageScript}]
[{/if}]