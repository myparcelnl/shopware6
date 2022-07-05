<?php

namespace MyPa\Shopware\Facade;

use MyPa\Shopware\Struct\DropOffPointStruct;
use MyParcelNL\Sdk\src\Exception\AccountNotActiveException;
use MyParcelNL\Sdk\src\Exception\ApiException;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use MyParcelNL\Sdk\src\Model\Account\CarrierOptions;
use MyParcelNL\Sdk\src\Services\Web\AccountWebService;
use MyParcelNL\Sdk\src\Services\Web\CarrierConfigurationWebService;
use MyParcelNL\Sdk\src\Services\Web\CarrierOptionsWebService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MyParcelFacade
{
    private AccountWebService $accountWebService;
    private CarrierOptionsWebService $carrierOptionsService;
    private CarrierConfigurationWebService $carrierConfigurationWebService;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->accountWebService = new AccountWebService();
        $this->carrierOptionsService = new CarrierOptionsWebService();
        $this->carrierConfigurationWebService = new CarrierConfigurationWebService();
        $this->logger = $logger;
    }

    public function getDropOffLocation(string $apiKey): JsonResponse
    {
        $this->accountWebService->setApiKey($apiKey);
        $this->carrierOptionsService->setApiKey($apiKey);
        $this->carrierConfigurationWebService->setApiKey($apiKey);

        try {
            $account = $this->accountWebService->getAccount();
            $shop = $account->getShops()->first();
            $carrierOptions = $this->carrierOptionsService->getCarrierOptions($shop->getId());
            /** @var CarrierOptions $myParcelCarrier */
            $myParcelCarrier = $carrierOptions->filter(function (CarrierOptions $options) {
                return $options->getCarrier()->getName() == 'instabox';
            })->first();
            $dropOffPoint = $this->carrierConfigurationWebService->getCarrierConfiguration(
                $shop->getId(),
                $myParcelCarrier->getCarrier()->getId(),
                true)->getDefaultDropOffPoint();
            $dropOffPointStruct = new DropOffPointStruct();
            $dropOffPointStruct->setWithDropOffPoint($dropOffPoint);
            return new JsonResponse($dropOffPointStruct->jsonSerialize());
        } catch (AccountNotActiveException|MissingFieldException|ApiException $e) {
            $this->logger->error('Error retrieving drop off location', ['Error' => $e]);
            return new JsonResponse(['errorMessage' => 'Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
