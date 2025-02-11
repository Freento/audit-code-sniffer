<?php

declare(strict_types=1);

namespace Freento\AuditCodeSniffer\Model;

use Freento\AuditCodeSniffer\Api\CodeSnifferInterface;
use Freento\AuditCodeSniffer\Api\Data\PHPCSReportInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Shell\CommandRendererInterface;
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
     * @var CommandRendererInterface
     */
    private CommandRendererInterface $commandRenderer;

    /**
     * @param File $file
     * @param DirectoryList $directoryList
     * @param PHPCSReportFactory $phpCsReportFactory
     * @param CommandRendererInterface $commandRenderer
     */
    public function __construct(
        File $file,
        DirectoryList $directoryList,
        PHPCSReportFactory $phpCsReportFactory,
        CommandRendererInterface $commandRenderer
    ) {
        $this->file = $file;
        $this->directoryList = $directoryList;
        $this->phpCsReportFactory = $phpCsReportFactory;
        $this->commandRenderer = $commandRenderer;
    }

    /**
     * @inheritdoc
     */
    public function run($dir): PHPCSReportInterface
    {
        $path = $this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $dir;
        if (!$this->file->isExists($path)) {
            throw new FileNotFoundException(__('Specified path doesn\'t exist')->render());
        }

        $phpcsPath = $this->directoryList->getRoot() . '/vendor/bin/phpcs';
        if (!$this->file->isExists($phpcsPath) || !$this->file->isFile($phpcsPath)) {
            throw new FileNotFoundException(__('PHP CS executable not found')->render());
        }

        $command = $this->commandRenderer->render(
            $phpcsPath . ' %s %s %s',
            [$path, '--standard=Magento2', '--report=json']
        );

        /*
         * \Magento\Framework\Shell::execute treats non-zero exit codes as an execution error and throws an exception.
         * But phpcs returns 1 or 2 if execution was successful and code issues are found. So we should use exec()
        */
        if (!function_exists('exec')) {
            throw new LocalizedException(__('The exec function is disabled.'));
        }

        try {
            // phpcs:ignore Magento2.Security.InsecureFunction
            exec($command, $output, $code);
        } catch (\ValueError $e) {
            throw new LocalizedException(__('exec() command must not be empty'), $e);
        }

        if (empty($output)) {
            throw new LocalizedException(__('Code sniffer didn\'t return any data'));
        }

        if (!in_array($code, [0, 1, 2])) {
            throw new LocalizedException(__('Code sniffer execution error: %1', $output[0]));
        }

        $phpCsReport = $this->phpCsReportFactory->create();
        $phpCsReport->setCode($code);
        $phpCsReport->setReport($output[0]);

        return $phpCsReport;
    }
}
