<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @category      module
 * @package       easycredit
 * @author        OXID Professional Services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

/**
 * Class oxpsEasyCreditWebServiceClient
 */
class oxpsEasyCreditWebServiceClient extends oxpsEasyCreditHttpClient
{
    /**
     * @var string Url for the method relatively to the base url.
     */
    protected $_function = '';

    /**
     * @var string Url for the method relatively to the base url.
     */
    protected $_httpmethod = '';

    /**
     * @var oxpsEasyCreditResponseValidator
     */
    protected $responseValidator;

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getHttpmethod()
    {
        return $this->_httpmethod;
    }

    /**
     * @param string $httpmethod
     */
    public function setHttpmethod($httpmethod)
    {
        $this->_httpmethod = $httpmethod;
    }

    public function setResponseValidator($responseValidator)
    {
        $this->responseValidator = $responseValidator;
    }

    /**
     * Sets the function for the web service.
     *
     * @param string $function
     * @param array $sprintfArgs non-assoc array of arguments to be unpacked for sprintf, e.g. order-id for capture
     * @param array $queryArgs assoc query params array
     *
     * @throws oxpsEasyCreditCurlException
     */
    public function setFunction($function, array $sprintfArgs = null, array $queryArgs = null)
    {
        if (isset($sprintfArgs) && is_array($sprintfArgs) && count($sprintfArgs)) {

            $this->checkParameters($function, $sprintfArgs);

            if (1 === count($sprintfArgs)) {
                $function = sprintf($function, $sprintfArgs[0]);
            } elseif (2 === count($sprintfArgs)) {
                $function = sprintf($function, $sprintfArgs[0], $sprintfArgs[1]);
            }
        }

        $function .= $this->addQueryArgs($queryArgs);

        $this->_function = $function;
    }

    protected function checkParameters($function, $sprintfArgs)
    {
        foreach ($sprintfArgs as $k => $urlParameter) {
            if (!isset($urlParameter)) {
                throw new oxpsEasyCreditCurlException("Parameter $k for curl function $function was empty");
            }
        }
    }

    /**
     * Gets the REST-function (i.e. the URL-path)
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getFunction()
    {
        return $this->_function;
    }

    /**
     * Executes the request.
     *
     * @param mixed $data
     *
     * @return stdClass
     *
     * @throws oxpsEasyCreditCurlException
     * @throws oxSystemComponentException
     */
    public function execute($data = null)
    {
        $response = $this->executeJsonRequest($this->_httpmethod, $this->_function, $data);
        if ($this->responseValidator) {
            $this->responseValidator->validate($response);
        }
        return $response;
    }

    protected function addQueryArgs($queryArgs)
    {
        if (isset($queryArgs) && is_array($queryArgs) && count($queryArgs)) {
            return '?' . http_build_query($queryArgs);
        }

        return "";
    }
}