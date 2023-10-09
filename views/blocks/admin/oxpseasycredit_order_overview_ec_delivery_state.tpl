[{$smarty.block.parent}]
[{if $edit->oxorder__ecredinterestsvalue->value }]
<tr>
    <td class="edittext"></td>
    <td class="edittext" valign="bottom"><br>
        [{oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_DELIVERY_STATE"}]: [{$oView->getDeliveryState($edit)}]
    </td>
</tr>
[{/if}]