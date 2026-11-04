<?php
declare(strict_types=1);

namespace Harshad\ThemeColor\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class ThemeColor implements ArgumentInterface
{
    private const XML_PATH = 'harshad_themecolor/colors/';

    /** tailwind prefix => css properties */
    private const PROPS = [
        'bg'      => ['background-color'],
        'text'    => ['color'],
        'border'  => ['border-color'],
        'fill'    => ['fill'],
        'stroke'  => ['stroke'],
        'outline' => ['outline-color'],
        'accent'  => ['accent-color'],
        'ring'    => ['--tw-ring-color'],
    ];

    /** tailwind variant => [class prefix, pseudo] */
    private const VARIANTS = [
        'base'   => ['', ''],
        'hover'  => ['hover\\:', ':hover'],
        'focus'  => ['focus\\:', ':focus'],
        'active' => ['active\\:', ':active'],
    ];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getCss(): string
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH . 'enabled', ScopeInterface::SCOPE_STORE)) {
            return '';
        }

        $colors = [];
        foreach (['primary', 'secondary'] as $name) {
            $hex = (string) $this->scopeConfig->getValue(self::XML_PATH . $name, ScopeInterface::SCOPE_STORE);
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) {
                continue;
            }
            $colors[$name] = [
                ''         => $hex,
                '-lighter' => $this->mix($hex, '#ffffff', 0.12),
                '-darker'  => $this->mix($hex, '#000000', 0.15),
            ];
        }

        if (!$colors) {
            return '';
        }

        // 1) CSS variables (Tailwind 4 / Hyva new versions use these directly)
        $css = ':root{';
        foreach ($colors as $name => $shades) {
            foreach ($shades as $suffix => $hex) {
                $css .= '--color-' . $name . $suffix . ':' . $hex . ';';
            }
        }
        $css .= '}';

        // 2) Utility class overrides
        foreach ($colors as $name => $shades) {
            foreach ($shades as $suffix => $hex) {
                foreach (self::PROPS as $prefix => $properties) {
                    foreach (self::VARIANTS as [$variantPrefix, $pseudo]) {
                        $selector = '.' . $variantPrefix . $prefix . '-' . $name . $suffix . $pseudo;
                        $rule = '';
                        foreach ($properties as $property) {
                            $rule .= $property . ':' . $hex . ' !important;';
                        }
                        $css .= $selector . '{' . $rule . '}';
                    }
                }
            }
        }

        // 3) Hyva button components
        foreach (['primary', 'secondary'] as $name) {
            if (!isset($colors[$name])) {
                continue;
            }
            $css .= '.btn-' . $name . '{background-color:' . $colors[$name][''] . ' !important;'
                . 'border-color:' . $colors[$name][''] . ' !important;}'
                . '.btn-' . $name . ':hover,.btn-' . $name . ':focus{'
                . 'background-color:' . $colors[$name]['-darker'] . ' !important;'
                . 'border-color:' . $colors[$name]['-darker'] . ' !important;}';
        }

        return $css;
    }

    private function mix(string $hex, string $with, float $weight): string
    {
        [$r1, $g1, $b1] = sscanf($hex, '#%02x%02x%02x');
        [$r2, $g2, $b2] = sscanf($with, '#%02x%02x%02x');

        return sprintf(
            '#%02x%02x%02x',
            (int) round($r1 + ($r2 - $r1) * $weight),
            (int) round($g1 + ($g2 - $g1) * $weight),
            (int) round($b1 + ($b2 - $b1) * $weight)
        );
    }
}
