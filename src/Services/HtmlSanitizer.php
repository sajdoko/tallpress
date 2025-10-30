<?php

namespace Sajdoko\TallPress\Services;

class HtmlSanitizer
{
    /**
     * Sanitize HTML content to prevent XSS attacks.
     */
    public function sanitize(string $html): string
    {
        if (! tallpress_setting('editor_sanitize_html', true)) {
            return $html;
        }

        // Use HTMLPurifier if available, otherwise use a simple strip_tags approach
        if (class_exists('HTMLPurifier')) {
            return $this->sanitizeWithPurifier($html);
        }

        return $this->sanitizeBasic($html);
    }

    /**
     * Sanitize using HTMLPurifier (more robust).
     */
    protected function sanitizeWithPurifier(string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();

        // Apply custom configuration
        $purifierConfig = config('tallpress.html_purifier', []);
        foreach ($purifierConfig as $key => $value) {
            $config->set($key, $value);
        }

        $purifier = new \HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * Basic sanitization without HTMLPurifier.
     */
    protected function sanitizeBasic(string $html): string
    {
        $allowed = config('tallpress.html_purifier.HTML.Allowed',
            'p,br,strong,em,u,s,a[href|title],ul,ol,li,blockquote,code,pre,h1,h2,h3,h4,h5,h6,img[src|alt]'
        );

        // Extract allowed tags
        preg_match_all('/([a-z0-9]+)(?:\[.*?\])?/', $allowed, $matches);
        $allowedTags = '<'.implode('><', $matches[1]).'>';

        return strip_tags($html, $allowedTags);
    }

    /**
     * Get excerpt from HTML content.
     */
    public function getExcerpt(string $html, int $length = 200): string
    {
        // Strip all HTML tags to get plain text
        $text = strip_tags($html);

        // Replace multiple whitespace with single space
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim whitespace
        $text = trim($text);

        // If text is shorter than max length, return as is
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        // Truncate to length
        $text = mb_substr($text, 0, $length);

        // Find last space to avoid cutting words
        $lastSpace = mb_strrpos($text, ' ');
        if ($lastSpace !== false) {
            $text = mb_substr($text, 0, $lastSpace);
        }

        return $text.'...';
    }
}
