<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Utils;

use DOMElement;
use Ramsey\Uuid\Uuid;

class StyleBuilder
{
    /**
     * @var DOMElement|null
     */
    private $styleElement;

    /**
     * @var string
     */
    protected $scopedAttribute;

    /**
     * @var ?bool
     */
    private $isScoped;

    /**
     * StyleBuilder constructor.
     */
    public function __construct()
    {
        $this->scopedAttribute = 'data-v-' . substr(md5(Uuid::uuid4()->toString()), 0, 8);
    }

    public function setStyleNode(?DOMElement $styleElement): void
    {
        $this->styleElement = $styleElement;
        $this->handle();
    }

    private function handle(): void
    {
        $this->isScoped = $this->styleElement->hasAttribute('scoped');
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
        if ($this->isScoped) {
            $style = preg_replace('/((?:^|[^},]*?)[\S]+)(\s*[{,])/i', '$1[' . $this->scopedAttribute . ']$2', $style);
        }

        return '<style>' . $style . '</style>';
    }
}
