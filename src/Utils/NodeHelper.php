<?php

declare(strict_types=1);

namespace Paneon\VueToTwig\Utils;

use DOMNode;

class NodeHelper
{
    /**
     * @param DOMNode[] $nodes
     */
    public function removeNodes(array $nodes): void
    {
        foreach ($nodes as $node) {
            $this->removeNode($node);
        }
    }

    public function removeNode(DOMNode $node): void
    {
        $node->parentNode->removeChild($node);
    }
}
