<?php
/**
 * Sniff for detecting deprecated ArtisanPack UI component syntax in Blade files.
 *
 * @package    ArtisanPack-Code-Style
 * @since      1.0.0
 */

namespace ArtisanPack\Sniffs\Blade;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects usage of `<x-artisanpack-artisanpack-..>` and flags it as a warning.
 *
 * @since 1.0.0
 */
class DeprecatedComponentSyntaxSniff implements Sniff
{
	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * We are interested in inline HTML, which is where Blade tags reside.
	 *
	 * @return array
	 */
	public function register(): array
	{
		return [ T_INLINE_HTML ];
	}

	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param int                         $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ): void
	{
		$tokens  = $phpcsFile->getTokens();
		$content = $tokens[ $stackPtr ]['content'];

		$pattern = '/<x-artisanpack-artisanpack-([\w-]+)/';

		if ( preg_match( $pattern, $content, $matches ) ) {
			$component = $matches[0];
			$newName   = str_replace( 'artisanpack-artisanpack-', 'artisanpack-', $component );

			$warning = 'Usage of the deprecated component syntax %s was found. Please update to %s.';
			$data    = [
				$component,
				$newName,
			];

			$phpcsFile->addWarning( $warning, $stackPtr, 'Found', $data );
		}
	}
}