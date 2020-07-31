<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Utils;

use DOMElement;
use Ramsey\Uuid\Uuid;
use ScssPhp\ScssPhp\Compiler as ScssCompiler;

class StyleBuilder
{
    /**
     * @var DOMElement|null
     */
    private $styleElement;

    /**
     * @var bool|null
     */
    private $isScoped;

    /**
     * @var string|null
     */
    private $scopedAttribute;

    /**
     * @var string|null
     */
    private $lang;

    /**
     * @var ScssCompiler|null
     */
    private $scssCompiler;

    /**
     * StyleBuilder constructor.
     */
    public function __construct()
    {
        $this->scopedAttribute = 'data-v-' . substr(md5(Uuid::uuid4()->toString()), 0, 8);
    }

    private function loadPhpScss(): void
    {
        $this->scssCompiler = new ScssCompiler();
    }

    public function setStyleNode(?DOMElement $styleElement): void
    {
        $this->styleElement = $styleElement;
        $this->handle();
    }

    private function handle(): void
    {
        $this->isScoped = $this->styleElement->hasAttribute('scoped');
        if (
            !$this->scssCompiler instanceof ScssCompiler
            && $this->styleElement->hasAttribute('lang')
            && $this->styleElement->getAttribute('lang') === 'scss'
        ) {
            $this->lang = 'scss';
            $this->loadPhpScss();
        }
    }

    public function getScopedAttribute(): string
    {
        return $this->scopedAttribute;
    }

    public function isScoped(): ?bool
    {
        return $this->isScoped;
    }

    public function getStyleOutput(): string
    {
        $style = $this->styleElement->textContent;
        if ($this->lang === 'scss') {
            $style = $this->scssCompiler->compile($style);
        }
        if ($this->isScoped) {
            $style = preg_replace('/((?:^|[^},]*?)[\S]+)(\s*[{,])/i', '$1[' . $this->scopedAttribute . ']$2', $style);
        }

        return '<style>' . $style . '</style>';
    }
}
