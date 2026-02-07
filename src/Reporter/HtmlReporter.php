<?php

namespace YakNet\AccessibilityConsole\Reporter;

use YakNet\AccessibilityConsole\Violation;

class HtmlReporter
{
    private $showSuccess = false;

    public function setShowSuccess(bool $show)
    {
        $this->showSuccess = $show;
    }

    public function render(array $violations): string
    {
        if (empty($violations)) {
            if ($this->showSuccess) {
                return "
                <div id='yaknet-a11y-success' onclick='this.style.display=\"none\"' style='position:fixed; bottom:10px; right:10px; background:#28a745; color:white; padding:8px 15px; border-radius:4px; font-family:sans-serif; font-weight:bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2); cursor:pointer; z-index:9999;'>
                    ✅ Accessibility: 0 Errors
                </div>";
            }
            return '';
        }

        $count = count($violations);

        // PHP Fatal Error / Xdebug style inspiration
        $html = "
        <div id='yaknet-a11y-console-toggle' onclick='document.getElementById(\"yaknet-a11y-console\").style.display=\"block\"' style='position:fixed; bottom:10px; right:10px; background:#ff9900; color:white; padding:8px 15px; border-radius:4px; cursor:pointer; font-family:sans-serif; z-index:99999; font-weight:bold; font-size:14px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); border: 1px solid #cc7a00;'>
            ⚠️ Accessibility: {$count} Errors
        </div>

        <div id='yaknet-a11y-console' style='display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:100000; font-family:Verdana, sans-serif;'>
            <div style='width:90%; max-width:1200px; margin:50px auto; background:#fff; box-shadow:0 0 20px rgba(0,0,0,0.5); overflow:hidden; border-radius:4px;'>
                
                <!-- Header imitating PHP Error -->
                <div style='background:#f7f7f7; color:#333; padding:15px 20px; border-bottom:1px solid #ccc; font-size:18px; font-weight:bold; display:flex; justify-content:space-between; align-items:center;'>
                    <span>
                        <span style='background:#d9534f; color:white; padding:2px 6px; border-radius:3px; font-size:14px; vertical-align:middle; margin-right:10px;'>FATAL ERROR</span>
                        Accessibility Violations Detected
                    </span>
                    <button onclick='document.getElementById(\"yaknet-a11y-console\").style.display=\"none\"' style='border:none; background:none; font-size:24px; cursor:pointer; color:#999; line-height:1;'>&times;</button>
                </div>

                <div style='max-height:80vh; overflow-y:auto; padding:0;'>
                    <table style='width:100%; border-collapse:collapse; font-size:14px;'>
                        <thead style='background:#eeeff0; color:#333; text-align:left;'>
                            <tr>
                                <th style='padding:12px 15px; border-bottom:2px solid #ddd; width: 50px;'>#</th>
                                <th style='padding:12px 15px; border-bottom:2px solid #ddd;'>Violation / Message</th>
                                <th style='padding:12px 15px; border-bottom:2px solid #ddd;'>Source File</th>
                            </tr>
                        </thead>
                        <tbody>";

        foreach ($violations as $index => $violation) {
            /** @var Violation $violation */
            $i = $index + 1;

            // Format file path to look like stack trace
            $file = $violation->sourceFile ?? 'Unknown Source';
            // Highlight line number
            $line = $violation->sourceLine ? "<strong>:{$violation->sourceLine}</strong>" : '';

            $html .= "
                            <tr style='border-bottom:1px solid #eee; background:" . ($i % 2 == 0 ? '#f9f9f9' : '#fff') . ";'>
                                <td style='padding:12px 15px; vertical-align:top; color:#999;'>{$i}</td>
                                <td style='padding:12px 15px; vertical-align:top;'>
                                    <div style='color:#cc0000; font-weight:bold; margin-bottom:5px; font-size:15px;'>{$violation->message}</div>
                                    <div style='font-size:12px; color:#555; margin-bottom:8px;'>Rule: <code>{$violation->ruleId}</code></div>
                                    
                                    <div style='background:#f1f1f1; padding:8px; border-left:3px solid #ccc; font-family:monospace; font-size:12px; margin-bottom:8px; color:#333; overflow-x:auto;'>
                                        " . htmlspecialchars(substr($violation->snippet, 0, 300)) . "
                                    </div>
                                    <div style='font-size:13px; color:#222;'>
                                        <span style='color:#28a745; font-weight:bold;'>Fix:</span> {$violation->fixSuggestion}
                                    </div>
                                </td>
                                <td style='padding:12px 15px; vertical-align:top; font-family:monospace; font-size:13px; color:#333;'>
                                    <div style='margin-bottom:5px;'>
                                        {$file}{$line}
                                    </div>
                                </td>
                            </tr>";
        }

        $html .= "
                        </tbody>
                    </table>
                </div>
            </div>
        </div>";

        return $html;
    }

    private function getCodeSnippet(string $filepath, int $line, int $radius = 2): string
    {
        if (!file_exists($filepath) || !is_readable($filepath)) {
            return '';
        }

        $lines = file($filepath);
        if (!$lines)
            return '';

        $start = max(0, $line - $radius - 1);
        $end = min(count($lines) - 1, $line + $radius - 1);

        $snippet = '<pre style="background:#333; color:#f8f8f2; padding:5px; border-radius:3px; overflow-x:auto;">';
        for ($i = $start; $i <= $end; $i++) {
            $currentLineNum = $i + 1;
            $isTarget = ($currentLineNum === $line);
            $style = $isTarget ? 'background:#444; color:#fff; font-weight:bold; display:block;' : '';
            $snippet .= '<span style="' . $style . '">' . str_pad($currentLineNum, 4, ' ', STR_PAD_LEFT) . ' | ' . htmlspecialchars($lines[$i]) . '</span>';
        }
        $snippet .= '</pre>';
        return $snippet;
    }
}
