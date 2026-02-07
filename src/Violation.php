<?php

namespace YakNet\AccessibilityConsole;

class Violation
{
    public $ruleId;
    public $message;
    public $snippet;
    public $domPath;
    public $fixSuggestion;
    public $sourceFile;
    public $sourceLine;

    public function __construct(string $ruleId, string $message, string $snippet = '', string $domPath = '', string $fixSuggestion = '')
    {
        $this->ruleId = $ruleId;
        $this->message = $message;
        $this->snippet = $snippet;
        $this->domPath = $domPath;
        $this->fixSuggestion = $fixSuggestion;
    }

    public function setSourceLocation(?string $file, ?int $line)
    {
        $this->sourceFile = $file;
        $this->sourceLine = $line;
    }
}
