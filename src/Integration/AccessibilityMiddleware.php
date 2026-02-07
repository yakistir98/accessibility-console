<?php

namespace YakNet\AccessibilityConsole\Integration;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use YakNet\AccessibilityConsole\Scanner;
use YakNet\AccessibilityConsole\Rules\StandardRules;
use YakNet\AccessibilityConsole\SourceLocator;
use YakNet\AccessibilityConsole\Reporter\HtmlReporter;

class AccessibilityMiddleware implements MiddlewareInterface
{
    private $scanner;
    private $locator;
    private $reporter;

    public function __construct(string $basePath)
    {
        $this->scanner = new Scanner();
        // Use standard rules for full coverage
        StandardRules::apply($this->scanner);

        $this->locator = new SourceLocator($basePath);
        $this->reporter = new HtmlReporter();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // CHECK 1: Query Parameter Force
        $queryParams = $request->getQueryParams();
        $isForceDebug = isset($queryParams['yaknet_debug']) && $queryParams['yaknet_debug'] == '1';

        // CHECK 2: Environment Variable
        // Default to not running if not local, unless forced.
        // You can set this via $_SERVER['APP_ENV'] or $_ENV['APP_ENV']
        $env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'production';
        $isLocal = in_array($env, ['local', 'dev', 'development', 'test']);

        // Decide: Run if forced OR if environment is local
        if (!$isForceDebug && !$isLocal) {
            return $handler->handle($request);
        }

        $response = $handler->handle($request);

        // Only scan HTML responses
        $contentType = $response->getHeaderLine('Content-Type');
        if (strpos($contentType, 'text/html') === false) {
            return $response;
        }

        $html = (string) $response->getBody();
        $violations = $this->scanner->scan($html);

        if (empty($violations)) {
            return $response;
        }

        // Try to locate sources
        foreach ($violations as $violation) {
            $location = $this->locator->locate($violation->snippet);
            if ($location) {
                $violation->setSourceLocation($location['file'], $location['line']);
            }
        }

        $reportHtml = $this->reporter->render($violations);

        // Inject before </body>
        $newBody = str_replace('</body>', $reportHtml . '</body>', $html);

        $body = $response->getBody();
        $body->rewind();
        $body->write($newBody);

        return $response;
    }
}
