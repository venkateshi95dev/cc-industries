<?php
declare(strict_types=1);

namespace Crimson\Performance\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Crimson\Performance\Helper\Config;

class HttpResponsePlugin
{
    private string $pageHtml = '';
    private const MATCH_REGEX = [
        'preload' => [
            'css'   => '/href="([^>"]+\.css(?:\?[^\"]*)?)"/i',
            'image' => '/<img[^>]+src="([^">]+\.(?:ico|jpg|jpeg|png|gif|svg|webp))"[^>]*>/i',
            'pb_bg' => '/data-background-images\s*=\s*(["\'])(?!\{\})(\{.+?\})\1/i',
            'js'    => '/<script[^>]+src="([^">]+\.js(?:\?[^\"]*)?)"[^>]*>/i',
            'js_link'  => '/<link[^>]+rel="(?:prefetch|preload)"[^>]+as="script"[^>]+href="([^">]+\.js(?:\?[^\"]*)?)"[^>]*>/i'
        ],
        'preconnect'     => '/((src|second-src|href)="([^>"]+\.(?:ico|jpg|jpeg|png|gif|svg|js|css|json|webp))")/i',
        'move_print_css' => '/\<link[^>]+media="print"[^>]*?\>/i'
    ];

    public function __construct(
        private readonly Config $config,
        private readonly RequestInterface $request
    ) {}

    private array $headSnippets = [];

    /**
     * @param Http $response
     * @return Http
     */
    public function beforeSendResponse(Http $response): Http
    {
        if (!$this->config->isEnabled() || !$this->canChangeRequest($response)) {
            return $response;
        }

        $body = $response->getBody();
        $this->pageHtml = $body;

        $this->collectPreloads($body)
             ->collectPreconnect($body)
             ->moveElementsToBottom($body);

        if ($this->headSnippets) {
            $body = str_replace(
                '</head>',
                implode("\n", $this->headSnippets) . "\n</head>",
                $body
            );
        }

        $response->setBody($body);
        return $response;
    }

    /**
     * @param string $body
     * @return self
     */
    private function collectPreloads(string &$body): self
    {
        $this->collectPreloadCSS($body)
             ->collectPreloadJS()
             ->collectPreloadPbBackgrounds($body)
             ->collectPreloadImages($body);
        return $this;
    }

    /**
     * @param string $body
     * @return self
     */
    private function collectPreloadCSS(string $body): self
    {
        preg_match_all(self::MATCH_REGEX['preload']['css'], $body, $m, PREG_SET_ORDER);
        $pageCss = array_unique(array_column($m, 1));

        $extra    = $this->config->getServerPushLinks()['style'] ?? [];
        $extra    = array_filter(array_map('trim', $extra));

        foreach ($extra as $entry) {
            if ($entry === '') {
                continue;
            }

            if (preg_match('#^(https?:)?//#', $entry) || str_starts_with($entry, '/')) {
                $this->headSnippets[] = '<link rel="preload" href="' . $entry . '" as="style" />';
                continue;
            }

            foreach ($pageCss as $href) {
                $normalizedHref = str_replace('.min.css', '.css', $href);

                if (str_ends_with($normalizedHref, $entry)) {
                    $this->headSnippets[] = '<link rel="preload" href="' . $href . '" as="style" />';
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * @return self
     */
    private function collectPreloadJS(): self
    {
        $include = $this->config->getServerPushLinks()['script'] ?? [];
        $include = array_filter(array_map('trim', $include));
        if (!$include) {
            return $this;
        }

        preg_match_all(self::MATCH_REGEX['preload']['js'],  $this->pageHtml, $m1, PREG_SET_ORDER);
        preg_match_all(self::MATCH_REGEX['preload']['js_link'], $this->pageHtml, $m2, PREG_SET_ORDER);
        $pageScripts = array_unique(array_merge(
            array_column($m1, 1),
            array_column($m2, 1)
        ));

        foreach ($include as $entry) {
            if ($entry === '') {
                continue;
            }

            if (preg_match('#^(https?:)?//#', $entry) || str_starts_with($entry, '/')) {
                $this->headSnippets[] = '<link rel="preload" href="' . $entry . '" as="script" />';
                continue;
            }

            foreach ($pageScripts as $src) {
                $normalizedSrc = str_replace('.min.js', '.js', $src);

                if (str_ends_with($normalizedSrc, $entry)) {
                    $this->headSnippets[] = '<link rel="preload" href="' . $src . '" as="script" />';
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * @param string $body
     * @return void
     */
    private function collectPreloadImages(string $body): void
    {
        $include = $this->config->getServerPushLinks()['image'] ?? [];
        $include = array_filter(array_map('trim', $include));

        if (!$include) {
            return;
        }

        foreach ($include as $src) {
            if ($src === '') {
                continue;
            }

            if (preg_match('#^(https?:)?//#', $src) || str_starts_with($src, '/')) {
                $this->headSnippets[] = '<link rel="preload" href="' . $src . '" as="image" fetchpriority="high" />';
                continue;
            }

            if (preg_match_all(self::MATCH_REGEX['preload']['image'], $body, $imgMatches, PREG_SET_ORDER)) {
                foreach ($imgMatches as $m) {
                    $href = $m[1];
                    if (str_ends_with($href, $src)) {
                        $this->headSnippets[] = '<link rel="preload" href="' . $href . '" as="image" fetchpriority="high" />';
                        break;
                    }
                }
            }
        }
    }

    private function collectPreloadPbBackgrounds(string $body): self
    {
        preg_match_all(self::MATCH_REGEX['preload']['pb_bg'], $body, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $raw = $m[2] ?? '';
            if ($raw === '') { continue; }

            $json = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5);

            if (str_contains($json, '\"')) {
                $json = str_replace('\"', '"', $json);
            }

            $data = json_decode($json, true);
            if (!is_array($data)) { continue; }

            $src = $data['desktop_image'] ?? $data['image'] ?? $data['background_image'] ?? null;
            if (!$src) { continue; }

            $this->headSnippets[] = '<link rel="preload" href="' . $src . '" as="image" fetchpriority="high" />';
            break;
        }
        return $this;
    }

    /**
     * @param string $body
     * @return self
     */
    private function collectPreconnect(string $body): self
    {
        preg_match_all(self::MATCH_REGEX['preconnect'], $body, $matches, PREG_SET_ORDER);

        $autoOrigins = array_unique(array_column($matches, 3));

        $autoOrigins = array_map(static function ($url) {
            if (!is_string($url) || $url === '' || str_starts_with($url, '/')) {
                return null;
            }

            $p = parse_url($url);
            return isset($p['scheme'], $p['host'])
                ? $p['scheme'] . '://' . $p['host']
                : null;
        }, $autoOrigins);

        $autoOrigins = array_filter(
            $autoOrigins,
            fn ($o) =>
                $o &&
                parse_url($o, PHP_URL_HOST) !== $this->request->getHttpHost()
        );

        $additional = array_map(
            static fn ($o) => rtrim($o, '/'),
            $this->config->getAdditionalPreconnect()
        );

        $additional = array_unique($additional);
        $autoOrigins = array_diff(array_unique($autoOrigins), $additional);

        $max = 4;

        if (count($additional) >= $max) {
            $final = $additional;
        } else {
            $remaining = $max - count($additional);
            $final = array_merge(
                $additional,
                array_slice($autoOrigins, 0, $remaining)
            );
        }

        foreach ($final as $origin) {
            $this->headSnippets[] =
                '<link rel="preconnect" href="' . $origin . '" crossorigin>';
        }

        return $this;
    }

    /**
     * @param string $body
     * @return void
     */
    private function moveElementsToBottom(string &$body): void
    {
        preg_match_all(self::MATCH_REGEX['move_print_css'], $body, $matches, PREG_SET_ORDER);
        $printLinks = array_column($matches, 0);
        foreach ($printLinks as $link) {
            $body = str_replace($link, '', $body);
        }
        if ($printLinks) {
            $body = str_replace('</body>', implode("\n", $printLinks) . "\n</body>", $body);
        }
    }

    /**
     * @param Http $response
     * @return bool
     */
    private function canChangeRequest(Http $response): bool
    {
        return !$this->request->isXmlHttpRequest() && str_contains($response->getBody(), '<html');
    }
}
