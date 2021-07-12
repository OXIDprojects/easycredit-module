<td valign="top" class="listfilter first" height="20">
    <div class="r1">
        <div class="b1">
            <select name="ecorders" class="folderselect" onChange="document.search.submit();">
                <option value="all" [{if $ecorders == 'all'}] selected [{/if}]>Alle</option>
                <option value="only"[{if $ecorders == 'only'}] selected [{/if}]>Nur Easy Credit Bestellungen</option>
                <option value="not"[{if $ecorders == 'not'}] selected [{/if}]>Keine EasyCredit Bestellungen</option>
            </select>
        </div>
    </div>
</td>

[{$smarty.block.parent}]