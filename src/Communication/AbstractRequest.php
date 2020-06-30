<?php

namespace Kiener\KienerMyParcel\Communication;

use Kiener\KienerMyParcel\Communication\Login\LoginRequest;
use Kiener\KienerMyParcel\Communication\Login\LoginResponse;
use Kiener\KienerMyParcel\Exception\Communication\ResponseNotSuccessfulException;
use Kiener\KienerMyParcel\Exception\Curl\CurlExecException;
use Kiener\KienerMyParcel\Consignment\Service\Curl\CurlService;
use Psr\Log\LoggerInterface;
use stdClass;

abstract class AbstractRequest
{
    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var CurlService $curlService
     */
    protected $curlService;

    /**
     * @var bool
     */
    private $authenticate;

    /**
     * @var string|null
     */
    protected $customRequest;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var array
     */
    private $urlParameters = [];

    /**
     * @var array
     */
    private $postParameters = [];

    /**
     * @var string $curlResponse
     */
    private $curlResponse;

    /**
     * AbstractRequest constructor.
     *
     * @param LoggerInterface $logger
     * @param CurlService     $curlService
     * @param bool            $authenticate
     * @param string|null     $customRequest
     */
    public function __construct(
        LoggerInterface $logger,
        CurlService $curlService,
        bool $authenticate = false,
        ?string $customRequest = null
    )
    {
        $this->logger = $logger;
        $this->curlService = $curlService;
        $this->authenticate = $authenticate;
        $this->customRequest = $customRequest;
    }

    /**
     * @return string
     */
    abstract protected function getUrlSection(): string;

    /**
     * @return string
     */
    abstract protected function getResponseClassFqdn(): string;

    /**
     * @param string $header
     *
     * @return $this
     */
    protected function addHeader(string $header): self
    {
        $this->headers[] = $header;

        return $this;
    }

    /**
     * @return string
     */
    protected function getAuthToken(): string
    {
        $request = new LoginRequest(
            $this->logger,
            $this->curlService
        );

        /** @var LoginResponse $response */
        try {
            $response = $request->getResponse();
            return $response->getAccessToken();
        } catch (ResponseNotSuccessfulException $e) {
            $this->logger->warning('Did not get a valid response');
        } catch (CurlExecException $e) {
            $this->logger->warning('Could not make Auth Token request');
        }

        return '';
    }

    /**
     * @return array
     */
    protected function getHeaders(): array
    {
        if ($this->authenticate) {
            $this->headers[] = sprintf('Authorization: Bearer %s', $this->getAuthToken());
        }

        return $this->headers;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    protected function addUrlParameter(string $name, string $value): self
    {
        $this->urlParameters[$name] = $value;

        return $this;
    }

    /**
     * @return string|null
     */
    protected function getUrlParameterString(): ?string
    {
        if (!$this->urlParameters) {
            return null;
        }

        $parameterString = null;

        foreach ($this->urlParameters as $parameter => $value) {
            $parameterString .= sprintf('%s%s=%s', $parameterString === null ? '?' : '&', (string)$parameter, (string)$value);
        }

        return $parameterString;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return $this
     */
    protected function addPostParameter(string $name, $value): self
    {
        $this->postParameters[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    protected function getPostParameters(): array
    {
        return $this->postParameters;
    }

    /**
     * @return string
     */
    private function getUrl(): string
    {
        return sprintf('%s/%s', $_ENV['AKENEO_API_ENDPOINT_BASE'], $this->getUrlSection());
    }

    /**
     * @return mixed
     * @throws CurlExecException
     */
    private function sendCurlRequest()
    {
        $res = $this->curlService->makeRequest(
            $this->getUrl(),
            $this->getHeaders(),
            !empty($this->getPostParameters()) ? json_encode($this->getPostParameters()) : null,
            false,
            $this->customRequest
        );

        $this->curlResponse = $res;
        return $res;
    }

    /**
     * @return string
     * @throws CurlExecException
     */
    private function getCurlResponse(): string
    {
        if ($this->curlResponse === null) {
            $this->sendCurlRequest();
        }
        return $this->curlResponse;
    }

    /**
     * @param string $json
     * @param bool   $assoc
     * @param int    $depth
     *
     * @return stdClass|null
     */
    private function decodeJson(string $json, bool $assoc = false, int $depth = 512): ?stdClass
    {
        $resource = json_decode($json, $assoc, $depth);

        if ($resource === null || json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Resources JSON could not be decoded', [
                'json' => $json
            ]);

            return null;

        }

        return $resource;
    }

    /**
     * @return AbstractResponse
     *
     * @throws CurlExecException
     * @throws ResponseNotSuccessfulException
     */
    private function buildResponseObject(): AbstractResponse
    {
        $class = $this->getResponseClassFqdn();

        $decodedJson = null;

        if (!empty($this->getcurlResponse())) {
            $decodedJson = $this->decodeJson($this->getCurlResponse());
        }

        $resource = $decodedJson ?: new stdClass();

        /** @var AbstractResponse $response */
        $response = new $class($resource);
        $this->logger->debug('Response class generated', get_object_vars($response));

        if (!$response->isSuccessful()) {
            throw new ResponseNotSuccessfulException(sprintf('Request was not successful: %s', $response->getErrorMessage()));
        }

        return $response;
    }

    /**
     * @return AbstractResponse
     * @throws CurlExecException
     * @throws ResponseNotSuccessfulException
     */
    public function getResponse(): AbstractResponse
    {
        return $this->buildResponseObject();
    }

}
