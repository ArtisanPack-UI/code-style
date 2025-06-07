<?php

namespace ArtisanPackUI\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that certain disallowed functions are not used.
 *
 * This sniff can be extended to check for proper usage of custom escaping
 * and sanitizing functions instead of generic disallowed ones.
 */
class DisallowedFunctionsSniff implements Sniff
{
	/**
	 * A list of functions that are not allowed.
	 *
	 * @var array<string, string>
	 */
	public $disallowedFunctions = [
		'die'           => 'Use exceptions or a proper exit strategy.',
		'exit'          => 'Use exceptions or a proper exit strategy.',
		'var_dump'      => 'Use `dd()` or a logger for debugging.',
		'print_r'       => 'Use `dd()` or a logger for debugging.',
		'create_function' => 'Anonymous functions should be used instead.',
		// Add your custom checks here.
		// Example: If you have a custom function `my_custom_escape_html()`
		// and you want to ensure it's always used correctly, this sniff
		// can be adapted to check for its presence or absence where expected,
		// or ensure it's not called with problematic arguments.
		// For instance, you might disallow direct `htmlspecialchars` if you have
		// a wrapper.
		// 'htmlspecialchars' => 'Use `YourCompany\Helpers\escape_html()` instead.',
		// 'stripslashes'     => 'Use `YourCompany\Helpers\sanitize_input()` instead.',
	];

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array<int>
	 */
	public function register()
	{
		return [T_STRING]; // Look for function calls (strings that are function names).
	}

	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
	 * @param int                         $stackPtr  The position of the current token in the stack.
	 *
	 * @return void
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();
		$token  = $tokens[$stackPtr];

		// Ensure it's a function call, not just any string.
		if (!isset($tokens[($stackPtr - 1)]) || $tokens[($stackPtr - 1)]['code'] !== T_NEW) {
			$prev = $phpcsFile->findPrevious(\PHP_CodeSniffer\Util\Tokens::$emptyTokens, ($stackPtr - 1), null, true);
			if ($prev !== false && $tokens[$prev]['code'] === T_FUNCTION) {
				// Ignore function declarations.
				return;
			}
		}

		// Find the next non-empty token, which should be an opening parenthesis for a function call.
		$next = $phpcsFile->findNext(\PHP_CodeSniffer\Util\Tokens::$emptyTokens, ($stackPtr + 1), null, true);
		if ($next === false || $tokens[$next]['code'] !== T_OPEN_PARENTHESIS) {
			return; // Not a function call.
		}

		$functionName = strtolower($token['content']);

		if (isset($this->disallowedFunctions[$functionName])) {
			$error = 'The use of %s() is disallowed; %s';
			$data  = [$functionName, $this->disallowedFunctions[$functionName]];
			$phpcsFile->addError($error, $stackPtr, 'Found', $data);
		}
	}
}
