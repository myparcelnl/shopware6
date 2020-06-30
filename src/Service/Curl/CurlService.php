<?php
/**
 * User: wybe
 * Date: 12-6-19
 * Time: 19:21
 */

namespace Kiener\KienerMyParcel\Service\Curl;

use Kiener\KienerMyParcel\Exception\Curl\CurlExecException;
use Psr\Log\LoggerInterface;

/**
 * Class CurlService
 * @package App\Service
 */
class CurlService
{
    public const REQUEST_METHOD_DELETE = 'DELETE';
    public const REQUEST_METHOD_PATCH = 'PATCH';
    public const REQUEST_METHOD_PUT = 'PUT';

    private const REQUEST_METHODS = [
        self::REQUEST_METHOD_DELETE,
        self::REQUEST_METHOD_PATCH,
        self::REQUEST_METHOD_PUT
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CurlService constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string      $url
     * @param array|null  $header
     * @param string|null $postData
     * @param bool|null   $failOnError
     * @param string|null $customRequest
     *
     * @return mixed
     *
     * @throws CurlExecException
     */
    public function makeRequest(
        string $url,
        ?array $header = null,
        ?string $postData = null,
        ?bool $failOnError = true,
        ?string $customRequest = null
    )
    {
        $this->logger->debug('Making request', [
            'url' => $url
        ]);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => null,
            CURLOPT_POST => !empty($postData),
            CURLOPT_TIMEOUT => 25,
            CURLOPT_FAILONERROR => $failOnError
        ];

        if ($header) {
            $options[CURLOPT_HTTPHEADER] = $header;
        }

        if (!empty($postData)) {
            $options[CURLOPT_POSTFIELDS] = $postData;
        }

        if ($customRequest && in_array($customRequest, self::REQUEST_METHODS, true)) {
            $options[CURLOPT_CUSTOMREQUEST] = $customRequest;
        }

        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $data = curl_exec($ch);

        if (curl_error($ch)) {
            $this->logger->error('Curl error', [
                'error' => curl_error($ch)
            ]);
            $this->logger->error('Curl response', [
                'response' => $data
            ]);

            throw new CurlExecException('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return $data;
    }

}
