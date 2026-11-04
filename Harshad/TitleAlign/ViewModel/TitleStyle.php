<?php
declare(strict_types=1);

namespace Harshad\TitleAlign\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class TitleStyle implements ArgumentInterface
{
    private const PAGE_MAP = [
        'catalog_category_view'      => 'list_page',
        'catalogsearch_result_index' => 'search_page',
        'catalog_product_view'       => 'pdp_page',
        'checkout_cart_index'        => 'cart_page',
    ];

    private const FLEX_MAP = [
        'left'   => 'flex-start',
        'center' => 'center',
        'right'  => 'flex-end',
    ];

    /**
     * Title element + every parent wrapper that can hold it (Hyva container, Luma wrapper)
     */
    private const TITLE = 'h1.page-title, .page-title-wrapper .page-title';
    private const PARENT = '.page-title-wrapper, :where(div):has(> h1.page-title)';

    public function __construct(
        private readonly RequestInterface $request,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getCss(): string
    {
        if (!$this->scopeConfig->isSetFlag(
            'harshad_titlealign/general/enabled',
            ScopeInterface::SCOPE_STORE
        )) {
            return '';
        }

        $action = $this->request->getFullActionName();
        if (!isset(self::PAGE_MAP[$action])) {
            return '';
        }

        $alignment = (string) $this->scopeConfig->getValue(
            'harshad_titlealign/' . self::PAGE_MAP[$action] . '/alignment',
            ScopeInterface::SCOPE_STORE
        );

        if ($alignment === 'hide') {
            return self::TITLE . ',' . self::PARENT . '{display:none !important;}';
        }

        if (!isset(self::FLEX_MAP[$alignment])) {
            return '';
        }

        $justify = self::FLEX_MAP[$alignment];

        return self::PARENT . '{'
            . 'justify-content:' . $justify . ' !important;'
            . 'align-items:' . $justify . ' !important;'
            . 'text-align:' . $alignment . ' !important;}'
            . self::TITLE . '{'
            . 'width:100% !important;'
            . 'flex:0 0 100% !important;'
            . 'text-align:' . $alignment . ' !important;}';
    }
}
