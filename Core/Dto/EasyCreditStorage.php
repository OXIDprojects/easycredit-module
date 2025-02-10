<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Dto;

/**
 * Class capsulates financial information about a certain user process
 * Data Transfer Object
 */
class EasyCreditStorage
{
    /** @var  int */
    private $lastUpdate;

    /** @var string */
    private $tbVorgangskennung;

    /** @var string  */
    private $fachlicheVorgangskennung;

    /** @var string */
    private $authorizationHash;

    /** @var double */
    private $authorizedAmount;

    /** @var float */
    private $interestAmount = 0.0;

    /** @var \stdClass */
    private $allgemeineVorgangsdaten;

    /** @var string */
    private $tilgungsplanTxt;

    /** @var string */
    private $ratenplanTxt;

    public function __construct(
        $tbVorgangskennung,
        $fachlicheVorgangskennung,
        $authorizationHash,
        $authorizedAmount
    ) {

        $this->tbVorgangskennung = $tbVorgangskennung;
        $this->fachlicheVorgangskennung = $fachlicheVorgangskennung;
        $this->authorizationHash = $authorizationHash;
        $this->authorizedAmount = $authorizedAmount;
        $this->lastUpdate = time();
    }

    /**
     * Returns time to expire
     * Ratenkauf-Process expired after 30 minutes. We get a buffer of 1 minute to be certain.
     *
     * @return int
     */
    protected function getStorageExpiredTimeRange()
    {
        return 60 * 29;
    }

    /**
     * Return true if easyCredit storage has expired
     *
     * @return bool
     */
    public function hasExpired()
    {

        if (empty($this->lastUpdate)) {
            return true;
        }
        if (time() > ($this->lastUpdate + $this->getStorageExpiredTimeRange())) {
            return true;
        }
        return false;
    }

    /**
     * Return vorgangskennung of current easyCredit payment process
     *
     * @return string
     */
    public function getTbVorgangskennung()
    {
        return $this->tbVorgangskennung;
    }

    /**
     * Return fachliche Vorgangskennung for easyCredit payment process
     *
     * @return string
     */
    public function getFachlicheVorgangskennung()
    {
        return $this->fachlicheVorgangskennung;
    }

    /**
     * Return paymentHash of current easyCredit payment process
     *
     * @return string
     */
    public function getAuthorizationHash()
    {
        return $this->authorizationHash;
    }

    /**
     * Return authorized amount by current easyCredit payment process
     *
     * @return float
     */
    public function getAuthorizedAmount()
    {
        return $this->authorizedAmount;
    }

    /**
     * Returns interests of current easyCredit payment process
     *
     * @return float
     */
    public function getInterestAmount()
    {
        return $this->interestAmount;
    }

    /**
     * Sets interests of current easyCredit payment process
     *
     * @param float $interestAmount
     */
    public function setInterestAmount($interestAmount)
    {
        $this->interestAmount = $interestAmount;
    }

    /**
     * Returns allgemeine Vorgangsdaten of current easyCredit payment process
     *
     * @return \stdClass
     */
    public function getAllgemeineVorgangsdaten()
    {
        return $this->allgemeineVorgangsdaten;
    }

    /**
     * Returns Tilgungsplan of current easyCredit payment process
     *
     * @return string
     */
    public function getTilgungsplanTxt()
    {
        return $this->tilgungsplanTxt;
    }

    /**
     * Returns payment plan (user formatted) of current easyCredit payment process
     *
     * @return string
     */
    public function getRatenplanTxt()
    {
        return $this->ratenplanTxt;
    }

    /**
     * Sets AllgemeineVorgangsdaten
     *
     * @param \stdClass $allgemeineVorgangsdaten
     */
    public function setAllgemeineVorgangsdaten($allgemeineVorgangsdaten)
    {
        $this->allgemeineVorgangsdaten = $allgemeineVorgangsdaten;
    }

    /**
     * Sets Tilgungsplan
     *
     * @param string $tilgungsplanTxt
     */
    public function setTilgungsplanTxt($tilgungsplanTxt)
    {
        $this->tilgungsplanTxt = $tilgungsplanTxt;
    }

    /**
     * Sets payment plan (user formatted)
     *
     * @param string $ratenplanTxt
     */
    public function setRatenplanTxt($ratenplanTxt)
    {
        $this->ratenplanTxt = $ratenplanTxt;
    }
}
