<?php

namespace ArtisanPackUI\Sniffs\Operators;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Ensures that Yoda conditionals are used.
 * 
 * Yoda conditionals place the constant or literal value on the left side of a comparison
 * and the variable on the right side, e.g., `if (true === $condition)` instead of
 * `if ($condition === true)`.
 */
class YodaConditionalsSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return Tokens::$comparisonTokens;
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
        
        // Get the tokens on both sides of the comparison operator
        $leftSideToken = $phpcsFile->findPrevious(
            Tokens::$emptyTokens,
            ($stackPtr - 1),
            null,
            true
        );
        
        $rightSideToken = $phpcsFile->findNext(
            Tokens::$emptyTokens,
            ($stackPtr + 1),
            null,
            true
        );
        
        if ($leftSideToken === false || $rightSideToken === false) {
            return;
        }
        
        // Check if the left side is a variable (which is not Yoda style)
        if ($tokens[$leftSideToken]['code'] === T_VARIABLE) {
            // Check if the right side is a literal or constant
            if (in_array($tokens[$rightSideToken]['code'], [
                T_TRUE, T_FALSE, T_NULL, T_LNUMBER, T_DNUMBER, T_CONSTANT_ENCAPSED_STRING,
                T_STRING, // Could be a constant
            ])) {
                $error = 'Use Yoda conditional style. The literal or constant should be on the left side of the comparison.';
                $phpcsFile->addError($error, $stackPtr, 'NotYoda');
            }
        }
    }
}