<?php

declare(strict_types=1);

namespace App\Services\Minifier;

use Illuminate\View\Compilers\BladeCompiler as BaseCompiler;

class BladeCompiler extends BaseCompiler
{
    /**
     * Compile the given Blade template contents.
     *
     * @param  string  $value
     * @return string
     */
    public function compileString($value)
    {
        $contents = parent::compileString($value);

        if (strpos($this->getPath(), 'console.blade.php')) {
            $contents = HtmlMinifier::minify($contents);
        }

        return $contents;
    }
}
