<?php

namespace MyPa\Shopware\Storefront\Controller;

use MyParcelNL\Sdk\src\Services\CheckApiKeyService;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CredentialsTestController
{
    /**
     * @Route(path="/api/_action/myparcel-api-test/verify")
     */
    public function check(RequestDataBag $dataBag): JsonResponse
    {
        $apiKey = $dataBag->get('MyPaShopware.config.myParcelApiKey');

        if(!$apiKey){
            return new JsonResponse(['success' => false]);
        }

        $checkApikeyService = new CheckApiKeyService();

        $checkApikeyService->setApiKey($apiKey);

        $success = $checkApikeyService->apiKeyIsCorrect();

        return new JsonResponse(['success' => $success]);
    }
}
