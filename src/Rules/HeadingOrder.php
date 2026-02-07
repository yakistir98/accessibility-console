<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use DOMXPath;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class HeadingOrder implements RuleInterface
{
    public function id(): string
    {
        return 'heading-order';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement) {
            return null;
        }

        // Check if it matches h1-h6
        if (!preg_match('/^h([1-6])$/', $node->nodeName, $matches)) {
            return null;
        }

        $currentLevel = (int) $matches[1];

        // Find the immediately preceding heading in document order
        // This is expensive but accurate for stateless checking.
        $xpath = new DOMXPath($node->ownerDocument);

        // Query: check all preceding siblings that are headings, get the last one.
        // Or basically all preceding headings in document
        $query = "preceding::*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]";
        $previousHeadings = $xpath->query($query, $node);

        if ($previousHeadings->length === 0) {
            // This is the first heading in document. It SHOULD be h1 (ideally), but skipping check for simplicity
            // or we could enforce "First heading must be h1"
            if ($currentLevel > 1) {
                // Optional: Enforce document starts with H1?
                // Let's purely check order skipping for now.
            }
            return null;
        }

        $lastHeading = $previousHeadings->item($previousHeadings->length - 1);
        preg_match('/^h([1-6])$/', $lastHeading->nodeName, $lastMatches);
        $previousLevel = (int) $lastMatches[1];

        // logic: e.g. prev=h2, curr=h4 (skipped h3) -> Error
        // prev=h2, curr=h2 -> OK
        // prev=h2, curr=h3 -> OK
        // prev=h2, curr=h1 -> OK (new section)

        if ($currentLevel > $previousLevel + 1) {
            return new Violation(
                $this->id(),
                sprintf('Skipped heading level: <%s> follows <%s>.', $node->nodeName, $lastHeading->nodeName),
                $node->ownerDocument->saveHTML($node),
                $node->getNodePath(),
                sprintf('Change <%s> to <%s> or rearrange content.', $node->nodeName, 'h' . ($previousLevel + 1))
            );
        }

        return null;
    }
}
