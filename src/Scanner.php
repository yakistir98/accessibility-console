<?php

namespace YakNet\AccessibilityConsole;

use DOMDocument;
use DOMXPath;
use YakNet\AccessibilityConsole\Rule\RuleInterface;

class Scanner
{
    /** @var RuleInterface[] */
    private $rules = [];

    public function addRule(RuleInterface $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * @param string $html
     * @return Violation[]
     */
    public function scan(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $dom = new DOMDocument();

        // Suppress warnings for invalid HTML structure (common in partials or extensive frameworks)
        $previousState = libxml_use_internal_errors(true);

        // UTF-8 hack: append meta charset if missing, or just rely on XML declaration
        // Using mb_convert_encoding if available is also good practice, but this hack works well for now.
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);

        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        if (!$loaded) {
            // Could not parse HTML, return empty or a specific violation?
            // For stability, let's just return empty for now, or log it if we had a logger.
            return [];
        }

        $xpath = new DOMXPath($dom);
        $violations = [];

        // Get all elements
        $elements = $dom->getElementsByTagName('*');

        foreach ($elements as $element) {
            foreach ($this->rules as $rule) {
                try {
                    $violation = $rule->check($element);
                    if ($violation) {
                        $violations[] = $violation;
                    }
                } catch (\Throwable $e) {
                    // Prevent one bad rule from crashing the entire scan
                    // Future: Add a "SystemViolation" to report this error?
                    // For now, silently continue to ensure stability as requested.
                    continue;
                }
            }
        }

        return $violations;
    }
}
