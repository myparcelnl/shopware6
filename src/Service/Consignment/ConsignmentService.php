<?php

namespace Kiener\KienerMyParcel\Service\Consignment;

use Exception;
use Kiener\KienerMyParcel\Helper\AddressHelper;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use RuntimeException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConsignmentService
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * TestService constructor.
     *
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(
        SystemConfigService $systemConfigService
    )
    {
        $this->apiKey = (string)$systemConfigService->get('KienerMyParcel.config.myParcelApiKey');
    }

    /**
     * @return array
     */
    public function getCarrierIds(): array
    {
        return [
            BpostConsignment::CARRIER_NAME => BpostConsignment::CARRIER_ID,
            DPDConsignment::CARRIER_NAME => DPDConsignment::CARRIER_ID,
            PostNLConsignment::CARRIER_NAME => PostNLConsignment::CARRIER_ID,
        ];
    }

    /**
     * @return array
     */
    public function getPackageTypes(): array
    {
        return [
            AbstractConsignment::PACKAGE_TYPE_PACKAGE => AbstractConsignment::PACKAGE_TYPE_PACKAGE_NAME,
            AbstractConsignment::PACKAGE_TYPE_MAILBOX => AbstractConsignment::PACKAGE_TYPE_MAILBOX_NAME,
            AbstractConsignment::PACKAGE_TYPE_LETTER => AbstractConsignment::PACKAGE_TYPE_LETTER_NAME,
            AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP => AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
        ];
    }

    /**
     * @param OrderEntity $orderEntity
     * @param int         $carrierId
     * @param bool|null   $ageCheck
     * @param bool|null   $largeFormat
     * @param bool|null   $returnIfNotHome
     * @param bool|null   $requiresSignature
     * @param bool|null   $onlyRecipient
     * @param int|null    $packageType
     * @param array|null  $labelPositions
     *
     * @return string|null
     * @throws MissingFieldException
     */
    public function createConsignment( //NOSONAR
        OrderEntity $orderEntity,
        int $carrierId,
        ?bool $ageCheck = false,
        ?bool $largeFormat = false,
        ?bool $returnIfNotHome = false,
        ?bool $requiresSignature = false,
        ?bool $onlyRecipient = false,
        ?int $packageType = null,
        ?array $labelPositions = []
    ): ?string
    {
        if ($orderEntity->getOrderCustomer() === null) {
            throw new RuntimeException('Could not get a customer');
        }

        if (
            $orderEntity->getDeliveries() === null ||
            $orderEntity->getDeliveries()->first() === null ||
            $orderEntity->getDeliveries()->first()->getShippingOrderAddress() === null
        ) {
            throw new RuntimeException('Could not get a shipping address');
        }

        $shippingAddress = $orderEntity->getDeliveries()->first()->getShippingOrderAddress();

        if ($shippingAddress === null ||
            $shippingAddress->getCountry() === null
        ) {
            throw new RuntimeException('Shipping address is not properly formatted');
        }

        $parsedAddress = AddressHelper::parseAddress($shippingAddress);

        $consignment = (ConsignmentFactory::createByCarrierId($carrierId))
            ->setApiKey($this->apiKey)
            ->setReferenceId($orderEntity->getId())
            ->setCountry($shippingAddress->getCountry()->getIso())
            ->setPerson(
                sprintf('%s %s', $shippingAddress->getFirstName(), $shippingAddress->getLastName())
            )
            ->setFullStreet(
                sprintf('%s %s %s', $parsedAddress['street'], $parsedAddress['houseNumber'], $parsedAddress['houseNumberAddition'])
            )
            ->setPostalCode($shippingAddress->getZipcode())
            ->setCity($shippingAddress->getCity())
            ->setEmail($orderEntity->getOrderCustomer()->getEmail());

        if($ageCheck !== null)
        {
            $consignment->setAgeCheck($ageCheck);
        }

        if($largeFormat !== null)
        {
            $consignment->setLargeFormat($largeFormat);
        }

        if($requiresSignature !== null)
        {
            $consignment->setSignature($requiresSignature);
        }

        if($onlyRecipient !== null)
        {
            $consignment->setOnlyRecipient($onlyRecipient);
        }

        if (
            $packageType !== null
            && is_int($packageType)
            && in_array($packageType, AbstractConsignment::PACKAGE_TYPES_IDS, true)
        ) {
            $consignment->setPackageType($packageType);
        }

        try {
            if($returnIfNotHome !== null)
            {
                $consignment->setReturn($returnIfNotHome);
            }

            $consignments = (new MyParcelCollection())
                ->addConsignment($consignment);

            if(is_array($labelPositions) && !empty($labelPositions))
            {
                if(count($labelPositions) === 1)
                {
                    $consignments->setPdfOfLabels($labelPositions[0]);
                }
                else
                {
                    $consignments->setPdfOfLabels($labelPositions);
                }
            }
            else{
                $consignments->setPdfOfLabels(false);
            }

            // ToDo: Store label in Entity
            // $consignments->getLinkOfLabels();

            /** @var AbstractConsignment $firstConsignment */
            $firstConsignment = $consignments->first();

            return $firstConsignment->getConsignmentId();
        } catch (MissingFieldException $e) {
            var_dump($e->getMessage());
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }

        return null;
    }

    public function createConsignments(array $orders)
    {
        $consignments = (new MyParcelCollection())
            ->setUserAgent('Shopware', '6'); // ToDo: Hier naar kijken

        /** @var OrderEntity $order */
        foreach ($orders as $order)
        {
            if ($order instanceof OrderEntity)
            {
                $consignment = $this->createConsignment();

                $consignments->addConsignment($consignment);
            }
        }
    }

    /**
     * @param string $referenceId
     *
     * @return array
     * @throws MissingFieldException
     */
    public function findByReferenceId(string $referenceId): array
    {
        $consignments = MyParcelCollection::findByReferenceId($referenceId, $this->apiKey);

        return $consignments->toArray();
    }
}