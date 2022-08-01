<?php declare(strict_types=1);

namespace MyPa\Shopware\Compatibility;

class VersionCompare
{
    /**
     * @var string
     */
    private $baseVersion;

    /**
     * @param string $baseVersion
     */
    public function __construct(string $baseVersion)
    {
        $this->baseVersion = $baseVersion;
    }

    /**
     * @param string $testVersion
     * @return bool
     */
    public function equals(string $testVersion): bool
    {
        return version_compare($this->baseVersion, $testVersion, '==');
    }

    /**
     * @param string $testVersion
     * @return bool
     */
    public function notEquals(string $testVersion): bool
    {
        return version_compare($this->baseVersion, $testVersion, '!=');
    }

    /**
     * @param string $testVersion
     * @return bool
     */
    public function greaterThan(string $testVersion): bool
    {
        return version_compare($this->baseVersion, $testVersion, '>');
    }

    /**
     * @param string $testVersion
     * @return bool
     */
    public function greaterThanOrEquals(string $testVersion): bool
    {
        return version_compare($this->baseVersion, $testVersion, '>=');
    }

    /**
     * @param string $testVersion
     * @return bool
     */
    public function lessThan(string $testVersion): bool
    {
        return version_compare($this->baseVersion, $testVersion, '<');
    }

    /**
     * @param string $testVersion
     * @return bool
     */
    public function lessThanOrEquals(string $testVersion): bool
    {
        return version_compare($this->baseVersion, $testVersion, '<=');
    }
}
