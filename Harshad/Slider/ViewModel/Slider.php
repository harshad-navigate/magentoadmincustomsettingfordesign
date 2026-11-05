<?php
declare(strict_types=1);

namespace Harshad\Slider\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Slider implements ArgumentInterface
{
    private const PATH = 'harshad_slider/';
    private const SLIDE_COUNT = 4;   // must match SLIDES in generate.php
    private const DEFAULT_BG = '#1f2937';
    private const H = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
    private const V = ['top' => 'flex-start', 'middle' => 'center', 'bottom' => 'flex-end'];

    /** @var array<int, array<string, mixed>>|null */
    private ?array $slides = null;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH . 'general/enabled', ScopeInterface::SCOPE_STORE)
            && count($this->getSlides()) > 0;
    }

    public function getPosition(): string
    {
        $v = (string) $this->value('general/position');
        return in_array($v, ['start', 'end', 'cms'], true) ? $v : 'start';
    }

    public function isFull(): bool
    {
        return (string) $this->value('general/width_mode') === 'full';
    }

    public function isLoop(): bool
    {
        return $this->flag('general/loop');
    }

    public function getAutoplayMs(): int
    {
        if (!$this->flag('general/autoplay')) {
            return 0;
        }
        $sec = (float) $this->value('general/delay');
        return (int) (max(1.5, min(60, $sec > 0 ? $sec : 5)) * 1000);
    }

    public function isPauseOnHover(): bool
    {
        return $this->flag('general/pause_hover');
    }

    public function getNavType(): string
    {
        $v = (string) $this->value('nav/type');
        return in_array($v, ['none', 'arrows', 'dots', 'both'], true) ? $v : 'both';
    }

    public function getRootClasses(): string
    {
        $arrow = (string) $this->value('nav/arrow_pos');
        $arrow = in_array($arrow, ['sides', 'bl', 'bc', 'br'], true) ? $arrow : 'sides';
        $dots = (string) $this->value('nav/dots_pos');
        $dots = in_array($dots, ['c', 'l', 'r'], true) ? $dots : 'c';

        $classes = 'hs hs-ap-' . $arrow . ' hs-dp-' . $dots;
        if ($this->isFull()) {
            $classes .= ' hs--full';
        }
        if (!$this->flag('nav/arrows_mobile')) {
            $classes .= ' hs-noam';
        }
        return $classes;
    }

    public function getStyleVars(): string
    {
        [$dw, $dh] = $this->ratio('general/ratio_desktop', 1920, 600);
        [$mw, $mh] = $this->ratio('general/ratio_mobile', 750, 900);

        $arrow = (string) $this->value('nav/arrow_pos');
        $bottomArrows = in_array($arrow, ['bl', 'bc', 'br'], true);
        $arrowBottom = ($bottomArrows && $this->getNavType() === 'both') ? 64 : 16;

        return '--hs-ar-d:' . $dw . '/' . $dh . ';--hs-ar-m:' . $mw . '/' . $mh . ';'
            . '--hs-ab:' . ($this->color('nav/arrow_bg') ?: '#ffffff') . ';'
            . '--hs-ac:' . ($this->color('nav/arrow_color') ?: '#111827') . ';'
            . '--hs-dc:' . ($this->color('nav/dot_color') ?: '#ffffff') . ';'
            . '--hs-da:' . ($this->color('nav/dot_active_color') ?: '#ffffff') . ';'
            . '--hs-abot:' . $arrowBottom . 'px;';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSlides(): array
    {
        if ($this->slides !== null) {
            return $this->slides;
        }

        $out = [];
        for ($i = 1; $i <= self::SLIDE_COUNT; $i++) {
            $p = 'slide_' . $i . '/';
            if (!$this->flag($p . 'enabled')) {
                continue;
            }

            $desktop = $this->image($p . 'image', $p . 'image_url');
            $bg = $this->color($p . 'bg_color');
            if ($bg === '' && $desktop === '') {
                $bg = self::DEFAULT_BG;
            }

            $h = (string) $this->value($p . 'h_pos');
            $v = (string) $this->value($p . 'v_pos');
            $ta = (string) $this->value($p . 'text_align');
            $ba = (string) $this->value($p . 'btn_align');
            $style = (string) $this->value($p . 'button_style');
            $width = (int) $this->value($p . 'content_width');
            $heading = trim((string) $this->value($p . 'heading'));
            $alt = trim((string) $this->value($p . 'alt'));

            $out[] = [
                'desktop'      => $desktop,
                'mobile'       => $this->image($p . 'mobile_image', $p . 'mobile_image_url'),
                'bg'           => $bg,
                'alt'          => $alt !== '' ? $alt : $heading,
                'heading'      => $heading,
                'text'         => trim((string) $this->value($p . 'text')),
                'button_text'  => trim((string) $this->value($p . 'button_text')),
                'button_url'   => $this->resolveUrl((string) $this->value($p . 'button_url'), true),
                'button_class' => $style === 'secondary' ? 'btn btn-secondary' : 'btn btn-primary',
                'link'         => $this->resolveUrl((string) $this->value($p . 'slide_link'), true),
                'new_tab'      => $this->flag($p . 'new_tab'),
                'color'        => $this->color($p . 'text_color') ?: '#ffffff',
                'overlay'      => max(0, min(80, (int) $this->value($p . 'overlay'))),
                'justify'      => self::H[$h] ?? 'flex-start',
                'align_items'  => self::V[$v] ?? 'center',
                'width'        => ($width >= 20 && $width <= 100) ? $width : 50,
                'text_align'   => isset(self::H[$ta]) ? $ta : 'left',
                'btn_align'    => isset(self::H[$ba]) ? $ba : 'left',
            ];
        }

        return $this->slides = $out;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function ratio(string $path, int $w, int $h): array
    {
        if (preg_match('/^(\d{2,5})\s*[x:\/]\s*(\d{2,5})$/i', trim((string) $this->value($path)), $m)) {
            return [(int) $m[1], (int) $m[2]];
        }
        return [$w, $h];
    }

    private function image(string $filePath, string $urlPath): string
    {
        $url = trim((string) $this->value($urlPath));
        if ($url !== '') {
            return $this->resolveUrl($url, false);
        }

        $file = trim((string) $this->value($filePath));
        if ($file === '' || str_contains($file, '..') || !preg_match('/^[\w\-\/\.]+$/', $file)) {
            return '';
        }

        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'harshad/slider/' . $file;
    }

    private function resolveUrl(string $url, bool $allowSpecial): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }
        if ($allowSpecial && preg_match('/^(mailto:|tel:|#)/i', $url)) {
            return $url;
        }
        if (preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url)) {
            return '';   // javascript:, data: and other schemes are blocked
        }

        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB) . ltrim($url, '/');
    }

    private function color(string $path): string
    {
        $hex = (string) $this->value($path);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? $hex : '';
    }

    private function flag(string $path): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH . $path, ScopeInterface::SCOPE_STORE);
    }

    private function value(string $path): mixed
    {
        return $this->scopeConfig->getValue(self::PATH . $path, ScopeInterface::SCOPE_STORE);
    }
}
