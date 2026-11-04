<?php
declare(strict_types=1);

namespace Harshad\Breadcrumbs\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class Breadcrumbs implements ArgumentInterface
{
    private const PATH = 'harshad_breadcrumbs/style/';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getCss(): string
    {
        if (!$this->scopeConfig->isSetFlag(self::PATH . 'enabled', ScopeInterface::SCOPE_STORE)) {
            return '';
        }

        $desktop = $this->size('desktop_size');
        $mobile = $this->size('mobile_size');
        $link = $this->color('link_color');
        $hover = $this->color('link_hover_color');
        $current = $this->color('current_color');
        $weight = (string) $this->value('font_weight');
        $weight = in_array($weight, ['100', '200', '300', '400', '500', '600', '700', '800', '900'], true)
            ? $weight
            : '';

        $all = '.breadcrumbs,.breadcrumbs ol,.breadcrumbs li,.breadcrumbs a,.breadcrumbs span,.breadcrumbs strong';
        $css = '';

        if ($mobile) {
            $css .= '@media (max-width:767px){' . $all . '{font-size:' . $mobile . 'px !important;}}';
        }
        if ($desktop) {
            $css .= '@media (min-width:768px){' . $all . '{font-size:' . $desktop . 'px !important;}}';
        }
        if ($weight !== '') {
            $css .= $all . '{font-weight:' . $weight . ' !important;}';
        }
        if ($link !== '') {
            $css .= '.breadcrumbs,.breadcrumbs li,.breadcrumbs a,.breadcrumbs span{color:' . $link . ' !important;}';
        }
        if ($hover !== '') {
            $css .= '.breadcrumbs a:hover,.breadcrumbs a:focus,'
                . '.breadcrumbs li:not(:last-child) a:hover,.breadcrumbs li:not(:last-child) a:focus'
                . '{color:' . $hover . ' !important;}';
        }
        if ($current !== '') {
            $css .= '.breadcrumbs li:last-child,.breadcrumbs li:last-child a,'
                . '.breadcrumbs li:last-child span,.breadcrumbs strong{color:' . $current . ' !important;}';
        }

        return $css;
    }

    private function size(string $key): float
    {
        $size = (float) $this->value($key);
        return ($size >= 8 && $size <= 40) ? $size : 0.0;
    }

    private function color(string $key): string
    {
        $hex = (string) $this->value($key);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : '';
    }

    private function value(string $key): mixed
    {
        return $this->scopeConfig->getValue(self::PATH . $key, ScopeInterface::SCOPE_STORE);
    }
}
