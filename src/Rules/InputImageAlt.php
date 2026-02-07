<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class InputImageAlt implements RuleInterface
{
    public function id(): string
    {
        return 'input-image-alt';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement || $node->nodeName !== 'input') {
            return null;
        }

        if ($node->getAttribute('type') !== 'image') {
            return null;
        }

        if (!$node->hasAttribute('alt') || trim($node->getAttribute('alt')) === '') {
            return new Violation(
                $this->id(),
                'Input type="image" is missing alt attribute.',
                $node->ownerDocument->saveHTML($node),
                $node->getNodePath(),
                'Add an alt attribute describing the function of the image button.'
            );
        }

        return null;
    }
}
