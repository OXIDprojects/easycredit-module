[{if !isset($oConfig)}]
    [{assign var="oConfig" value=$oViewConf->getConfig()}]
[{/if}]

[{$smarty.block.parent}]
[{if $oConfig->getConfigParam('oxpsECExampleCalcMinibasket')}]
    [{oxid_include_widget cl="oxpsEasyCreditExampleCalculation" _parent=$oView->getClassName() placeholderId="oxpseasycredit-example-minibasket"}]
[{/if}]