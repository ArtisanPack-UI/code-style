<?php

namespace ArtisanPackUI\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that opening and closing PHP tags are on separate lines.
 * Also ensures that PHP tags are not used in Blade files.
 */
class PhpTagsSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_OPEN_TAG,
            T_CLOSE_TAG,
        ];
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
        $fileName = $phpcsFile->getFilename();
        
        // Check if this is a Blade file
        if (preg_match('/\.blade\.php$/', $fileName)) {
            $error = 'PHP tags should not be used in Blade files';
            $phpcsFile->addError($error, $stackPtr, 'PhpTagsInBladeFile');
            return;
        }
        
        // Check if opening tag is on its own line
        if ($tokens[$stackPtr]['code'] === T_OPEN_TAG) {
            $nextNonWhitespace = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
            if ($nextNonWhitespace !== false && $tokens[$nextNonWhitespace]['line'] === $tokens[$stackPtr]['line']) {
                $error = 'Opening PHP tag must be on a line by itself';
                $phpcsFile->addError($error, $stackPtr, 'OpeningTagNotOnOwnLine');
            }
        }
        
        // Check if closing tag is on its own line
        if ($tokens[$stackPtr]['code'] === T_CLOSE_TAG) {
            $prevNonWhitespace = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
            if ($prevNonWhitespace !== false && $tokens[$prevNonWhitespace]['line'] === $tokens[$stackPtr]['line']) {
                $error = 'Closing PHP tag must be on a line by itself';
                $phpcsFile->addError($error, $stackPtr, 'ClosingTagNotOnOwnLine');
            }
        }
    }
}