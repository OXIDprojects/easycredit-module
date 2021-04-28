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
 * Class HttpClient: Client for the easycredit webservice.
 */
class oxpsEasyCreditHttpClient
{
    /**
     * @var resource Curl session handle.
     */
    protected $_handle;

    /**
     * @var string[] Additional request headers.
     */
    protected $_requestHeaders = array();

    /**
     * @var string Base url for the request.
     */
    protected $_baseUrl;

    /**
     * @var oxpsEasyCreditLogging
     */
    protected $logging;

    /**
     * Sets the logging instance.
     *
     * @param oxpsEasyCreditLogging $logging
     */
    public function setLogging(oxpsEasyCreditLogging $logging)
    {
        $this->logging = $logging;
    }

    /**
     * Sets the base url for the service
     *
     * @param string $url
     */
    public function setBaseUrl($url)
    {
        $this->_baseUrl = $url;
    }

    /**
     * Sets the headers for the service requests.
     *
     * @param string[] $headers
     */
    public function setRequestHeaders(array $headers)
    {
        $this->_requestHeaders = $headers;
    }

    /**
     * Performs an request to a server with an JSON string.
     *
     * @param string $httpMethod
     * @param string $serviceUrl
     * @param mixed $data
     *
     * @return stdClass
     * @throws oxpsEasyCreditCurlException
     * @throws oxSystemComponentException
     */
    public function executeJsonRequest($httpMethod, $serviceUrl, $data = null)
    {
        if (!$httpMethod) {
            throw new oxpsEasyCreditCurlException('$httpMethod was empty');
        }
        if (!$serviceUrl) {
            throw new oxpsEasyCreditCurlException('$serviceUrl was empty');
        }

        $encodedData = null;
        if ($data) {
            $encodedData = json_encode($data, JSON_PRETTY_PRINT);
        }

        $startTime       = microtime(true);
        $encodedResponse = $this->executeHttpRequest($httpMethod, $serviceUrl, $encodedData);
        $duration        = microtime(true) - $startTime;
        $response        = json_decode($encodedResponse);
        $this->logging->logRestRequest($encodedData, $encodedResponse, $serviceUrl, $duration);
        return $response;
    }

    /**
     * Performs an HTTP request to a server.
     *
     * @param string $httpMethod
     * @param string $serviceUrl
     * @param string $data
     *
     * @return string
     * @throws oxpsEasyCreditCurlException
     */
    public function executeHttpRequest($httpMethod, $serviceUrl, $data = null)
    {
        if (!$httpMethod) {
            throw new oxpsEasyCreditCurlException('$httpMethod was empty');
        }
        if (!$serviceUrl) {
            throw new oxpsEasyCreditCurlException('$serviceUrl was empty');
        }

        $this->init($this->_baseUrl . $serviceUrl);

        $httpMethod = strtoupper($httpMethod);
        $this->handleHttpMethod($httpMethod, $data);

        $this->addHeaders();

        $response = $this->curl_exec();
        $this->close();

        return $response;
    }

    protected function handleHttpMethod($httpMethod, $data)
    {
        if ('POST' == $httpMethod) {
            $this->setPost();
            $this->setPostData($data);
        } elseif ('GET' == $httpMethod) {
            $this->setGet();
        } else {
            throw new oxpsEasyCreditCurlException('Unknown httpMethod ' . $httpMethod);
        }
    }

    /**
     * Adds additional headers for the request.
     *
     * @throws oxpsEasyCreditCurlException
     */
    protected function addHeaders()
    {
        curl_setopt($this->_handle, CURLOPT_HTTPHEADER, $this->_requestHeaders);
        $this->catchRequestError();
    }

    /**
     * Executes the curl request.
     *
     * @return string
     * @throws oxpsEasyCreditCurlException
     */
    protected function curl_exec()
    {
        $response = curl_exec($this->_handle);
        $this->catchRequestError();

        return $response;
    }

    /**
     * Closes a curl session handle.
     */
    protected function close()
    {
        curl_close($this->_handle);
    }

    /**
     * Creates a curl session handle.
     *
     * @param $url
     *
     * @throws oxpsEasyCreditCurlException
     */
    protected function init($url)
    {
        $this->_handle = curl_init($url);
        $this->catchRequestError();
        curl_setopt($this->_handle, CURLOPT_RETURNTRANSFER, true);
        $this->catchRequestError();
    }

    /**
     * Sets the POST data for a curl request.
     *
     * @param string $data
     *
     * @throws oxpsEasyCreditCurlException
     */
    protected function setPostData($data)
    {
        if (!$data) {
            $data = array();
        }

        curl_setopt($this->_handle, CURLOPT_POSTFIELDS, $data);
        $this->catchRequestError();
    }

    /**
     * Sets the method to POST.
     *
     * @throws oxpsEasyCreditCurlException
     */
    protected function setPost()
    {
        curl_setopt($this->_handle, CURLOPT_POST, true);
        $this->catchRequestError();
    }

    /**
     * Sets the method to GET.
     *
     * @throws oxpsEasyCreditCurlException
     */
    protected function setGet()
    {
        curl_setopt($this->_handle, CURLOPT_HTTPGET, true);
        $this->catchRequestError();
    }

    /**
     * Tests if there was an curl error.
     *
     * @throws oxpsEasyCreditCurlException
     */
    protected function catchRequestError()
    {
        if (curl_errno($this->_handle) != 0) {
            throw new oxpsEasyCreditCurlException(curl_error($this->_handle), curl_errno($this->_handle));
        }
    }
}