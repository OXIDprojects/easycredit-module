[{if !isset($oConfig)}]
    [{assign var="oConfig" value=$oViewConf->getConfig()}]
[{/if}]

[{$smarty.block.parent}]
[{if $oConfig->getConfigParam('oxpsECExampleCalcArticle')}]
    [{oxid_include_widget cl="oxpsEasyCreditExampleCalculation" _parent=$oView->getClassName() articleId=$oDetailsProduct->getId() placeholderId="oxpseasycredit-example-product"}]
[{/if}]