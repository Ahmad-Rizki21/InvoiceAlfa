<?php

declare(strict_types=1);

namespace App\Services\Minifier;

class HtmlMinifier
{
    // Run minifier
    public static function minify(string $html): string
    {
        $output = preg_replace('~>\s*\n\s*<~', '><', $html);
        $output = preg_replace('~\n\s+~', '', $output);

        return $output;
    }
}
