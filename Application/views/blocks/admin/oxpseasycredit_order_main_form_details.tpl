[{if $edit->oxorder__oxpaymenttype->value == "easycreditinstallment"}]
    <tr>
        <td class="edittext">
            [{oxmultilang ident="ORDER_MAIN_IPADDRESS"}]
        </td>
        <td class="edittext">
            [{$edit->oxorder__oxip->value}]
        </td>
    </tr>
    <tr>
        <td class="edittext">
            [{oxmultilang ident="GENERAL_ORDERNUM"}]
        </td>
        <td class="edittext">
            <input type="text" class="editinput" size="15" maxlength="[{$edit->oxorder__oxordernr->fldmax_length}]" name="editval[oxorder__oxordernr]" value="[{$edit->oxorder__oxordernr->value}]" [{$readonly}]>
            [{oxinputhelp ident="HELP_GENERAL_ORDERNUM"}]
        </td>
    </tr>
    <tr>
        <td class="edittext">
        [{oxmultilang ident="ORDER_MAIN_BILLNUM"}]
        </td>
        <td class="edittext">
            <input type="text" class="editinput" size="15" maxlength="[{$edit->oxorder__oxbillnr->fldmax_length}]" name="editval[oxorder__oxbillnr]" value="[{$edit->oxorder__oxbillnr->value}]" [{$readonly}]>
            [{oxinputhelp ident="HELP_ORDER_MAIN_BILLNUM"}]
        </td>
    </tr>
    <tr>
        <td class="edittext">
            [{oxmultilang ident="ORDER_MAIN_DISCOUNT"}]
        </td>
        <td class="edittext">
            <input type="text" class="editinput" size="15" maxlength="[{$edit->oxorder__oxdiscount->fldmax_length}]" name="editval[oxorder__oxdiscount]" value="[{$edit->oxorder__oxdiscount->value}]" readonly disabled> ([{$edit->oxorder__oxcurrency->value}])
            [{oxinputhelp ident="HELP_ORDER_MAIN_DISCOUNT"}]
        </td>
    </tr>
    [{foreach from=$aVouchers item=sVoucher}]
        <tr>
            <td class="edittext">
                [{oxmultilang ident="ORDER_MAIN_USERVOUCHER"}]:&nbsp;
            </td>
            <td class="edittext">
                [{$sVoucher}]
            </td>
        </tr>
    [{/foreach}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]
