<?php

namespace YakNet\AccessibilityConsole\Rules;

use DOMElement;
use DOMNode;
use YakNet\AccessibilityConsole\Rule\RuleInterface;
use YakNet\AccessibilityConsole\Violation;

class ColorContrast implements RuleInterface
{
    public function id(): string
    {
        return 'color-contrast';
    }

    public function check(DOMNode $node): ?Violation
    {
        if (!$node instanceof DOMElement) {
            return null;
        }

        $style = $node->getAttribute('style');
        if (empty($style)) {
            return null;
        }

        // Parse inline style for color and background-color
        $styles = $this->parseStyleAttribute($style);
        if (!isset($styles['color']) || !isset($styles['background-color'])) {
            return null;
        }

        $fg = $this->parseColor($styles['color']);
        $bg = $this->parseColor($styles['background-color']);

        if (!$fg || !$bg) {
            return null;
        }

        $ratio = $this->calculateRatio($fg, $bg);

        // WCAG AA requires 4.5:1 for normal text (simplified check)
        if ($ratio < 4.5) {
            return new Violation(
                $this->id(),
                sprintf('Low contrast ratio (%.2f:1). Expected at least 4.5:1.', $ratio),
                $node->ownerDocument->saveHTML($node),
                $node->getNodePath(),
                'Darken the text color or lighten the background to increase contrast.'
            );
        }

        return null;
    }

    private function parseStyleAttribute($style)
    {
        $result = [];
        $parts = explode(';', $style);
        foreach ($parts as $part) {
            $prop = explode(':', $part, 2);
            if (count($prop) == 2) {
                $result[trim(strtolower($prop[0]))] = trim($prop[1]);
            }
        }
        return $result;
    }

    private function parseColor($colorStr)
    {
        // Simple hex parser
        if (preg_match('/^#([a-f0-9]{3})$/i', $colorStr, $m)) {
            return [
                hexdec($m[1][0] . $m[1][0]),
                hexdec($m[1][1] . $m[1][1]),
                hexdec($m[1][2] . $m[1][2])
            ];
        }
        if (preg_match('/^#([a-f0-9]{6})$/i', $colorStr, $m)) {
            return [
                hexdec(substr($m[1], 0, 2)),
                hexdec(substr($m[1], 2, 2)),
                hexdec(substr($m[1], 4, 2))
            ];
        }
        // Basic RGB parser
        if (preg_match('/rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i', $colorStr, $m)) {
            return [$m[1], $m[2], $m[3]];
        }
        // TODO: Map HTML color names to RGB if needed
        return null;
    }

    private function calculateRatio($fg, $bg)
    {
        $l1 = $this->relativeLuminance($fg);
        $l2 = $this->relativeLuminance($bg);

        if ($l1 > $l2) {
            return ($l1 + 0.05) / ($l2 + 0.05);
        } else {
            return ($l2 + 0.05) / ($l1 + 0.05);
        }
    }

    private function relativeLuminance($rgb)
    {
        $rs = $rgb[0] / 255;
        $gs = $rgb[1] / 255;
        $bs = $rgb[2] / 255;

        $r = ($rs <= 0.03928) ? $rs / 12.92 : pow(($rs + 0.055) / 1.055, 2.4);
        $g = ($gs <= 0.03928) ? $gs / 12.92 : pow(($gs + 0.055) / 1.055, 2.4);
        $b = ($bs <= 0.03928) ? $bs / 12.92 : pow(($bs + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
