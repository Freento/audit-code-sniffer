<?php

declare(strict_types=1);

namespace Freento\AuditCodeSniffer\Api;

use Freento\AuditCodeSniffer\Api\Data\PHPCSReportInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;

interface CodeSnifferInterface
{
    /**
     * Run phpcs
     *
     * @param string $dir
     * @return PHPCSReportInterface
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function run(string $dir): PHPCSReportInterface;
}
