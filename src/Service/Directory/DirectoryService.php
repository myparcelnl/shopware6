<?php
/**
 * User: wybe
 * Date: 26-12-19
 * Time: 13:14
 */

namespace Kiener\KienerMyParcel\Service\Directory;

use Kiener\KienerMyParcel\Exception\Directory\DirectoryCouldNotBeCreatedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class DirectoryService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * DirectoryService constructor.
     * @param LoggerInterface $logger
     * @param KernelInterface $kernel
     */
    public function __construct(LoggerInterface $logger, KernelInterface $kernel)
    {
        $this->logger = $logger;
        $this->kernel = $kernel;
    }

    /**
     * @param string $directory
     * @param bool $prependProjectRoot
     * @return string
     * @throws DirectoryCouldNotBeCreatedException
     */
    public function getDirectory(string $directory, $prependProjectRoot = false): string
    {
        if($prependProjectRoot)
        {
            $directory = sprintf('%s/%s', $this->kernel->getProjectDir(), $directory);
        }

        if (!file_exists($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {

            $errorMessage = sprintf('Directory "%s" could not be created', $directory);

            $this->logger->error($errorMessage);
            throw new DirectoryCouldNotBeCreatedException($errorMessage);
        }

        return $directory;
    }
}
