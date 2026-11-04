<?php
declare(strict_types=1);

namespace Harshad\Buttons\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class Buttons implements ArgumentInterface
{
    private const PATH = 'harshad_buttons/';
    private const TYPES = ['primary', 'secondary'];
    private const STATES = ['normal', 'hover', 'focus', 'active', 'disabled'];

    /** field suffix => css property */
    private const PROPS = [
        'bg'     => 'background-color',
        'text'   => 'color',
        'border' => 'border-color',
    ];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getCss(): string
    {
        if (!$this->scopeConfig->isSetFlag(self::PATH . 'general/enabled', ScopeInterface::SCOPE_STORE)) {
            return '';
        }

        $css = '';
        foreach (self::TYPES as $type) {
            foreach (self::STATES as $state) {
                $css .= $this->stateCss($type, $state);
            }
        }

        return $css . $this->typographyCss();
    }

    private function stateCss(string $type, string $state): string
    {
        $rule = '';
        foreach (self::PROPS as $suffix => $property) {
            $color = $this->color($type . '/' . $state . '_' . $suffix);
            if ($color === '') {
                continue;
            }
            $rule .= $property . ':' . $color . ' !important;';
            if ($suffix === 'border') {
                $rule .= 'border-style:solid !important;';
            }
        }

        if ($rule === '') {
            return '';
        }

        return implode(',', $this->selectors($type, $state)) . '{' . $rule . '}';
    }

    /**
     * @return string[]
     */
    private function selectors(string $type, string $state): array
    {
        $base = 'body .btn-' . $type;

        return match ($state) {
            'hover'    => [$base . ':hover:not(:disabled):not(.disabled)'],
            'focus'    => [$base . ':focus', $base . ':focus-visible'],
            'active'   => [$base . ':active:not(:disabled):not(.disabled)'],
            'disabled' => [
                $base . ':disabled',
                $base . '.disabled',
                $base . '[disabled]',
                $base . '[aria-disabled="true"]',
            ],
            default    => [$base],
        };
    }

    private function typographyCss(): string
    {
        $target = 'body .btn-primary,body .btn-secondary';
        $desktop = $this->size('typography/desktop_size');
        $mobile = $this->size('typography/mobile_size');
        $weight = (string) $this->value('typography/font_weight');
        $weight = in_array($weight, ['100', '200', '300', '400', '500', '600', '700', '800', '900'], true)
            ? $weight
            : '';

        $css = '';
        if ($mobile) {
            $css .= '@media (max-width:767px){' . $target . '{font-size:' . $mobile . 'px !important;}}';
        }
        if ($desktop) {
            $css .= '@media (min-width:768px){' . $target . '{font-size:' . $desktop . 'px !important;}}';
        }
        if ($weight !== '') {
            $css .= $target . '{font-weight:' . $weight . ' !important;}';
        }

        return $css;
    }

    private function size(string $path): float
    {
        $size = (float) $this->value($path);
        return ($size >= 8 && $size <= 40) ? $size : 0.0;
    }

    private function color(string $path): string
    {
        $hex = (string) $this->value($path);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : '';
    }

    private function value(string $path): mixed
    {
        return $this->scopeConfig->getValue(self::PATH . $path, ScopeInterface::SCOPE_STORE);
    }
}
