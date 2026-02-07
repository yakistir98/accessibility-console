<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class ButtonName implements RuleInterface
{
    public function id(): string
    {
        return 'button-name';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement || $node->nodeName !== 'button') {
            return null;
        }

        // 1. Aria Label
        if ($node->hasAttribute('aria-label') || $node->hasAttribute('aria-labelledby')) {
            return null;
        }

        // 2. Text Content
        if (trim($node->textContent) !== '') {
            return null;
        }

        // 3. Image with Alt
        $imgs = $node->getElementsByTagName('img');
        foreach ($imgs as $img) {
            if ($img->hasAttribute('alt') && trim($img->getAttribute('alt')) !== '') {
                return null;
            }
        }

        // 4. SVG with title? (Simplified)

        return new Violation(
            $this->id(),
            'Button is empty or has no accessible name.',
            $node->ownerDocument->saveHTML($node),
            $node->getNodePath(),
            'Add text content, an aria-label, or an image with alt text.'
        );
    }
}
