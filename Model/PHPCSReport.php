<?php

declare(strict_types=1);

namespace Freento\AuditCodeSniffer\Model;

use Freento\AuditCodeSniffer\Api\CodeSnifferInterface;
use Freento\AuditCodeSniffer\Api\Data\PHPCSReportInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;

class PHPCSReport extends DataObject implements PHPCSReportInterface
{
    private const CODE = 'code';
    private const REPORT = 'report';
    public const CODE_DIR = 'app/code';

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var File
     */
    private File $driver;

    /**
     * @param Json $json
     * @param DirectoryList $directoryList
     * @param File $driver
     */
    public function __construct(Json $json, DirectoryList $directoryList, File $driver)
    {
        parent::__construct();
        $this->json = $json;
        $this->directoryList = $directoryList;
        $this->driver = $driver;
    }

    /**
     * @inheritdoc
     */
    public function getReportGroupedByModules(): array
    {
        $report = $this->json->unserialize($this->getReport());
        /** @var array $files */
        $files = $report['files'] ?? [];

        $modules = [];
        foreach ($files as $filepath => $fileReport) {
            $pattern = sprintf(
                '/%s(%s\/\w*\/\w*)\/(.*)/',
                str_replace('/', '\/', $this->directoryList->getRoot() . DIRECTORY_SEPARATOR),
                str_replace('/', '\/', self::CODE_DIR)
            );

            preg_match($pattern, $filepath, $matches);

            $modulePath = $matches[1];

            $errors = ($modules[$modulePath]['errors'] ?? 0) + $fileReport['errors'];
            $modules[$modulePath]['errors'] = $errors;

            $warnings = ($modules[$modulePath]['warnings'] ?? 0) + $fileReport['warnings'];
            $modules[$modulePath]['warnings'] = $warnings;

            $modules[$modulePath]['files'][$matches[2]] = $fileReport;
        }

        foreach (array_keys($modules) as $modulePath) {
            $moduleXml = $this->directoryList->getRoot()
                . DIRECTORY_SEPARATOR . $modulePath
                . DIRECTORY_SEPARATOR . 'etc'
                . DIRECTORY_SEPARATOR . 'module.xml';
            if ($this->driver->isExists($moduleXml)) {
                try {
                    $xmlContent = simplexml_load_string($this->driver->fileGetContents($moduleXml));
                    $modules[$modulePath]['name'] = (string)($xmlContent->module['name']
                        ?? __('undefined (name attribute not found in module.xml)'));
                } catch (\Exception $e) {
                    $modules[$modulePath]['name'] = __('undefined (etc/module.xml is incorrect)');
                }
            } else {
                $modules[$modulePath]['name'] = __('undefined (etc/module.xml not found)');
            }
        }

        ksort($modules);
        return [
            'code' => $this->getCode(),
            'modules' => $modules
        ];
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function getReport(): string
    {
        return $this->getData(self::REPORT);
    }

    /**
     * @inheritDoc
     */
    public function setCode(int $code): void
    {
        $this->setData(self::CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function setReport(string $report): void
    {
        $this->setData(self::REPORT, $report);
    }
}
