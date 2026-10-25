<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Http;
use Throwable;

class MetadataExtractor
{
    /**
     * Maximum number of response bytes read when parsing a remote document.
     */
    private const MAX_BODY_BYTES = 1_048_576;

    /**
     * Fetch a URL and extract the metadata used to render a wall card.
     *
     * @return array{title: string, description: string|null, image_url: string|null, favicon_url: string|null, site_name: string|null}
     */
    public function extract(string $url): array
    {
        $fallback = $this->fallback($url);

        try {
            $response = Http::withHeaders([
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'es-MX,es;q=0.9,en;q=0.7',
            ])
                ->withUserAgent('Mozilla/5.0 (compatible; UrlWall/1.0; +https://localhost)')
                ->connectTimeout(5)
                ->timeout(10)
                ->get($url);
        } catch (Throwable) {
            return $fallback;
        }

        if (! $response->successful()) {
            return $fallback;
        }

        $contentType = (string) $response->header('Content-Type');
        if ($contentType !== '' && ! str_contains($contentType, 'html')) {
            return $fallback;
        }

        $html = mb_substr($response->body(), 0, self::MAX_BODY_BYTES);
        $parsed = $this->parseHtml($this->forceUtf8($html, $contentType), $url);

        return [
            'title' => $parsed['title'] !== '' ? $parsed['title'] : $fallback['title'],
            'description' => $parsed['description'] ?? $fallback['description'],
            'image_url' => $parsed['image_url'] ?? $fallback['image_url'],
            'favicon_url' => $parsed['favicon_url'] ?? $fallback['favicon_url'],
            'site_name' => $parsed['site_name'] ?? $fallback['site_name'],
        ];
    }

    /**
     * Make document text safe for DOMDocument, which otherwise assumes Latin-1
     * when no charset is declared, corrupting accented characters.
     */
    private function forceUtf8(string $html, string $contentType): string
    {
        if (preg_match('/charset=([\w-]+)/i', $contentType, $matches) === 1
            && strcasecmp($matches[1], 'utf-8') !== 0
            && strcasecmp($matches[1], 'utf8') !== 0) {
            $converted = mb_convert_encoding($html, 'UTF-8', $matches[1]);

            if (is_string($converted)) {
                return $converted;
            }
        }

        return '<?xml encoding="UTF-8">'."\n".$html;
    }

    /**
     * Metadata derived without fetching the document.
     *
     * @return array{title: string, description: string|null, image_url: string|null, favicon_url: string|null, site_name: string|null}
     */
    private function fallback(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST);
        $host = is_string($host) ? $host : null;
        $path = (string) parse_url($url, PHP_URL_PATH);

        $segment = trim($path, '/');
        $segment = $segment !== '' ? urldecode(strtok($segment, '/')) : '';

        return [
            'title' => $segment !== '' ? str_replace(['-', '_'], ' ', $segment) : ($host ?? $url),
            'description' => null,
            'image_url' => null,
            'favicon_url' => $host !== null ? "https://{$host}/favicon.ico" : null,
            'site_name' => $host !== null ? ltrim($host, 'www.') : null,
        ];
    }

    /**
     * Read Open Graph, Twitter and basic HTML metadata from a document.
     *
     * @return array{title: string, description: string|null, image_url: string|null, favicon_url: string|null, site_name: string|null}
     */
    private function parseHtml(string $html, string $baseUrl): array
    {
        $previous = libxml_use_internal_errors(true);

        $document = new DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($document);

        $meta = function (string $expression) use ($xpath): ?string {
            $nodes = $xpath->query($expression);
            $node = $nodes !== false ? $nodes->item(0) : null;
            $value = $node instanceof DOMElement ? trim($node->getAttribute('content')) : ($node !== null ? trim($node->nodeValue ?? '') : '');

            return $value !== '' ? $value : null;
        };

        $titleNodes = $xpath->query('//title');
        $titleTag = $titleNodes !== false ? $titleNodes->item(0) : null;

        $title = $meta("//meta[@property='og:title' or @name='og:title']/@content")
            ?? $meta("//meta[@name='twitter:title']/@content")
            ?? ($titleTag !== null ? trim($titleTag->textContent) : null);

        $description = $meta("//meta[@property='og:description' or @name='og:description']/@content")
            ?? $meta("//meta[@name='description']/@content")
            ?? $meta("//meta[@name='twitter:description']/@content");

        $imageUrl = $meta("//meta[@property='og:image:secure_url' or @name='og:image:secure_url']/@content")
            ?? $meta("//meta[@property='og:image' or @name='og:image']/@content")
            ?? $meta("//meta[@name='twitter:image']/@content");

        $siteName = $meta("//meta[@property='og:site_name' or @name='og:site_name']/@content");

        $favicon = null;
        foreach (['//link[contains(@rel, "icon")]/@href', '//link[@rel="shortcut icon"]/@href'] as $expression) {
            $candidate = $meta($expression);
            if ($candidate !== null) {
                $favicon = $candidate;

                break;
            }
        }

        $host = parse_url($baseUrl, PHP_URL_HOST);
        if ($favicon === null && is_string($host)) {
            $favicon = "https://{$host}/favicon.ico";
        }

        return [
            'title' => $title ?? '',
            'description' => $description !== null ? mb_substr($description, 0, 1000) : null,
            'image_url' => $imageUrl !== null ? $this->absoluteUrl($baseUrl, $imageUrl) : null,
            'favicon_url' => $favicon !== null ? $this->absoluteUrl($baseUrl, $favicon) : null,
            'site_name' => $siteName ?? (is_string($host) ? ltrim($host, 'www.') : null),
        ];
    }

    /**
     * Resolve a possibly relative reference against the document URL.
     */
    private function absoluteUrl(string $baseUrl, string $candidate): ?string
    {
        $candidate = trim($candidate);

        if ($candidate === '' || preg_match('#^(data|javascript|blob):#i', $candidate) === 1) {
            return null;
        }

        if (preg_match('#^https?://#i', $candidate) === 1) {
            return $candidate;
        }

        if (str_starts_with($candidate, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';

            return "{$scheme}:{$candidate}";
        }

        $parts = parse_url($baseUrl);
        if ($parts === false || ! isset($parts['host'])) {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $authority = $parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');

        if (str_starts_with($candidate, '/')) {
            return "{$scheme}://{$authority}{$candidate}";
        }

        $basePath = $parts['path'] ?? '/';
        $directory = str_contains($basePath, '/') ? substr($basePath, 0, strrpos($basePath, '/') + 1) : '/';

        return "{$scheme}://{$authority}{$directory}{$candidate}";
    }
}
