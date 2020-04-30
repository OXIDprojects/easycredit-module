[{$smarty.block.parent}]
[{if $edit->oxorder__ecredinterestsvalue->value }]
    <tr>
        <td class="edittext" height="15">[{ oxmultilang ident="OXPS_EASY_CREDIT_SUMTOTAL_INCLUDES_INTERESTS" }]&nbsp;&nbsp;</td>
        <td class="edittext" align="right"><b>[{ $edit->getFInterestsValue() }]</b></td>
        <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{ $currency->name}] [{/if}]</b></td>
    </tr>
[{/if}]