<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>

[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign box="list"}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
    [{/if}]

<script type="text/javascript">
    <!--
    window.onload = function () {
        top.reloadEditFrame();
        [{if $updatelist == 1}]
        top.oxid.admin.updateList('[{ $oxid }]');
        [{ /if}]
    }
    //-->
</script>

<form name="search" id="search" action="[{$oViewConf->getSelfLink()}]" method="post">
    <div class="hidden">
        [{$oViewConf->getHiddenSid()}]
        <input type="hidden" name="cl" value="easycreditoverview_list">
        <input type="hidden" name="fnc" value="setFilter">
        <input type="hidden" name="oxid" value="[{$oxid}]">
    </div>
    <div class="row">
        <div class="col-2">
            <label for="date_from">Startdatum</label><br>
            <input id="date_from" name="ecfilter[date_from]" type="date" value="[{ $filterparams.start }]"/>
        </div>
        <div class="col-2 ">
            <label for="date_to">Enddatum</label><br>
            <input id="date_to" name="ecfilter[date_to]" type="date" value="[{$filterparams.end}]"/>
        </div>
        <div class="col-4">
            <label for="ec_state">EasyCredit Status</label><br>
            <select id="ec_state" name="ecfilter[ec_state]" class="form-select">
                <option value="" selected="selected">Bitte wählen</option>
                [{foreach from=$states key=state item=text}]
                <option value="[{$state}]" [{if $filterparams.state == $state}] selected[{/if}]>
                    [{oxmultilang ident=$text}]
                </option>
                [{/foreach}]
            </select>
        </div>
        <div class="col-3 ">
            <br><input type="submit" class="btn btn-primary">
        </div>
    </div>
</form>

<div id="liste">
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <colgroup>
        <col width="95%">
        </colgroup>
        <form name="search" id="search" action="[{ $oViewConf->getSelfLink() }]" method="post">
            [{include file="_formparams.tpl" cl="easycreditoverview_list" lstrt=$lstrt actedit=$actedit oxid=$order-oxorderid fnc="" language=$actlang editlanguage=$actlang}]

            [{foreach from=$ecorders item=order}]
            <tr id="row.[{$_cnt}]" class="listitem">
                <td valign="top" class="[{$listclass}]" height="15">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{$order->oxorderid}]');" class="[{$listclass}]">
                            [{$order->bestelldatum}]
                        </a>
                    </div>
                </td>
                <td valign="top" class="[{$listclass}]" height="15">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{$order->oxorderid}]');" class="[{$listclass}]">
                            [{$order->kundeVorname}] [{$order->kundeNachname}]
                        </a>
                    </div>
                </td>
                <td valign="top" class="[{$listclass}]" height="15">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{$order->oxorderid}]');" class="[{$listclass}]">
                            [{$order->bestellwertAktuell}]
                        </a>
                    </div>
                </td>
                <td valign="top" class="[{$listclass}]" height="15">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{$order->oxorderid}]');" class="[{$listclass}]">
                            [{$order->vorgangskennungFachlich}]
                        </a>
                    </div>
                </td>
                <td valign="top" class="[{$listclass}]" height="15">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{$order->oxorderid}]');" class="[{$listclass}]">
                            [{$order->haendlerstatusV2}]
                        </a>
                    </div>
                </td>
                <td valign="top" class="[{$listclass}]" height="15">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{$order->oxorderid}]');" class="[{$listclass}]">
                            [{$order->lieferdatum}]
                        </a>
                    </div>
                </td>
            </tr>
            [{/foreach}]

            [{if $blWhite == "2"}]
            [{assign var="blWhite" value=""}]
            [{else}]
            [{assign var="blWhite" value="2"}]
            [{/if}]
        </form>
        [{include file="pagenavisnippet.tpl"}]
    </table>
</div>

</body>
</html>
