[{if $edit->oxorder__oxpaymenttype->value == "easycreditinstallment"}]
    [{if $listitem->oxorderarticles__oxstorno->value == 1}]
        [{assign var="listclass" value=listitem3}]
    [{else}]
        [{assign var="listclass" value=listitem$blWhite}]
    [{/if}]
    <td valign="top" class="[{$listclass}]">
        [{$listitem->oxorderarticles__oxamount->value}]
    </td>
    <td valign="top" class="[{$listclass}]" height="15">
        [{if $listitem->oxarticles__oxid->value}]
            <a href="Javascript:editThis('[{$listitem->oxarticles__oxid->value}]');" class="[{$listclass}]">
        [{/if}]
        [{$listitem->oxorderarticles__oxartnum->value}]
        [{if $listitem->oxarticles__oxid->value}]
            </a>
        [{/if}]
    </td>
    <td valign="top" class="[{$listclass}]">
        [{if $listitem->oxarticles__oxid->value}]
            <a href="Javascript:editThis('[{$listitem->oxarticles__oxid->value}]');" class="[{$listclass}]">
        [{/if}]
            [{$listitem->oxorderarticles__oxtitle->value|oxtruncate:20:""|strip_tags}]
        [{if $listitem->oxarticles__oxid->value}]
            </a>
        [{/if}]
    </td>
    <td valign="top" class="[{$listclass}]">[{$listitem->oxorderarticles__oxselvariant->value}]</td>
    <td valign="top" class="[{$listclass}]">
        [{if $listitem->getPersParams()}]
            [{block name="admin_order_article_persparams"}]
                [{include file="include/persparams.tpl" persParams=$listitem->getPersParams()}]
            [{/block}]
        [{/if}]
    </td>
    <td valign="top" class="[{$listclass}]">[{$listitem->oxorderarticles__oxshortdesc->value|oxtruncate:20:""|strip_tags}]</td>
    [{if $edit->isNettoMode()}]
        <td valign="top" class="[{$listclass}]">
            [{$listitem->getNetPriceFormated()}] <small>[{$edit->oxorder__oxcurrency->value}]</small>
        </td>
        <td valign="top" class="[{$listclass}]">
            [{$listitem->getTotalNetPriceFormated()}] <small>[{$edit->oxorder__oxcurrency->value}]</small>
        </td>
    [{else}]
        <td valign="top" class="[{$listclass}]">
            [{$listitem->getBrutPriceFormated()}] <small>[{$edit->oxorder__oxcurrency->value}]</small>
        </td>
        <td valign="top" class="[{$listclass}]">
            [{$listitem->getTotalBrutPriceFormated()}] <small>[{$edit->oxorder__oxcurrency->value}]</small>
        </td>
    [{/if}]
    <td valign="top" class="[{$listclass}]">
        [{$listitem->oxorderarticles__oxvat->value}]
    </td>
    <td valign="top" class="[{$listclass}]"></td>
    <td valign="top" class="[{$listclass}]"></td>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
