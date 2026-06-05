<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

/**
 * Regression test for the boost guideline blade files.
 *
 * Ensures every file under resources/boost/guidelines/ compiles via Blade
 * and that the resulting PHP parses cleanly even with short_open_tag enabled.
 * Prevents the class of bug where a literal `<?xml` (or any other `<?` token)
 * inside a code snippet gets executed as a PHP open tag by consumer apps
 * running `boost:update`.
 */
$guidelinesDir = __DIR__ . '/../../resources/boost/guidelines';
$bladeFiles    = glob($guidelinesDir . '/*.blade.php') ?: [];

it('has at least one boost guideline blade file', function () use ($bladeFiles) {
	expect($bladeFiles)->not->toBeEmpty();
});

it('compiles boost guideline blade files without producing a PHP parse error under short_open_tag=On', function (string $bladePath) {
	$cacheDir = sys_get_temp_dir() . '/artisanpack-code-style-boost-tests-' . bin2hex(random_bytes(4));
	mkdir($cacheDir, 0777, true);

	try {
		$compiler = new BladeCompiler(new Filesystem(), $cacheDir);
		$compiled = $compiler->compileString(file_get_contents($bladePath));

		$tmpFile = $cacheDir . '/compiled.php';
		file_put_contents($tmpFile, $compiled);

		$output = [];
		$status = 0;
		// nosemgrep: php.lang.security.command-injection.command-injection
		exec(sprintf('php -d short_open_tag=On -l %s 2>&1', escapeshellarg($tmpFile)), $output, $status);

		expect($status)->toBe(0, sprintf(
			"Compiled blade for %s failed to parse:\n%s\n\nCompiled output:\n%s",
			basename($bladePath),
			implode("\n", $output),
			$compiled
		));
	} finally {
		array_map('unlink', glob($cacheDir . '/*') ?: []);
		@rmdir($cacheDir);
	}
})->with($bladeFiles);
