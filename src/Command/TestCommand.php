<?php


namespace Kiener\KienerMyParcel\Command;

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
     * TestCommand constructor.
     *
     * @param ConsignmentService $testService
     */
    public function __construct(ConsignmentService $testService)
    {
        Command::__construct();
        $this->testService = $testService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->testService->createConsignment();
    }

}