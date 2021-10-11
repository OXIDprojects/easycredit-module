[{$smarty.block.parent}]
[{if $edit->oxorder__ecredinterestsvalue->value }]
    <table border="0" cellspacing="0" cellpadding="0" id="order.info">
        <tr>
            <td class="edittext" height="15">[{ oxmultilang ident="OXPS_EASY_CREDIT_SUMTOTAL_INCLUDES_INTERESTS" }]&nbsp;&nbsp;</td>
            <td class="edittext" align="right"><b>[{ $edit->getFInterestsValue() }]</b></td>
            <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] &euro; [{/if}]</b></td>
        </tr>
    </table>
[{/if}]