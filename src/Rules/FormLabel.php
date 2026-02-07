<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use DOMXPath;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class FormLabel implements RuleInterface
{
    public function id(): string
    {
        return 'form-label';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement) {
            return null;
        }

        $inputTypes = ['text', 'password', 'email', 'search', 'tel', 'url', 'checkbox', 'radio', 'number', 'date'];
        if ($node->nodeName === 'input' && in_array($node->getAttribute('type'), $inputTypes)) {
            return $this->checkInput($node);
        }

        if ($node->nodeName === 'textarea' || $node->nodeName === 'select') {
            return $this->checkInput($node);
        }

        return null;
    }

    private function checkInput(DOMElement $node): ?Violation
    {
        // 1. Check for aria-label or aria-labelledby
        if ($node->hasAttribute('aria-label') || $node->hasAttribute('aria-labelledby')) {
            return null;
        }

        // 2. Check for implicit label (wrapped in <label>)
        $parent = $node->parentNode;
        while ($parent) {
            if ($parent->nodeName === 'label') {
                return null;
            }
            $parent = $parent->parentNode;
        }

        // 3. Check for explicit label (for="id")
        if ($node->hasAttribute('id')) {
            $id = $node->getAttribute('id');
            $xpath = new DOMXPath($node->ownerDocument);
            // Look for a label with for="$id"
            $labels = $xpath->query("//label[@for='$id']");
            if ($labels->length > 0) {
                return null;
            }
        }

        // 4. Check for title attribute (weak fallback but valid)
        if ($node->hasAttribute('title')) {
            return null;
        }

        return new Violation(
            $this->id(),
            "Form field <{$node->nodeName}> missing label.",
            $node->ownerDocument->saveHTML($node),
            $node->getNodePath(),
            'Associate a <label> via "for" attribute, wrap in <label>, or use "aria-label".'
        );
    }
}
