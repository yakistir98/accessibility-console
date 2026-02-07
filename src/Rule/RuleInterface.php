<?php

namespace YakNet\AccessibilityConsole\Rule;

use DOMNode;
use YakNet\AccessibilityConsole\Violation;

interface RuleInterface
{
    public function id(): string;
    public function check(DOMNode $node): ?Violation;
}
