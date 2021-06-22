<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>
<form method="post" action="[{$oViewConf->getSelfLink()}]">
    <div class="hidden">
        [{$oViewConf->getHiddenSid()}]
        <input type="hidden" name="cl" value="easycreditoverview_list">
        <input type="hidden" name="fnc" value="loadFilteredOrders">
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
                <option value="-1" selected="selected">Bitte wählen</option>
                [{foreach from=$states key=state item=text}]
                <option value="[{$state}]" [{if $filterparams.state == $state}] selected[{/if}]>[{oxmultilang ident=$text}]</option>
                [{/foreach}]
            </select>
        </div>
        <div class="col-3 ">
            <br><input type="submit" class="btn btn-primary">
        </div>
    </div>
</form>
