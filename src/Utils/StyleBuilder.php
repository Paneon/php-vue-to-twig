<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Utils;

use DOMElement;

class StyleBuilder
{
    /**
     * @var DOMElement|null
     */
    private $styleElement;

    /**
     * StyleBuilder constructor.
     */
    public function setStyleNode(?DOMElement $styleElement): void
    {
        $this->styleElement = $styleElement;
    }

    public function getStyleOutput(): string
    {
        return '<style>' . $this->styleElement->textContent . '</style>';
    }
}
