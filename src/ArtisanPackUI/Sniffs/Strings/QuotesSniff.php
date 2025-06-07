<?php

namespace ArtisanPackUI\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that single quotes are used if not escaping a variable,
 * and double quotes are used if escaping a variable.
 */
class QuotesSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_CONSTANT_ENCAPSED_STRING,
            T_DOUBLE_QUOTED_STRING,
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
        $content = $tokens[$stackPtr]['content'];
        
        // Check if it's a single-quoted string
        if ($tokens[$stackPtr]['code'] === T_CONSTANT_ENCAPSED_STRING && $content[0] === "'") {
            // Check if it contains a variable that should be escaped
            if (preg_match('/\$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*/', $content)) {
                $error = 'Use double quotes for strings that contain variables to be escaped';
                $phpcsFile->addError($error, $stackPtr, 'SingleQuotesWithVariable');
            }
        }
        
        // Check if it's a double-quoted string
        if ($tokens[$stackPtr]['code'] === T_DOUBLE_QUOTED_STRING) {
            // Check if it doesn't contain a variable that needs to be escaped
            if (!preg_match('/\$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*/', $content) && 
                !preg_match('/\\\\[nrtvef$"]/', $content)) {
                $error = 'Use single quotes for strings that do not contain variables to be escaped';
                $phpcsFile->addError($error, $stackPtr, 'DoubleQuotesWithoutVariable');
            }
        }
    }
}