[{if $oView->easycreditIsApiV3()}]
    [{if $oView->easycreditIsApiKeyUsable()}]
        <div style="color: green; margin-bottom: 20px">[{$validateEcCredentials}]</div>
    [{else}]
        <div style="color: red; margin-bottom: 20px">[{$validateEcCredentials}]</div>
    [{/if}]
[{/if}]
[{$smarty.block.parent}]
