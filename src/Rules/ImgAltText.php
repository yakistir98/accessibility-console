<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class ImgAltText implements RuleInterface
{
    public function id(): string
    {
        return 'img-alt-text';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement || $node->nodeName !== 'img') {
            return null;
        }

        if (!$node->hasAttribute('alt')) {
            // Check for role="presentation" or role="none"
            $role = $node->getAttribute('role');
            if ($role === 'presentation' || $role === 'none') {
                return null;
            }

            return new Violation(
                $this->id(),
                'Image is missing alt attribute.',
                $node->ownerDocument->saveHTML($node),
                $node->getNodePath(),
                'Add an alt="" attribute describing the image, or alt="" if decorative.'
            );
        }

        return null;
    }
}
