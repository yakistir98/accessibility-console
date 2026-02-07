<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class MetaViewport implements RuleInterface
{
    public function id(): string
    {
        return 'meta-viewport';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement || $node->nodeName !== 'meta') {
            return null;
        }

        if ($node->getAttribute('name') !== 'viewport') {
            return null;
        }

        $content = $node->getAttribute('content');

        // Check for user-scalable=no or maximum-scale < 2
        if (stripos($content, 'user-scalable=no') !== false || stripos($content, 'maximum-scale=1') !== false) {
            return new Violation(
                $this->id(),
                'Viewport meta tag prevents zooming.',
                $node->ownerDocument->saveHTML($node),
                $node->getNodePath(),
                'Remove "user-scalable=no" and ensure "maximum-scale" is at least 2.0.'
            );
        }

        return null;
    }
}
