<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Utils;

use DOMElement;
use DOMNode;
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
     * @var bool|null
     */
    private $isScoped;

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
    }

    /**
     * @param DOMNode|DOMElement|null $styleElement
     */
    public function compile($styleElement): ?string
    {
        if (!$styleElement instanceof DOMElement) {
            return null;
        }

        $this->handle($styleElement);

        $style = $styleElement->textContent;
        if ($this->lang === 'scss') {
            $style = $this->scssCompiler->compile($style);
        }
        if ($this->isScoped) {
            $style = preg_replace('/((?:^|[^},]*?)\S+)(\s*[{,])/i', '$1[' . $this->scopedAttribute . ']$2', $style);
        }

        return '<style>' . $style . '</style>';
    }

    public function isScoped(): ?bool
    {
        return $this->isScoped;
    }

    public function getScopedAttribute(): string
    {
        return $this->scopedAttribute;
    }

    private function handle(DOMElement $styleElement): void
    {
        $this->isScoped = $styleElement->hasAttribute('scoped');
        if (
            !$this->scssCompiler instanceof ScssCompiler
            && $styleElement->hasAttribute('lang')
            && $styleElement->getAttribute('lang') === 'scss'
        ) {
            $this->lang = 'scss';
            $this->scssCompiler = new ScssCompiler();
        }
    }
}
