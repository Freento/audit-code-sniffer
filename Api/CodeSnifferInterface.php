<?php

declare(strict_types=1);

namespace Freento\AuditCodeSniffer\Api;

use Freento\AuditCodeSniffer\Api\Data\PHPCSReportInterface;
use Magento\Framework\Exception\FileSystemException;

interface CodeSnifferInterface
{
    /**
     * Run phpcs
     *
     * @param string $dir
     * @return PHPCSReportInterface
     * @throws FileSystemException
     */
    public function run(string $dir): PHPCSReportInterface;
}
