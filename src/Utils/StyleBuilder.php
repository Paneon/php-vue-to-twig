<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Utils;

use DOMElement;
use Exception;
use ScssPhp\ScssPhp\Compiler as ScssCompiler;
use ScssPhp\ScssPhp\Exception\CompilerException;

class StyleBuilder
{
    public const STYLE_NO = 0;
    public const STYLE_SCOPED = 1;
    public const STYLE = 2;
    public const STYLE_ALL = 3;

    /**
     * @var int
     */
    private $outputType;

    /**
     * @var string
     */
    private $scssData;

    /**
     * @var ScssCompiler|null
     */
    private $scssCompiler;

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
        $this->outputType = self::STYLE_ALL;
        $this->scssData = '';
        $this->scssCompiler = null;
        $this->hasScoped = false;
        $this->scopedAttribute = '';
    }

    public function setOutputType(int $outputType): void
    {
        $this->outputType = $outputType;
    }

    public function getOutputType(): int
    {
        return $this->outputType;
    }

    public function setScssData(string $data): void
    {
        $this->scssData = $data;
    }

    public function compile(?DOMElement $styleElement): ?string
    {
        if (!$styleElement instanceof DOMElement
            || ($styleElement->hasAttribute('scoped') && !($this->outputType & self::STYLE_SCOPED))
            || (!$styleElement->hasAttribute('scoped') && !($this->outputType & self::STYLE))) {
            return null;
        }

        $style = $styleElement->textContent;

        if ($styleElement->hasAttribute('lang') && $styleElement->getAttribute('lang') === 'scss') {
            if ($this->scssCompiler === null) {
                $this->scssCompiler = new ScssCompiler();
            }
            try {
                $style = $this->scssCompiler->compile($this->scssData . ' ' . $style);
            } catch (CompilerException $e) {
                $style = "\n/* Warning: " . $e->getMessage() . " */\n";
            }
        }

        if ($styleElement->hasAttribute('scoped')) {
            $this->hasScoped = true;
            $style = preg_replace(
                '/((?:^|\s)\s*[^@\s,][a-z0-9-_:]+?[a-z0-9-_]+)(\s*[{,])/i',
                '$1[' . $this->scopedAttribute . ']$2',
                $style
            );
        }

        return '<style>' . $style . '</style>';
    }

    public function hasScoped(): ?bool
    {
        return $this->hasScoped;
    }

    public function setScopedAttribute(string $scopedAttribute): void
    {
        $this->scopedAttribute = $scopedAttribute;
    }

    public function getScopedAttribute(): string
    {
        return $this->scopedAttribute;
    }
}
