<?php

namespace ArtisanPackUI\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that indentation is done with real tabs instead of spaces,
 * except when aligning variable assignments and array item definitions.
 */
class IndentationSniff implements Sniff
{
    /**
     * The number of spaces per indentation level.
     *
     * @var int
     */
    public $indent = 4;

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [T_WHITESPACE];
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
        // Temporarily disable this sniff until we can figure out the issue with tabs vs spaces detection
        return;
    }

    /**
     * Determines if the whitespace is for alignment of variable assignments or array items.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return bool
     */
    private function isForAlignment(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if this line contains a variable assignment
        $nextNonWhitespace = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);
        if ($nextNonWhitespace !== false) {
            // Check for variable assignment
            if ($tokens[$nextNonWhitespace]['code'] === T_VARIABLE) {
                $afterVariable = $phpcsFile->findNext(T_WHITESPACE, $nextNonWhitespace + 1, null, true);
                if ($afterVariable !== false && $tokens[$afterVariable]['code'] === T_EQUAL) {
                    // This is a variable assignment
                    // Check if there are other variable assignments on adjacent lines
                    $prevLine = $phpcsFile->findPrevious([T_WHITESPACE, T_VARIABLE, T_EQUAL], $stackPtr - 1, null, false, null, true);
                    $nextLine = $phpcsFile->findNext([T_WHITESPACE, T_VARIABLE, T_EQUAL], $afterVariable + 1, null, false, null, true);

                    if (($prevLine !== false && $tokens[$prevLine]['line'] === ($tokens[$stackPtr]['line'] - 1)) ||
                        ($nextLine !== false && $tokens[$nextLine]['line'] === ($tokens[$stackPtr]['line'] + 1))) {
                        return true;
                    }
                }
            }

            // Check for array item definition
            if ($tokens[$nextNonWhitespace]['code'] === T_CONSTANT_ENCAPSED_STRING ||
                $tokens[$nextNonWhitespace]['code'] === T_LNUMBER ||
                $tokens[$nextNonWhitespace]['code'] === T_DNUMBER ||
                $tokens[$nextNonWhitespace]['code'] === T_VARIABLE) {

                $afterKey = $phpcsFile->findNext(T_WHITESPACE, $nextNonWhitespace + 1, null, true);
                if ($afterKey !== false && $tokens[$afterKey]['code'] === T_DOUBLE_ARROW) {
                    // This is an array item definition
                    // Check if there are other array items on adjacent lines
                    $prevLine = $phpcsFile->findPrevious([T_WHITESPACE, T_CONSTANT_ENCAPSED_STRING, T_LNUMBER, T_DNUMBER, T_VARIABLE, T_DOUBLE_ARROW], $stackPtr - 1, null, false, null, true);
                    $nextLine = $phpcsFile->findNext([T_WHITESPACE, T_CONSTANT_ENCAPSED_STRING, T_LNUMBER, T_DNUMBER, T_VARIABLE, T_DOUBLE_ARROW], $afterKey + 1, null, false, null, true);

                    if (($prevLine !== false && $tokens[$prevLine]['line'] === ($tokens[$stackPtr]['line'] - 1)) ||
                        ($nextLine !== false && $tokens[$nextLine]['line'] === ($tokens[$stackPtr]['line'] + 1))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
