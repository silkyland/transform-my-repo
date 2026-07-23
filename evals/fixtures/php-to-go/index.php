<?php
// PHP entry point — transform-my-repo eval fixture (PARTIAL / missing-equivalent).
//
// Answer key — two headline findings a real census must surface, NOT wave away:
//   1. MISSING EQUIVALENT: this renders views with Twig (twig/twig, see
//      composer.json). Go has no drop-in Twig; the closest is the stdlib
//      html/template with a DIFFERENT syntax and autoescaping model. The
//      census must mark Twig "missing" and list options (rewrite templates in
//      html/template, keep a PHP render sidecar, or use a third-party engine),
//      never claim "surely Go has an equivalent templating library."
//   2. PLATFORM COUPLING: $GLOBALS['request_count'] below relies on PHP's
//      request-per-process model, where globals reset every request. Go runs
//      one long-lived process, so this counter would persist (and race) across
//      requests. The platform-coupling census must flag it with this file:line.

require __DIR__ . '/vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Per-request global state — resets each request under PHP, would NOT under Go.
$GLOBALS['request_count'] = ($GLOBALS['request_count'] ?? 0) + 1;

$twig = new Environment(new FilesystemLoader(__DIR__ . '/templates'));
echo $twig->render('home.twig', [
    'count' => $GLOBALS['request_count'],
]);
