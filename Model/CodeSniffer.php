<?php

declare(strict_types=1);

namespace Freento\AuditCodeSniffer\Model;

use Freento\AuditCodeSniffer\Api\CodeSnifferInterface;
use Freento\AuditCodeSniffer\Api\Data\PHPCSReportInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use PHP_CodeSniffer\Runner;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class CodeSniffer implements CodeSnifferInterface
{
    /**
     * @var File
     */
    private File $file;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var PHPCSReportFactory
     */
    private PHPCSReportFactory $phpCsReportFactory;

    /**
     * @param File $file
     * @param DirectoryList $directoryList
     * @param PHPCSReportFactory $phpCsReportFactory
     */
    public function __construct(File $file, DirectoryList $directoryList, PHPCSReportFactory $phpCsReportFactory)
    {
        $this->file = $file;
        $this->directoryList = $directoryList;
        $this->phpCsReportFactory = $phpCsReportFactory;
    }

    /**
     * @inheritdoc
     */
    public function run($dir): PHPCSReportInterface
    {
        $autoload = $this->directoryList->getRoot()
            . DIRECTORY_SEPARATOR . 'vendor'
            . DIRECTORY_SEPARATOR . 'squizlabs'
            . DIRECTORY_SEPARATOR . 'php_codesniffer'
            . DIRECTORY_SEPARATOR . 'autoload.php';

        if ($this->file->isExists($autoload)) {
            // phpcs:ignore Magento2.Security.IncludeFile
            require_once $autoload;
        } else {
            throw new FileNotFoundException(__('Code sniffer autoload not found')->render());
        }

        $basename = __FILE__;
        $path = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $dir;
        if (!$this->file->isExists($path)) {
            throw new FileNotFoundException(__('Specified path doesn\'t exist')->render());
        }

        // phpcs:ignore Magento2.Security.Superglobal.SuperglobalUsageWarning
        $_SERVER['argv'] = [
            $basename,
            '--standard=Magento2',
            '--report=json',
            $path
        ];

        $runner = new Runner();

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        ob_start();
        $code = $runner->runPHPCS();
        $response = ob_get_clean();
        $report = $response;

        $phpCsReport = $this->phpCsReportFactory->create();
        $phpCsReport->setCode($code);
        $phpCsReport->setReport($report);

        return $phpCsReport;
    }
}
