[{$smarty.block.parent}]
[{block name="checkout_basketcontents_interests"}]
    [{assign var="interestsAmount" value=$oxcmp_basket->getInterestsAmount()}]
    [{if $interestsAmount }]
        <tr>
            <th>[{ oxmultilang ident="OXPS_EASY_CREDIT_INTERESTS" suffix="COLON" }]</th>
            <td id="interestsAmount">[{oxprice price=$interestsAmount currency=$currency}]</td>
        </tr>
    [{/if}]
[{/block}]