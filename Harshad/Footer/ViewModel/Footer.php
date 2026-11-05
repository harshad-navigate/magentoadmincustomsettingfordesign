<?php
declare(strict_types=1);

namespace Harshad\Footer\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Footer implements ArgumentInterface
{
    private const PATH = 'harshad_footer/';
    private const ROW_COUNT = 4;       // must match ROWS in generate.php
    private const SECTION_COUNT = 6;   // must match SECTIONS in generate.php
    private const ICON_COUNT = 8;
    private const TYPES = ['links', 'text', 'image', 'newsletter', 'payment', 'social'];
    private const FLEX = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];

    /** @var array<int, array<string, mixed>>|null */
    private ?array $rows = null;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly UrlInterface $urlBuilder,
        private readonly FormKey $formKey,
        private readonly Json $json
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH . 'general/enabled', ScopeInterface::SCOPE_STORE);
    }

    public function hasAccordion(): bool
    {
        foreach ($this->getRows() as $row) {
            if ($row['accordion'] > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRows(): array
    {
        if ($this->rows !== null) {
            return $this->rows;
        }

        $rows = [];
        for ($r = 1; $r <= self::ROW_COUNT; $r++) {
            $p = 'row_' . $r . '/';
            if (!$this->scopeConfig->isSetFlag(self::PATH . $p . 'enabled', ScopeInterface::SCOPE_STORE)) {
                continue;
            }

            $sections = $this->getSections($r);
            if (!$sections) {
                continue;
            }

            // 0 = no accordion, 1 = mobile only, 2 = mobile and tablet
            $mode = (int) $this->value($p . 'accordion');
            if ($mode < 0 || $mode > 2) {
                $mode = 0;
            }

            $rows[] = [
                'index'     => $r,
                'accordion' => $mode,
                'desktop'   => $this->columns($p . 'desktop_cols', 4),
                'tablet'    => $this->columns($p . 'tablet_cols', 2),
                'mobile'    => $this->columns($p . 'mobile_cols', 1),
                'sections'  => $sections,
            ];
        }

        return $this->rows = $rows;
    }

    /**
     * @return string[]
     */
    public function getPaymentIcons(): array
    {
        $icons = [];
        for ($i = 1; $i <= self::ICON_COUNT; $i++) {
            $url = $this->mediaUrl((string) $this->value('payment/icon_' . $i));
            if ($url !== '') {
                $icons[] = $url;
            }
        }
        return $icons;
    }

    public function getNewsletterAction(): string
    {
        return $this->urlBuilder->getUrl('newsletter/subscriber/new');
    }

    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    public function getNewsletterPlaceholder(): string
    {
        return (string) $this->value('newsletter/placeholder');
    }

    public function getNewsletterButton(): string
    {
        return (string) $this->value('newsletter/button_text');
    }

    public function getCopyright(): string
    {
        $text = (string) $this->value('bottom/copyright');
        $store = $this->storeManager->getStore()->getFrontendName();

        return str_replace(['{year}', '{store}'], [date('Y'), (string) $store], $text);
    }

    public function getCss(): string
    {
        $bg = $this->color('general/bg_color');
        $text = $this->color('general/text_color');
        $title = $this->color('general/title_color');
        $link = $this->color('general/link_color');
        $hover = $this->color('general/link_hover_color');
        $top = max(0, (int) $this->value('general/padding_top'));
        $bottom = max(0, (int) $this->value('general/padding_bottom'));
        $gap = max(0, (int) $this->value('general/column_gap'));
        $iconHeight = max(8, (int) $this->value('payment/icon_height'));
        $bottomAlign = (string) $this->value('bottom/align');
        $bottomAlign = in_array($bottomAlign, ['left', 'center', 'right'], true) ? $bottomAlign : 'center';

        $css = '.hf-footer{' . ($bg ? 'background:' . $bg . ';' : '') . ($text ? 'color:' . $text . ';' : '')
            . 'padding:' . $top . 'px 0 ' . $bottom . 'px;}';
        if ($link) {
            $css .= '.hf-footer a{color:' . $link . ';text-decoration:none;}';
        }
        if ($hover) {
            $css .= '.hf-footer a:hover,.hf-footer a:focus{color:' . $hover . ';text-decoration:underline;}';
        }
        if ($title) {
            $css .= '.hf-title{color:' . $title . ';}';
        }

        $css .= '.hf-row{display:flex;flex-wrap:wrap;margin:0 -' . $gap . 'px;}'
            . '.hf-col{box-sizing:border-box;width:100%;padding:0 ' . $gap . 'px;margin-bottom:24px;}'
            . '.hf-title{font-size:18px;font-weight:600;margin:0 0 16px;}'
            . '.hf-body img{max-width:100%;height:auto;display:block;}'
            . '.hf-body p{margin:0 0 8px;}'
            . '.hf-list,.hf-social{list-style:none;margin:0;padding:0;}'
            . '.hf-list li{margin:0 0 8px;}'
            . '.hf-social{display:flex;flex-wrap:wrap;gap:8px 16px;}'
            . '.hf-nl{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;}'
            . '.hf-nl input[type=email]{flex:1 1 160px;min-width:0;padding:8px 12px;border:1px solid #d1d5db;'
            . 'border-radius:4px;color:#111827;background:#fff;}'
            . '.hf-nl button{padding:8px 16px;cursor:pointer;}'
            . '.hf-pay{display:flex;flex-wrap:wrap;gap:8px;align-items:center;}'
            . '.hf-pay img{height:' . $iconHeight . 'px;width:auto;}';

        // Border between rows
        if ($this->scopeConfig->isSetFlag(self::PATH . 'general/row_border_enabled', ScopeInterface::SCOPE_STORE)) {
            $borderColor = $this->color('general/row_border_color') ?: '#374151';
            $borderHeight = min(20, max(1, (int) $this->value('general/row_border_height')));
            $css .= '.hf-rw + .hf-rw{border-top:' . $borderHeight . 'px solid ' . $borderColor . ';padding-top:24px;}';
        }

        // Columns per row + alignment per section
        foreach ($this->getRows() as $row) {
            $r = (int) $row['index'];
            $sel = '.hf-r' . $r . ' .hf-col';
            $mode = (int) $row['accordion'];

            $mobileCols = $mode >= 1 ? 1 : $row['mobile'];
            $tabletCols = $mode === 2 ? 1 : $row['tablet'];

            $css .= $sel . '{width:' . $this->pct($mobileCols) . '%;}';
            $css .= '@media (min-width:768px){' . $sel . '{width:' . $this->pct($tabletCols) . '%;}}';
            $css .= '@media (min-width:1024px){' . $sel . '{width:' . $this->pct($row['desktop']) . '%;}';

            foreach ($row['sections'] as $section) {
                $width = (float) $this->value('row_' . $r . '_section_' . $section['index'] . '/width');
                if ($width > 0 && $width <= 100) {
                    $css .= '.hf-r' . $r . ' .hf-col-' . $r . '-' . (int) $section['index']
                        . '{width:' . $this->pct($width, false) . '%;}';
                }
            }
            $css .= '}';

            // Content alignment of each section
            foreach ($row['sections'] as $section) {
                $a = (string) $section['align'];
                $flex = self::FLEX[$a];
                $col = '.hf-r' . $r . ' .hf-col-' . $r . '-' . (int) $section['index'];

                $css .= $col . '{text-align:' . $a . ';}'
                    . $col . ' .hf-social,' . $col . ' .hf-nl,' . $col . ' .hf-pay{justify-content:' . $flex . ';}'
                    . $col . ' .hf-body img{margin-left:' . ($a === 'left' ? '0' : '0')
                    . ';margin-right:' . ($a === 'right' ? '0' : 'auto') . ';}';
            }
        }

        // Accordion: mobile (below 768px) and tablet (768px - 1023px)
        $css .= '@media (max-width:767px){' . $this->accordionCss('.hf-acc') . '}';
        $css .= '@media (min-width:768px) and (max-width:1023px){' . $this->accordionCss('.hf-acc-t') . '}';

        // Bottom bar
        $bottomBg = $this->color('bottom/bg_color');
        $bottomText = $this->color('bottom/text_color');
        $css .= '.hf-bottom{padding:16px 0;text-align:' . $bottomAlign . ';font-size:14px;'
            . ($bottomBg ? 'background:' . $bottomBg . ';' : '')
            . ($bottomText ? 'color:' . $bottomText . ';' : '') . '}';

        return $css;
    }

    private function accordionCss(string $col): string
    {
        return $col . '{margin-bottom:0;border-bottom:1px solid rgba(128,128,128,.35);padding-top:4px;padding-bottom:4px;}'
            . $col . ' .hf-title{cursor:pointer;display:flex;justify-content:space-between;align-items:center;'
            . 'margin:0;padding:12px 0;text-align:left;}'
            . $col . ' .hf-title:after{content:"+";font-size:22px;line-height:1;}'
            . $col . '.is-open .hf-title:after{content:"\2212";}'
            . $col . ' .hf-body{display:none;padding-bottom:12px;}'
            . $col . '.is-open .hf-body{display:block;}';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSections(int $row): array
    {
        $result = [];

        for ($i = 1; $i <= self::SECTION_COUNT; $i++) {
            $p = 'row_' . $row . '_section_' . $i . '/';

            if (!$this->scopeConfig->isSetFlag(self::PATH . $p . 'enabled', ScopeInterface::SCOPE_STORE)) {
                continue;
            }

            $type = (string) $this->value($p . 'type');
            if (!in_array($type, self::TYPES, true)) {
                $type = 'links';
            }

            $align = (string) $this->value($p . 'align');

            $result[] = [
                'index'       => $i,
                'type'        => $type,
                'align'       => isset(self::FLEX[$align]) ? $align : 'left',
                'title'       => trim((string) $this->value($p . 'title')),
                'content'     => trim((string) $this->value($p . 'content')),
                'image'       => $this->mediaUrl((string) $this->value($p . 'image')),
                'image_link'  => $this->resolveUrl((string) $this->value($p . 'image_link'))['url'],
                'image_width' => max(0, (int) $this->value($p . 'image_width')),
                'links'       => $this->links((string) $this->value($p . 'links')),
            ];
        }

        return $result;
    }

    private function columns(string $path, int $default): int
    {
        $value = (int) $this->value($path);
        return ($value >= 1 && $value <= 6) ? $value : $default;
    }

    private function pct(float $value, bool $divide = true): string
    {
        $pct = $divide ? 100 / max(1, $value) : $value;
        return rtrim(rtrim(number_format($pct, 4, '.', ''), '0'), '.');
    }

    /**
     * @return array<int, array{label: string, url: string, external: bool}>
     */
    private function links(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        try {
            $rows = $this->json->unserialize($raw);
        } catch (\Throwable) {
            return [];
        }

        $links = [];
        foreach ((array) $rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            $resolved = $this->resolveUrl((string) ($row['url'] ?? ''));
            if ($label === '' || $resolved['url'] === '') {
                continue;
            }
            $links[] = ['label' => $label, 'url' => $resolved['url'], 'external' => $resolved['external']];
        }

        return $links;
    }

    /**
     * @return array{url: string, external: bool}
     */
    private function resolveUrl(string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            return ['url' => '', 'external' => false];
        }

        $base = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        if (preg_match('/^https?:\/\//i', $url)) {
            return ['url' => $url, 'external' => stripos($url, $base) !== 0];
        }
        if (preg_match('/^(mailto:|tel:|#)/i', $url)) {
            return ['url' => $url, 'external' => false];
        }
        if (preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url)) {
            // any other scheme (javascript:, data:...) is blocked
            return ['url' => '', 'external' => false];
        }

        return ['url' => $base . ltrim($url, '/'), 'external' => false];
    }

    private function mediaUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '' || str_contains($value, '..') || !preg_match('/^[\w\-\/\.]+$/', $value)) {
            return '';
        }

        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'harshad/footer/' . $value;
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
