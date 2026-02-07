<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class FieldsetLegend implements RuleInterface
{
    public function id(): string
    {
        return 'fieldset-legend';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement || $node->nodeName !== 'fieldset') {
            return null;
        }

        $first = $node->firstElementChild; // PHP 8+ DOMElement property, or find first child
        // If older PHP (DOMElement doesn't always have firstElementChild in extremely old versions, but 7.4/8.0 should carry it via DOMNodelist if strict)
        // Let's be safe and search children.

        $hasLegend = false;
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->nodeName === 'legend') {
                    $hasLegend = true;
                }
                break; // Legend should be the first element child
            }
        }

        if (!$hasLegend) {
            return new Violation(
                $this->id(),
                'Fieldset is missing a legend.',
                $node->ownerDocument->saveHTML($node),
                $node->getNodePath(),
                'Add a <legend> tag as the first child of the fieldset.'
            );
        }

        return null;
    }
}
