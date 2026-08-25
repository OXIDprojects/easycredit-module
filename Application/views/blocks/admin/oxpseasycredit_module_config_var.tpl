[{if $oView->easycreditIsApiKeyUsable()}]
    <div style="color: green; margin-bottom: 20px">[{$validateEcCredentials}]</div>
[{else}]
    <div style="color: red; margin-bottom: 20px">[{$validateEcCredentials}]</div>
[{/if}]
[{$smarty.block.parent}]
