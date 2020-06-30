<?php

namespace Kiener\KienerMyParcel\Communication;

use stdClass;

class AbstractResponse
{
    /**
     * @var stdClass $response
     */
    private $response;

    /**
     * @var bool $isSuccessful
     */
    private $isSuccessful;

    /**
     * @var string $errorMessage
     */
    private $errorMessage;

    /**
     * AbstractResponse constructor.
     *
     * @param stdClass $response.
     */
    public function __construct(stdClass $response)
    {
        $this->response = $response;
        $this->checkForErrors();
    }

    private function processErrors(): void
    {
        if (isset($this->response->errors) && is_array($this->response->errors))
        {
            $error = $this->response->errors[0];

            if (property_exists($error, 'message'))
            {
                if (property_exists($error, 'property'))
                {
                    $this->errorMessage = sprintf('[%s] %s', $error->property, $error->message);
                    return;
                }

                $this->errorMessage = $error->message;
                return;
            }

        }

        if(isset($this->response->message))
        {
            $this->errorMessage = $this->response->message;
            return;
        }

        $this->errorMessage = 'Unknown error';
    }

    private function checkForErrors(): void
    {
        if (isset($this->response->errors) || (isset($this->response->code) && $this->response->code !== 200)) {
            $this->processErrors();
            $this->isSuccessful = false;
        }
        else{
            $this->isSuccessful = true;
        }
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    /**
     * @param bool $isSuccessful
     */
    public function setIsSuccessful(bool $isSuccessful): void
    {
        $this->isSuccessful = $isSuccessful;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}