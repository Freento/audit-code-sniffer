<?php

declare(strict_types=1);

namespace Freento\AuditCodeSniffer\Block\Adminhtml;

use Freento\AuditCodeSniffer\Api\CodeSnifferInterface;
use Freento\AuditCodeSniffer\Model\PHPCSReport;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\FileSystemException;

class Report extends Template
{
    public const WARNING_CSS_CLASS = 'grid-severity-minor';
    public const ERROR_CSS_CLASS = 'grid-severity-major';

    /**
     * @var string
     */
    protected $_template = 'Freento_AuditCodeSniffer::report.phtml';

    /**
     * @var CodeSnifferInterface
     */
    private CodeSnifferInterface $sniffer;

    /**
     * @param Context $context
     * @param CodeSnifferInterface $sniffer
     */
    public function __construct(Context $context, CodeSnifferInterface $sniffer)
    {
        parent::__construct($context);
        $this->sniffer = $sniffer;
    }

    /**
     * Returns CSS class for severity level
     *
     * @param string $severity
     * @return string
     */
    public function getSeverityCssClass(string $severity): string
    {
        $class = '';
        switch ($severity) {
            case 'WARNING':
                $class = self::WARNING_CSS_CLASS;
                break;
            case 'ERROR':
                $class = self::ERROR_CSS_CLASS;
                break;
        }

        return $class;
    }

    /**
     * Render string with number of errors and warnings
     *
     * @param array $report
     * @return string
     */
    public function showErrorsWarnings(array $report): string
    {
        return __('errors: ') . ($report['errors'] ?? __('unknown')) . ', '
            . __('warnings: ') . ($report['warnings'] ?? __('unknown'));
    }

    /**
     * Returns array with reports grouped by modules and return code
     *
     * @return array
     * @throws FileSystemException
     */
    public function getReport(): array
    {
        return $this->sniffer->run(PHPCSReport::CODE_DIR)->getReportGroupedByModules();
    }
}
