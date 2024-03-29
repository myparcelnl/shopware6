<?php declare(strict_types=1);

namespace MyPa\Shopware\Exception\CustomField;

class CustomFieldSetNotExistsException extends CustomFieldException
{
    public function __construct(array $parameters, ?\Throwable $previous = null)
    {
        $message = "Custom field set does not exist";
        parent::__construct($message, $parameters, $previous);
    }

    public function getErrorCode(): string
    {
        return 'MYPARCEL__CUSTOM_FIELD_SET_NOT_EXISTS';
    }
}
