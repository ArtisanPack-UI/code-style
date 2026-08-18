<?php

/*
|--------------------------------------------------------------------------
| ArtisanPackUIStandard integration tests
|--------------------------------------------------------------------------
|
| These tests shell out to the installed PHP_CodeSniffer binary and run the
| ArtisanPackUIStandard against a known-dirty sample file. They confirm that
| the standard resolves and that the custom sniffs actually fire, guarding
| against API breaks between PHP_CodeSniffer majors (for example, the removal
| of the T_ARRAY_HINT token in PHPCS 4.0).
|
*/

/**
 * Run the ArtisanPackUIStandard against a target path and capture the result.
 *
 * @return array{json: mixed, stderr: string, exit: int}
 */
function runArtisanPackStandard(string $target): array
{
    $root    = dirname(__DIR__, 2);
    $phpcs   = $root . '/vendor/bin/phpcs';
    $ruleset = $root . '/ArtisanPackUIStandard/ruleset.xml';

    $command = implode(' ', [
        escapeshellarg(PHP_BINARY),
        escapeshellarg($phpcs),
        '--standard=' . escapeshellarg($ruleset),
        '--report=json',
        '--no-colors',
        escapeshellarg($target),
    ]);

    $stdout   = [];
    $exitCode = 0;
    exec($command . ' 2>/dev/null', $stdout, $exitCode);

    $stderr = [];
    exec($command . ' 2>&1 1>/dev/null', $stderr);

    return [
        'json'   => json_decode(implode("\n", $stdout), true),
        'stderr' => implode("\n", $stderr),
        'exit'   => $exitCode,
    ];
}

it('resolves the ArtisanPackUIStandard without a fatal error', function () {
    $result = runArtisanPackStandard(dirname(__DIR__) . '/samples/SampleClass.php');

    // A removed token constant (e.g. T_ARRAY_HINT under PHPCS 4.x) surfaces as an uncaught Error.
    expect($result['stderr'])->not->toContain('Fatal error')
        ->and($result['stderr'])->not->toContain('Undefined constant')
        ->and($result['json'])->toBeArray();
});

it('fires the custom sniffs against a sample file', function () {
    $result = runArtisanPackStandard(dirname(__DIR__) . '/samples/SampleClass.php');

    $sources = [];
    foreach ($result['json']['files'] ?? [] as $file) {
        foreach ($file['messages'] as $message) {
            $sources[] = $message['source'];
        }
    }

    expect($sources)->not->toBeEmpty()
        ->and($sources)->toContain('ArtisanPackUI.TypeHints.TypeDeclaration.MissingReturnTypeDeclaration');

    foreach ($sources as $source) {
        expect($source)->toStartWith('ArtisanPackUI.');
    }
});
