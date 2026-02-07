<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMNode;
use DOMElement;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class EmptyLink implements RuleInterface
{
    public function id(): string
    {
        return 'empty-link';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement || $node->nodeName !== 'a') {
            return null;
        }

        // 1. Check if it has aria-label or aria-labelledby
        if ($node->hasAttribute('aria-label') || $node->hasAttribute('aria-labelledby')) {
            return null;
        }

        // 2. Check text content
        $text = trim($node->textContent);
        if (!empty($text)) {
            return null;
        }

        // 3. Check for images with alt text inside
        $images = $node->getElementsByTagName('img');
        foreach ($images as $img) {
            if ($img->hasAttribute('alt') && trim($img->getAttribute('alt')) !== '') {
                return null;
            }
        }

        // 4. Check for SVGs (simplified check) - often used as icons
        if ($node->getElementsByTagName('svg')->length > 0) {
            // SVGs should have titles or ARIA, but we'll flag strict empty links for now unless they have aria-label on parent
            return new Violation(
                $this->id(),
                'Link contains no text and no accessible image.',
                $node->ownerDocument->saveHTML($node),
                $node->getNodePath(),
                'Add text content, an image with alt text, or an aria-label to the <a> tag.'
            );
        }

        return new Violation(
            $this->id(),
            'Link is empty.',
            $node->ownerDocument->saveHTML($node),
            $node->getNodePath(),
            'Add text content or an aria-label.'
        );
    }
}
