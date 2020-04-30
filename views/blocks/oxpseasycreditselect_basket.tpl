[{if !isset($oConfig)}]
    [{assign var="oConfig" value=$oViewConf->getConfig()}]
[{/if}]

[{if $oConfig->getConfigParam('oxpsECExampleCalcBasket')}]
    [{oxid_include_widget cl="oxpsEasyCreditExampleCalculation" _parent=$oView->getClassName() placeholderId="oxpseasycredit-example-basket"}]
[{/if}]
[{$smarty.block.parent}]