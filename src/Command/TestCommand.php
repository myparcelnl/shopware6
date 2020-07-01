<?php


namespace Kiener\KienerMyParcel\Command;

use Kiener\KienerMyParcel\Service\ShippingMethod\ShippingMethodService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Kiener\KienerMyParcel\Service\Consignment\ConsignmentService;

class TestCommand extends Command
{
    protected static $defaultName = 'kiener-myparcel:test';

    /**
     * @var ConsignmentService
     */
    private $testService;

    /**
     * @var ShippingMethodService
     */
    private $shippingMethodService;

    /**
     * TestCommand constructor.
     *
     * @param ConsignmentService $testService
     */
    public function __construct(ConsignmentService $testService, ShippingMethodService $shippingMethodService)
    {
        Command::__construct();
        $this->testService = $testService;
        $this->shippingMethodService = $shippingMethodService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$this->testService->createConsignment();
    }

}