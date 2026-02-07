<?php

namespace YakNet\AccessibilityConsole\Rules;

use YakNet\AccessibilityConsole\Scanner;

class StandardRules
{
    public static function apply(Scanner $scanner)
    {
        $scanner->addRule(new ImgAltText());
        $scanner->addRule(new FormLabel());
        $scanner->addRule(new HtmlHasLang());
        $scanner->addRule(new EmptyLink());
        $scanner->addRule(new FrameTitle());
        $scanner->addRule(new InputImageAlt());
        $scanner->addRule(new MetaViewport());
        $scanner->addRule(new FieldsetLegend());
        $scanner->addRule(new ButtonName());
        $scanner->addRule(new ColorContrast());
        $scanner->addRule(new HeadingOrder());
        $scanner->addRule(new NoFocusOutline());
    }
}
