<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\CrossCutting;

class EasyCreditLogging
{
    public const LOG_CONFIG_LOG_DIR = 'logDir';
    public const LOG_CONFIG_LOG_ENABLED = 'logEnabled';

    public const EASYCREDIT_DEFAULT_LOG_FILENAME = 'easycredit.log';

    /**
     * @var string
     */
    protected $logConfig;

    /**
     * @var string name for the log file
     */
    protected $logFileName = self::EASYCREDIT_DEFAULT_LOG_FILENAME;

    /**
     * Logging constructor.
     *
     * @param array $logConfig
     */
    public function __construct(array $logConfig)
    {
        $this->logConfig = $logConfig;
    }

    /**
     * Set the log file name.
     *
     * @param string $logFileName
     *
     * @codeCoverageIgnore
     */
    public function setLogFileName($logFileName)
    {
        $this->logFileName = $logFileName;
    }

    public function logRestRequest($encodedData, $encodedResponse, $serviceUrl, $duration)
    {
        if ($this->isLogEnabled()) {
            return $this->log($this->buildRequestString($encodedData, $encodedResponse, $serviceUrl, $duration));
        }
    }

    protected function buildRequestString($encodedData, $encodedResponse, $serviceUrl, $duration)
    {
        $result = $serviceUrl . PHP_EOL;
        $result .= str_repeat('=', 60) . PHP_EOL;

        if ($encodedData) {
            $result .= 'data:' . PHP_EOL;
            $result .= $this->buildPrettyJsonString($encodedData);
        }

        if ($encodedResponse) {
            $result .= 'response:' . PHP_EOL;
            $result .= $this->buildPrettyJsonString($encodedResponse);
        }

        $result .= 'took ' . round($duration * 1_000) . ' milliseconds' . PHP_EOL;

        $result .= str_repeat('=', 60) . PHP_EOL . PHP_EOL;

        return $result;
    }

    public function log($msg)
    {
        return file_put_contents(
            $this->getLogFilePath(),
            date("Y-m-d H:i:s", time()) . ': ' . $msg . PHP_EOL,
            FILE_APPEND
        );
    }

    protected function getLogFilePath()
    {
        return $this->getLogDir() . $this->logFileName;
    }

    protected function getLogDir()
    {
        if (isset($this->logConfig[self::LOG_CONFIG_LOG_DIR])) {
            return $this->logConfig[self::LOG_CONFIG_LOG_DIR];
        }

        return "";
    }

    protected function buildPrettyJsonString($jsonString)
    {
        $result = str_repeat('-', 40) . PHP_EOL;

        $jsonObject = json_decode($jsonString);
        $prettyString = json_encode($jsonObject, JSON_PRETTY_PRINT);
        $result .= $prettyString . PHP_EOL;

        $result .= str_repeat('-', 40) . PHP_EOL;

        return $result;
    }

    protected function isLogEnabled()
    {
        return (isset($this->logConfig[self::LOG_CONFIG_LOG_ENABLED]) && $this->logConfig[self::LOG_CONFIG_LOG_ENABLED]);
    }
}
