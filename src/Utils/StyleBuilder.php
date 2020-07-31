<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Utils;

use DOMElement;
use Exception;
use Ramsey\Uuid\Uuid;
use ScssPhp\ScssPhp\Compiler as ScssCompiler;

class StyleBuilder
{
    /**
     * @var ScssCompiler|null
     */
    private $scssCompiler;

    /**
     * @var string|null
     */
    private $lang;

    /**
     * @var bool
     */
    private $hasScoped;

    /**
     * @var string|null
     */
    private $scopedAttribute;

    /**
     * StyleBuilder constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->scopedAttribute = 'data-v-' . substr(md5(Uuid::uuid4()->toString()), 0, 8);
        $this->hasScoped = false;
    }

    /**
     * @param DOMElement|null $styleElement
     */
    public function compile($styleElement): ?string
    {
        if (!$styleElement instanceof DOMElement) {
            return null;
        }

        if (
            !$this->scssCompiler instanceof ScssCompiler
            && $styleElement->hasAttribute('lang')
            && $styleElement->getAttribute('lang') === 'scss'
        ) {
            $this->lang = 'scss';
            $this->scssCompiler = new ScssCompiler();
        }

        $style = $styleElement->textContent;
        if ($this->lang === 'scss') {
            $style = $this->scssCompiler->compile($style);
        }
        if ($styleElement->hasAttribute('scoped')) {
            $this->hasScoped = true;
            $style = preg_replace('/((?:^|[^},]*?)\S+)(\s*[{,])/i', '$1[' . $this->scopedAttribute . ']$2', $style);
        }

        return '<style>' . $style . '</style>';
    }

    public function hasScoped(): ?bool
    {
        return $this->hasScoped;
    }

    public function getScopedAttribute(): string
    {
        return $this->scopedAttribute;
    }
}
