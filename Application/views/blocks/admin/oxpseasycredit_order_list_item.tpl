[{if $listitem->oxorder__oxstorno->value == 1}]
    [{assign var="listclass" value=listitem3}]
    [{else}]
    [{if $listitem->blacklist == 1}]
    [{assign var="listclass" value=listitem3}]
    [{else}]
    [{assign var="listclass" value=listitem$blWhite}]
    [{/if}]
    [{/if}]
[{if $listitem->getId() == $oxid}]
    [{assign var="listclass" value=listitem4}]
[{/if}]

<td valign="top" class="[{$listclass}] ecorder" height="15"><div class="listitemfloating">
        &nbsp;<a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');" class="[{$listclass}]">
            [{if $listitem->oxorder__ecredfunctionalid->value}]
            [{oxmultilang ident="EASY_CREDIT_ORDER_TYPE"}]
            [{/if}]
        </a>
    </div>
</td>

[{$smarty.block.parent}]