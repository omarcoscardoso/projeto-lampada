<?php

namespace App\Services;

class WhatsAppFormatter
{
    public static function format(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // 1. Line breaks
        $formatted = preg_replace('/<br\s*\/?>/i', "\n", $html);

        // 2. Titles (h1-h6) become bold
        $formatted = preg_replace('/<h[1-6][^>]*>/i', "\n\n*", $formatted);
        $formatted = preg_replace('/<\/h[1-6]>/i', "*\n", $formatted);

        // 3. Block tags correctly handled
        $formatted = preg_replace('/<(p|div|section)[^>]*>/i', "\n", $formatted);
        $formatted = preg_replace('/<\/(p|div|section)>/i', "\n", $formatted);

        // 4. Lists: use a simple hyphen bullet
        $formatted = preg_replace('/<li[^>]*>/i', "\n- ", $formatted);
        $formatted = preg_replace('/<\/li>/i', "\n", $formatted);
        $formatted = preg_replace('/<\/?(ul|ol)[^>]*>/i', "\n", $formatted);

        // 5. Stylistic tags
        $formatted = preg_replace('/<(b|strong)[^>]*>/i', '*', $formatted);
        $formatted = preg_replace('/<\/(b|strong)>/i', '*', $formatted);
        $formatted = preg_replace('/<(i|em)[^>]*>/i', '_', $formatted);
        $formatted = preg_replace('/<\/(i|em)>/i', '_', $formatted);
        $formatted = preg_replace('/<(s|strike|del)[^>]*>/i', '~', $formatted);
        $formatted = preg_replace('/<\/(s|strike|del)>/i', '~', $formatted);
        $formatted = preg_replace('/<(code|pre)[^>]*>/i', '```', $formatted);
        $formatted = preg_replace('/<\/(code|pre)>/i', '```', $formatted);

        // 6. Strip remaining tags and decode entities
        $text = strip_tags($formatted);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // 7. Cleanup
        // Step A: Replace bullet followed immediately by newline (common with nested tags like <li><p>)
        $text = preg_replace('/(-)\s*[\r\n]+/', '$1 ', $text);

        // Step B: Clean up whitespace on each line but preserve bullets
        $lines = explode("\n", $text);
        $cleanLines = array_map(function ($line) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '-')) {
                // Ensure there is only one space after hyphen
                return '- '.trim(substr($trimmed, 1));
            }

            return $trimmed;
        }, $lines);

        $text = implode("\n", $cleanLines);

        // Step C: Collapse excessive newlines
        // First collapse any sequence of newlines before a bullet into a SINGLE newline
        $text = preg_replace('/\n+(- )/', "\n$1", $text);

        // Then collapse other sequences of 3+ newlines to 2 (maximum 1 blank line between paragraphs)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
