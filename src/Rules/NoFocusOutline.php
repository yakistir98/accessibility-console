<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class NoFocusOutline implements RuleInterface
{
    public function id(): string
    {
        return 'no-focus-outline';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement) {
            return null;
        }

        // 1. Check Inline Style
        if ($node->hasAttribute('style')) {
            $style = strtolower($node->getAttribute('style'));
            if ($this->hasBadOutline($style)) {
                return new Violation(
                    $this->id(),
                    'Focus outline removed (inline style).',
                    $node->ownerDocument->saveHTML($node),
                    $node->getNodePath(),
                    'Remove "outline: 0" or "outline: none" to preserve accessibility focus.'
                );
            }
        }

        // 2. Check <style> tag content for global rules
        if ($node->nodeName === 'style') {
            $css = strtolower($node->textContent);
            // Regex to find "outline: 0" or "outline: none" inside blocks
            // This is a rough check, might have false positives if inside comments (though we lowercased it)
            if ($this->hasBadOutline($css)) {
                return new Violation(
                    $this->id(),
                    'Focus outline removed in CSS block.',
                    // Show a truncated snippet of css
                    substr($node->textContent, 0, 100) . '...',
                    $node->getNodePath(),
                    'Check CSS for "outline: 0" or "outline: none".'
                );
            }
        }

        return null;
    }

    private function hasBadOutline($css)
    {
        // matches: outline:0, outline:none, outline: 0, outline: none
        // but try to avoid "outline: 0px solid red" which is technically 0 but implies border? NO, outline:0 is usually bad.
        // strict check
        return preg_match('/outline\s*:\s*(0|none)\s*(;|}|!)/', $css . ';');
    }
}
