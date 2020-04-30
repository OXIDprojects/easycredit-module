<?php

class TestClient
{
    const BASE_URL = 'https://ratenkauf.easycredit.de/ratenkauf-ws/rest';

    /** @var string */
    private $endpoint;

    /** @var string */
    private $method;

    /** @var string */
    private $data;

    /** @var array|null */
    private $headers;

    /**
     * TestClient constructor.
     *
     * @param string $endpoint
     * @param string $method
     * @param string|null $data
     * @param array|null $headers
     */
    public function __construct(string $endpoint, string $method, string $data = null, array $headers = null)
    {
        $this->endpoint = $endpoint;
        $this->method   = $method;
        $this->data     = $data;
        $this->headers  = $headers;
    }

    /**
     * Executes the request.
     *
     * @return mixed
     */
    public function execute()
    {
        $ch = curl_init(self::BASE_URL . $this->endpoint);

        $this->setMethod($ch);
        $this->setHeaders($ch);
        $this->setData($ch);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $r = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $httpCode . "\n" . json_encode(json_decode($r), JSON_PRETTY_PRINT);
    }

    protected function setMethod($ch)
    {
        switch (strtoupper($this->method)) {
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, TRUE);
                break;
        }
    }

    protected function setData($ch)
    {
        if ($this->data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
        }
    }

    protected function setHeaders($ch)
    {
        if ($this->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }
    }
}
