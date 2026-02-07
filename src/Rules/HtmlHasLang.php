<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMNode;
use DOMElement;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class HtmlHasLang implements RuleInterface
{
    public function id(): string
    {
        return 'html-has-lang';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement || $node->nodeName !== 'html') {
            return null;
        }

        if (!$node->hasAttribute('lang') || trim($node->getAttribute('lang')) === '') {
            return new Violation(
                $this->id(),
                '<html> element is missing a "lang" attribute.',
                $node->ownerDocument->saveHTML($node),
                $node->getNodePath(),
                'Add a lang attribute to the html tag, e.g., <html lang="tr">.'
            );
        }

        return null;
    }
}
