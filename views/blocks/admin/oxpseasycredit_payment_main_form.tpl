[{$smarty.block.parent}]
[{if $edit && $edit->isEasyCreditInstallment() }]
    <tr>
        <td class="edittext" width="70">
            [{ oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_VALUE" }]
        </td>
        <td class="edittext">
            [{assign var="aquisitionBorderValue" value=$edit->getFEasyCreditAquisitionBorderValue()}]
            [{if $aquisitionBorderValue }]
                [{$aquisitionBorderValue}] [{ oxinputhelp ident="HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_VALUE" }]
            [{else}]
                - [{ oxinputhelp ident="HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_VALUE" }]
            [{/if}]
        </td>
    </tr>
    <tr>
        <td class="edittext" width="70">
            [{ oxmultilang ident="OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_LASTUPDATE" }]
        </td>
        <td class="edittext">
            [{assign var="aquisitionBorderLastUpdate" value=$edit->getFEasyCreditAquisitionBorderLastUpdate()}]
            [{if $aquisitionBorderLastUpdate }]
                [{$aquisitionBorderLastUpdate}] [{ oxinputhelp ident="HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_LASTUPDATE" }]
            [{else}]
                - [{ oxinputhelp ident="HELP_OXPS_EASY_CREDIT_ADMIN_AQUISITIONBORDER_LASTUPDATE" }]
            [{/if}]
        </td>
    </tr>
[{/if}]