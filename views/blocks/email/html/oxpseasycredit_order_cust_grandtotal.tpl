[{block name="email_html_order_cust_interests"}]
    [{assign var="interestsAmount" value=$order->getFInterestsValue()}]
    [{if $interestsAmount}]
    <tr valign="top" bgcolor="#ebebeb" bgcolor="#ebebeb">
        <td align="right" colspan="[{$iFooterColspan}]" class="text-right odd">[{oxmultilang ident="OXPS_EASY_CREDIT_INTERESTS"}]</td>
        <td align="right" class="odd text-right">[{$interestsAmount}] [{$currency->sign}]</td>
    </tr>
    [{/if}]
[{/block}]
[{$smarty.block.parent}]