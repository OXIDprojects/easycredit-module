<?php

/*
 * This file is part of OXID eSales AG EasyCredit module
 * Copyright © OXID eSales AG. All rights reserved.
 *
 * Licensed under the GNU GPL v3 - See the file LICENSE for details.
 */

namespace OxidProfessionalServices\EasyCredit\Core\Api;

use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDic;
use OxidProfessionalServices\EasyCredit\Core\Di\EasyCreditDicFactory;

class EasyCreditResponseValidator
{
    public const VALIDATION_KEY_FIELDNAME = 'fieldname';
    public const VALIDATION_KEY_REQUIRED = 'required';
    public const VALIDATION_KEY_REQUIRED_VALUE = 'requiredValue';
    public const VALIDATION_KEY_ERROR_EXCEPTION = 'errorException';
    public const VALIDATION_KEY_EXCEPTION_MESSAGE = 'exceptionMessage';

    /** @var array */
    private $validationScheme;

    /** @var EasyCreditDic */
    private $dic = false;

    /**
     * EasyCreditResponseValidator constructor.
     *
     * @param array $validationScheme
     */
    public function __construct(array $validationScheme)
    {
        $this->validationScheme = $validationScheme;
    }

    /**
     * Validates the response against the validation scheme of this validator.
     *
     * @param \stdClass $response
     */
    public function validate($response)
    {
        if (!isset($this->validationScheme) || !is_array($this->validationScheme) || !count($this->validationScheme)) {
            return;
        }

        foreach ($this->validationScheme as $validation) {
            $this->checkValidation($response, $validation);
        }
    }

    /**
     * Validates the response against a single validation criteria of the scheme of this validator.
     *
     * @param \stdClass $response
     * @param array $validation
     */
    protected function checkValidation($response, $validation)
    {
        $errorExceptionClassname = $this->getExceptionClassname($validation);
        $validationExceptionMessage = $this->getValidationExceptionMessage($validation);

        $required = $this->isRequired($validation);
        $fieldname = $this->getFieldname($validation);

        $this->checkField($fieldname, $required, $errorExceptionClassname, $response, $validationExceptionMessage);

        $requiredValue = $this->getRequiredValue($validation);

        $this->checkFieldValue($fieldname, $requiredValue, $errorExceptionClassname, $response, $validationExceptionMessage);
    }

    private function getExceptionClassname($validation)
    {
        return $validation[self::VALIDATION_KEY_ERROR_EXCEPTION] ?? EasyCreditValidationException::class;
    }

    private function getValidationExceptionMessage(array $validation)
    {
        return $validation[self::VALIDATION_KEY_EXCEPTION_MESSAGE] ?? null;
    }

    private function isRequired(array $validation)
    {
        return $validation[self::VALIDATION_KEY_REQUIRED] ?? false;
    }

    private function getFieldname(array $validation)
    {
        return $validation[self::VALIDATION_KEY_FIELDNAME] ?? false;
    }

    private function getRequiredValue(array $validation)
    {
        return $validation[self::VALIDATION_KEY_REQUIRED_VALUE] ?? false;
    }

    private function checkField($fieldname, $required, $errorExceptionClassname, $response, $exceptionMessage)
    {
        if ($required && $fieldname && !isset($response->$fieldname)) {
            $responseMessage = $this->getExceptionMessage($response, $exceptionMessage);
            $this->log($responseMessage ? $responseMessage : "Required field $fieldname not found in response.");
            throw new $errorExceptionClassname(Registry::getLang()->translateString('OXPS_EASY_CREDIT_VALIDATION_ERROR'));
        }
    }

    private function checkFieldValue($fieldname, $requiredValue, $errorExceptionClassname, $response, $exceptionMessage)
    {
        if ($requiredValue && $response->$fieldname != $requiredValue) {
            $invalidValue = serialize($response->$fieldname);
            $responseMessage = $this->getExceptionMessage($response, $exceptionMessage);
            $this->log($responseMessage ? $responseMessage : "Required field '$fieldname' has invalid value '$invalidValue'.");
            throw new $errorExceptionClassname(Registry::getLang()->translateString('OXPS_EASY_CREDIT_VALIDATION_ERROR'));
        }
    }

    private function getExceptionMessage($response, $exceptionMessage)
    {
        $responseMessage = $this->getResponseMessage($response);
        return $responseMessage ? $responseMessage : $exceptionMessage;
    }

    protected function getResponseMessage($response)
    {
        if (isset($response->wsMessages)) {
            $wsMessages = $response->wsMessages;
            return $this->getMessages($wsMessages);
        }
    }

    protected function getMessages($wsMessages)
    {
        if (isset($wsMessages->messages)) {
            $messages = $wsMessages->messages;
            return $this->getMessage($messages);
        }
    }

    protected function getMessage($messages)
    {
        if (is_array($messages) && count($messages)) {
            $message = array_shift($messages);
            return $this->getInfo($message);
        }
    }

    protected function getInfo($message)
    {
        if ($message && isset($message->infoFuerBenutzer)) {
            return $message->infoFuerBenutzer;
        }
    }

    /**
     * Returns the dic container.
     *
     * @return EasyCreditDic
     * @throws SystemComponentException
     */
    protected function getDic()
    {
        if (!$this->dic) {
            $this->dic = EasyCreditDicFactory::getDic();
        }

        return $this->dic;
    }

    protected function log($message)
    {
        $this->getDic()->getLogging()->log($message);
    }
}
