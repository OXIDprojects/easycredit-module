[{block name="email_html_order_owner_interests"}]
    [{assign var="interestsAmount" value=$order->getFInterestsValue()}]
    [{if $interestsAmount}]
        <tr valign="top" bgcolor="#ebebeb">
            <td colspan="[{$iFooterColspan}]" class="odd text-right" align="right">
                [{oxmultilang ident="OXPS_EASY_CREDIT_INTERESTS"}]
            </td>
            <td align="right" class="odd text-right">
                [{$interestsAmount}] [{$currency->sign}]
            </td>
        </tr>
    [{/if}]
[{/block}]
[{$smarty.block.parent}]