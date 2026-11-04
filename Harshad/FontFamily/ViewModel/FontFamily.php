<?php
declare(strict_types=1);

namespace Harshad\FontFamily\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class FontFamily implements ArgumentInterface
{
    private const PATH = 'harshad_fontfamily/';
    private const MAX_FONTS = 5;
    private const ALL_WEIGHTS = '100,200,300,400,500,600,700,800,900';
    private const GOOGLE_HOST = 'fonts.googleapis.com';
    private const BUNNY_HOST = 'fonts.bunny.net';
    private const FALLBACK = 'system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif';

    /** @var array{google: string[], others: string[]}|null */
    private ?array $resolved = null;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH . 'general/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * All stylesheet URLs to load.
     * Google fonts (list + Google CDN links) are combined into ONE request.
     *
     * @return string[]
     */
    public function getStylesheets(): array
    {
        $data = $this->resolve();
        $urls = [];

        if ($data['google']) {
            $urls[] = $this->buildUrl('https://' . self::GOOGLE_HOST . '/css', $data['google']);
        }

        return array_merge($urls, $data['others']);
    }

    /**
     * @return array<int, array{href: string, crossorigin: bool}>
     */
    public function getPreconnects(): array
    {
        $data = $this->resolve();
        $list = [];

        if ($data['google']) {
            $list['https://' . self::GOOGLE_HOST] = false;
            $list['https://fonts.gstatic.com'] = true;
        }

        foreach ($data['others'] as $url) {
            $parts = parse_url($url);
            if (empty($parts['host'])) {
                continue;
            }
            $origin = 'https://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
            if (!isset($list[$origin])) {
                $list[$origin] = true;
            }
        }

        $result = [];
        foreach ($list as $href => $crossorigin) {
            $result[] = ['href' => $href, 'crossorigin' => $crossorigin];
        }
        return $result;
    }

    public function getCss(): string
    {
        $body = $this->cleanFontName((string) $this->getValue('apply/body_font'));
        $heading = $this->cleanFontName((string) $this->getValue('apply/heading_font'));

        $css = '';
        if ($body !== '') {
            $css .= 'body,button,input,select,textarea{font-family:"' . $body . '",' . self::FALLBACK . ' !important;}';
        }
        if ($heading !== '') {
            $css .= 'h1,h2,h3,h4,h5,h6,.page-title,.title{font-family:"' . $heading . '",' . self::FALLBACK . ' !important;}';
        }

        return $css;
    }

    /**
     * Collects Google font names (from the list and from Google CDN links)
     * and the remaining non-Google CDN links.
     *
     * @return array{google: string[], others: string[]}
     */
    private function resolve(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $google = [];
        $others = [];

        // 1) Google Fonts list (name only; any ":weights" part is ignored)
        foreach ($this->lines('google/fonts') as $line) {
            $name = $this->cleanGoogleName(explode(':', $line, 2)[0]);
            if ($name !== '') {
                $google[strtolower($name)] = $name;
            }
        }

        // 2) Custom CDN links
        foreach ($this->lines('custom/urls') as $line) {
            if (!filter_var($line, FILTER_VALIDATE_URL) || stripos($line, 'https://') !== 0) {
                continue;
            }

            $host = strtolower((string) parse_url($line, PHP_URL_HOST));

            if ($host === self::GOOGLE_HOST) {
                // Google link: take the font names, load them with all weights
                foreach ($this->extractNames($line) as $name) {
                    $google[strtolower($name)] = $name;
                }
            } elseif ($host === self::BUNNY_HOST) {
                $names = $this->extractNames($line);
                $others[] = $names
                    ? $this->buildUrl('https://' . self::BUNNY_HOST . '/css', $names)
                    : $line;
            } else {
                $others[] = $line;
            }
        }

        $this->resolved = [
            'google' => array_slice(array_values($google), 0, self::MAX_FONTS),
            'others' => array_slice($others, 0, self::MAX_FONTS),
        ];

        return $this->resolved;
    }

    /**
     * @param string[] $names
     */
    private function buildUrl(string $base, array $names): string
    {
        $families = [];
        foreach ($names as $name) {
            $families[] = str_replace(' ', '+', $name) . ':' . self::ALL_WEIGHTS;
        }

        return $base . '?family=' . implode('%7C', $families) . '&display=swap';
    }

    /**
     * Get font names from a Google/Bunny CSS link (css and css2 formats)
     *
     * @return string[]
     */
    private function extractNames(string $url): array
    {
        $query = (string) parse_url($url, PHP_URL_QUERY);
        $names = [];

        foreach (explode('&', $query) as $part) {
            if (stripos($part, 'family=') !== 0) {
                continue;
            }
            $value = urldecode(substr($part, 7));
            foreach (explode('|', $value) as $family) {
                $name = $this->cleanGoogleName(explode(':', $family, 2)[0]);
                if ($name !== '') {
                    $names[strtolower($name)] = $name;
                }
            }
        }

        return array_values($names);
    }

    private function cleanGoogleName(string $name): string
    {
        $name = trim($name);
        return preg_match('/^[A-Za-z0-9 ]+$/', $name) ? $name : '';
    }

    /**
     * @return string[]
     */
    private function lines(string $path): array
    {
        $raw = (string) $this->getValue($path);
        $lines = preg_split('/\R/', $raw) ?: [];
        return array_values(array_filter(array_map('trim', $lines), static fn ($l) => $l !== ''));
    }

    private function cleanFontName(string $name): string
    {
        $name = trim($name);
        return preg_match('/^[\w\s\-]+$/u', $name) ? $name : '';
    }

    private function getValue(string $path): mixed
    {
        return $this->scopeConfig->getValue(self::PATH . $path, ScopeInterface::SCOPE_STORE);
    }
}
