<?php
declare(strict_types=1);

namespace Harshad\SiteWidth\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class SiteWidth implements ArgumentInterface
{
    private const PATH_CONTAINER = 'harshad_sitewidth/container/';

    /** Full width layouts par container setting nahi lagse */
    private const NOT_FULL_WIDTH = 'body:not(.page-layout-cms-full-width)'
    . ':not(.page-layout-category-full-width)'
    . ':not(.page-layout-product-full-width)';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getCss(): string
    {
        if (!$this->scopeConfig->isSetFlag(self::PATH_CONTAINER . 'enabled', ScopeInterface::SCOPE_STORE)) {
            return '';
        }

        $width = (float) $this->scopeConfig->getValue(self::PATH_CONTAINER . 'max_width', ScopeInterface::SCOPE_STORE);
        $unit = (string) $this->scopeConfig->getValue(self::PATH_CONTAINER . 'unit', ScopeInterface::SCOPE_STORE);
        $padding = (int) $this->scopeConfig->getValue(self::PATH_CONTAINER . 'padding', ScopeInterface::SCOPE_STORE);

        if ($width <= 0) {
            return '';
        }
        if (!in_array($unit, ['px', '%'], true)) {
            $unit = 'px';
        }
        if ($unit === '%' && $width > 100) {
            $width = 100;
        }

        $max = rtrim(rtrim(number_format($width, 2, '.', ''), '0'), '.') . $unit;
        $pad = max(0, $padding) . 'px';

        // Outer wrappers: only width + center (no padding, to avoid double padding)
        $wrappers = [
            '.page-main',
            '.page-wrapper > .page-bottom',
            '.top-container',
        ];

        // Inner content blocks: width + center + side padding
        $contents = [
            '.container',
            '.page-main .columns',
            '.page-header .header.content',
            '.footer.content',
        ];

        $wrapperSelectors = [];
        foreach ($wrappers as $target) {
            $wrapperSelectors[] = self::NOT_FULL_WIDTH . ' ' . $target;
        }

        $contentSelectors = [];
        foreach ($contents as $target) {
            $contentSelectors[] = self::NOT_FULL_WIDTH . ' ' . $target;
        }

        $common = 'max-width:' . $max . ' !important;'
            . 'width:100% !important;'
            . 'margin-left:auto !important;'
            . 'margin-right:auto !important;'
            . 'box-sizing:border-box;';

        return implode(',', $wrapperSelectors) . '{' . $common . '}'
            . implode(',', $contentSelectors) . '{'
            . $common
            . 'padding-left:' . $pad . ' !important;'
            . 'padding-right:' . $pad . ' !important;}';
    }
}
