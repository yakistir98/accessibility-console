<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class FrameTitle implements RuleInterface
{
    public function id(): string
    {
        return 'frame-title';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement || ($node->nodeName !== 'iframe' && $node->nodeName !== 'frame')) {
            return null;
        }

        if (!$node->hasAttribute('title') || trim($node->getAttribute('title')) === '') {
            return new Violation(
                $this->id(),
                "Frame <{$node->nodeName}> is missing a title attribute.",
                $node->ownerDocument->saveHTML($node),
                $node->getNodePath(),
                'Add a title attribute describing the frame content.'
            );
        }

        return null;
    }
}
