<?php

namespace YakNet\AccessibilityConsole;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SourceLocator
{
    private $searchPath;
    private $extensions = ['php', 'html', 'twig', 'blade.php'];

    public function __construct(string $searchPath)
    {
        $this->searchPath = $searchPath;
    }

    public function locate(string $snippet): ?array
    {
        if (!is_dir($this->searchPath) || !is_readable($this->searchPath)) {
            return null;
        }

        // 1. Try Exact Match (Normalized)
        $cleanSnippet = trim(preg_replace('/\s+/', ' ', $snippet));
        if ($cleanSnippet === '') {
            return null;
        }

        // Shorten if too long, but keep enough for context
        $searchSnippet = strlen($cleanSnippet) > 150 ? substr($cleanSnippet, 0, 150) : $cleanSnippet;

        try {
            $directory = new RecursiveDirectoryIterator($this->searchPath, \FilesystemIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
        } catch (\UnexpectedValueException $e) {
            // Path might be invalid or permission denied
            return null;
        }

        $bestFuzzyMatch = null;
        $highestScore = 0;

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            try {
                if ($file->isDir()) {
                    continue;
                }

                if (!$this->isValidExtension($file->getFilename())) {
                    continue;
                }

                $result = $this->searchInFile($file->getPathname(), $searchSnippet, $cleanSnippet);
            } catch (\Throwable $e) {
                // Skip files that cannot be read
                continue;
            }

            if ($result['type'] === 'exact') {
                return $result['location'];
            }

            if ($result['type'] === 'fuzzy' && $result['score'] > $highestScore) {
                $highestScore = $result['score'];
                $bestFuzzyMatch = $result['location'];
            }
        }

        // Return best fuzzy match if it meets a threshold
        // Threshold: At least Tag + 1 Attribute (Score ~3) or Tag + Unique (Score ~2)
        if ($bestFuzzyMatch && $highestScore >= 2) {
            return $bestFuzzyMatch;
        }

        return null;
    }

    private function isValidExtension(string $filename): bool
    {
        foreach ($this->extensions as $ext) {
            if (strpos($filename, $ext) !== false) {
                return true;
            }
        }
        return false;
    }

    private function searchInFile(string $filepath, string $searchSnippet, string $fullSnippet): array
    {
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return ['type' => 'none'];
        }

        $parsedSnippet = $this->parseSnippet($fullSnippet);
        $bestLine = 0;
        $maxScore = 0;

        $lineNumber = 0;
        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $cleanLine = trim(preg_replace('/\s+/', ' ', $line));

            // 1. Exact Match Check
            if (strpos($cleanLine, $searchSnippet) !== false) {
                fclose($handle);
                return [
                    'type' => 'exact',
                    'location' => ['file' => $filepath, 'line' => $lineNumber]
                ];
            }

            // 2. Fuzzy Match Check
            $score = $this->calculateFuzzyScore($cleanLine, $parsedSnippet);
            if ($score > $maxScore) {
                $maxScore = $score;
                $bestLine = $lineNumber;
            }
        }

        fclose($handle);

        if ($maxScore > 0) {
            return [
                'type' => 'fuzzy',
                'score' => $maxScore,
                'location' => ['file' => $filepath, 'line' => $bestLine]
            ];
        }

        return ['type' => 'none'];
    }

    private function parseSnippet(string $snippet): array
    {
        preg_match('/<([a-zA-Z0-9]+)/', $snippet, $matches);
        $tagName = $matches[1] ?? null;

        // Parse attributes: name="value"
        preg_match_all('/([a-zA-Z0-9-]+)=["\']([^"\']*)["\']/', $snippet, $matches, PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2];
        }

        return ['tag' => $tagName, 'attributes' => $attributes];
    }

    private function calculateFuzzyScore(string $line, array $parsed): int
    {
        if (!$parsed['tag'] || stripos($line, '<' . $parsed['tag']) === false) {
            return 0;
        }

        // Base score for tag match
        $score = 1;

        // Boost for unique structural tags
        if (in_array(strtolower($parsed['tag']), ['html', 'head', 'body', 'title'])) {
            $score += 1; // Score 2 -> acceptable
        }

        foreach ($parsed['attributes'] as $name => $value) {
            // Check for exact attribute="value" match (allowing single or double quotes)
            if (stripos($line, "$name=\"$value\"") !== false || stripos($line, "$name='$value'") !== false) {
                $score += 2; // Strong match
            }
            // Check for attribute name presence only?
            elseif (stripos($line, $name . '=') !== false) {
                $score += 0.5; // Weak match
            }
        }

        return $score;
    }
}
