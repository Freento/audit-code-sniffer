<?php

declare(strict_types=1);

namespace Freento\AuditCodeSniffer\Api\Data;

use Magento\Framework\Exception\FileSystemException;

interface PHPCSReportInterface
{
    /**
     * Groups separate file reports by modules
     *
     * @return array
     * @throws FileSystemException
     */
    public function getReportGroupedByModules(): array;

    /**
     * Gets return code
     *
     * @return int
     */
    public function getCode(): int;

    /**
     * Gets code sniffer report
     *
     * @return string
     */
    public function getReport(): string;

    /**
     * Sets return code
     *
     * @param int $code
     * @return void
     */
    public function setCode(int $code): void;

    /**
     * Sets code sniffer report
     *
     * @param string $report
     * @return void
     */
    public function setReport(string $report): void;
}
