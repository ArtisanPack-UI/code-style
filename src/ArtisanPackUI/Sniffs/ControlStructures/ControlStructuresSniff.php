<?php

namespace ArtisanPackUI\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that control structures follow the correct format:
 * - Use if : elseif : else format in template/Blade files
 * - Use bracket format in all other files
 * - Same for loops
 */
class ControlStructuresSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_IF,
            T_ELSEIF,
            T_ELSE,
            T_FOR,
            T_FOREACH,
            T_WHILE,
            T_DO,
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
        $isBladeFile = preg_match('/\.blade\.php$/', $fileName);
        
        // For if, elseif, for, foreach, while, do
        if (in_array($tokens[$stackPtr]['code'], [T_IF, T_ELSEIF, T_FOR, T_FOREACH, T_WHILE, T_DO])) {
            // Find the opening brace
            $openBracePtr = $phpcsFile->findNext([T_OPEN_CURLY_BRACKET, T_COLON], $stackPtr + 1, null, false, null, true);
            
            if ($openBracePtr === false) {
                return;
            }
            
            // Check if the correct format is used
            if ($isBladeFile) {
                // Blade files should use colon format
                if ($tokens[$openBracePtr]['code'] === T_OPEN_CURLY_BRACKET) {
                    $error = 'Use colon format for control structures in Blade files';
                    $phpcsFile->addError($error, $openBracePtr, 'BracketFormatInBladeFile');
                }
            } else {
                // Non-Blade files should use bracket format
                if ($tokens[$openBracePtr]['code'] === T_COLON) {
                    $error = 'Use bracket format for control structures in non-Blade files';
                    $phpcsFile->addError($error, $openBracePtr, 'ColonFormatInNonBladeFile');
                }
            }
        }
        
        // For else
        if ($tokens[$stackPtr]['code'] === T_ELSE) {
            // Find what follows the else
            $nextPtr = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);
            
            if ($nextPtr === false) {
                return;
            }
            
            // Check if it's an if (else if) or a brace/colon
            if ($tokens[$nextPtr]['code'] !== T_IF) {
                $openBracePtr = $nextPtr;
                
                // Check if the correct format is used
                if ($isBladeFile) {
                    // Blade files should use colon format
                    if ($tokens[$openBracePtr]['code'] === T_OPEN_CURLY_BRACKET) {
                        $error = 'Use colon format for control structures in Blade files';
                        $phpcsFile->addError($error, $openBracePtr, 'BracketFormatInBladeFile');
                    }
                } else {
                    // Non-Blade files should use bracket format
                    if ($tokens[$openBracePtr]['code'] === T_COLON) {
                        $error = 'Use bracket format for control structures in non-Blade files';
                        $phpcsFile->addError($error, $openBracePtr, 'ColonFormatInNonBladeFile');
                    }
                }
            }
        }
    }
}