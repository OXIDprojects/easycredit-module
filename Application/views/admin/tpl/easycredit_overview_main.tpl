<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>


[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]
[{assign var="ecorder" value=$orderdata.ecorder}]
[{assign var="ecorder" value=$orderdata.}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
    [{/if}]

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="cl" value="easycreditoverview_main">
    <input type="hidden" name="lstrt" value="[{$lstrt}]">
    <input type="hidden" name="actedit" value="[{$actedit}]">
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="fnc" value="[{$fnc}]">
    <input type="hidden" name="language" value="[{$language}]">
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]">
    <input type="hidden" name="delshopid" value="[{$delshopid}]">
    <input type="hidden" name="updatenav" value="[{$updatenav}]">
</form>

<form name="myedit" enctype="multipart/form-data" id="myedit" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="cl" value="easycreditoverview_main">
    <input type="hidden" name="fnc" value="">
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="editval[oxps_quotagroupset__oxid]" value="[{ $oxid }]">
    <input type="hidden" name="language" value="[{$actlang}]">

    <div class="">
        <div class="row">
            <div class="col-12">
                <h2>Bestellinformation</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-xs-12">
                Bestellung *BstNr* vom dd-mm-YYY
            </div>
            <div class="col-6 col-xs-12">
                Easy Credit Bestellnummer *ECBSTNR*:<br/>
                Fachliche Kennung: *YXCV7T*
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="row">
                    <div class="col-1">
                        Kunde:
                    </div>
                    <div class="col">
                        Firma .inc<br/>
                        Vorname Nachname<br/>
                        Straße Nr<br/>
                        PLZ Stadt<br/>
                        <br/>
                        email@domain.co
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="row">
                    <div class="col">
                        Auflistung Artikel und Preis
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        Auflistung Preise<br/>
                        Gesamt<br/>
                        Netto<br/>
                        Brutto<br/>
                        Mwst<br/>
                        Zinsen<br/>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="row">
                    <div class="col-4">
                        Easy Credit Bestelldatum
                    </div>
                    <div class="col-8">
                        ECOrderDate
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        Ursprünglicher Bestellwert
                    </div>
                    <div class="col-8">
                        700
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        Aktueller Bestellwert
                    </div>
                    <div class="col-8">
                        650
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        Lieferung gemeldet am:
                    </div>
                    <div class="col-8">
                        noch nicht gemeldet| Datum
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        Widerrufener Betrag
                    </div>
                    <div class="col-8">
                        50
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        Widerruf gemeldet
                    </div>
                    <div class="col-8">
                        nicht gemeldet | Datum
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        Widerruf gebucht
                    </div>
                    <div class="col-8">
                        nicht gebucht | Datum
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>

[{debug}]

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
