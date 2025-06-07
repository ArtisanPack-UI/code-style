<?php

namespace ArtisanPackUI\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that arrays use the short syntax and that associative arrays
 * with multiple items have each item on a new line.
 */
class ArraySyntaxSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_ARRAY,
            T_OPEN_SHORT_ARRAY,
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
        
        // Check for long array syntax
        if ($tokens[$stackPtr]['code'] === T_ARRAY) {
            $error = 'Use short array syntax instead of long array syntax';
            $phpcsFile->addError($error, $stackPtr, 'LongArraySyntax');
            return;
        }
        
        // For short array syntax, check if it's an associative array with multiple items
        if ($tokens[$stackPtr]['code'] === T_OPEN_SHORT_ARRAY) {
            // Find the closing bracket
            $closeBracketPtr = $tokens[$stackPtr]['bracket_closer'];
            
            // Count the number of items and check if it's an associative array
            $isAssociative = false;
            $itemCount = 0;
            $currentPtr = $stackPtr + 1;
            
            while ($currentPtr < $closeBracketPtr) {
                // Skip nested arrays
                if (isset($tokens[$currentPtr]['bracket_opener']) && $tokens[$currentPtr]['bracket_opener'] === $currentPtr) {
                    $currentPtr = $tokens[$currentPtr]['bracket_closer'] + 1;
                    continue;
                }
                
                // Check for double arrow (=>), which indicates an associative array
                if ($tokens[$currentPtr]['code'] === T_DOUBLE_ARROW) {
                    $isAssociative = true;
                }
                
                // Count commas to determine the number of items
                if ($tokens[$currentPtr]['code'] === T_COMMA) {
                    $itemCount++;
                }
                
                $currentPtr++;
            }
            
            // If there's at least one comma, there are at least 2 items
            if ($isAssociative && $itemCount >= 1) {
                // Check if all items are on separate lines
                $currentPtr = $stackPtr + 1;
                $lastCommaLine = -1;
                
                while ($currentPtr < $closeBracketPtr) {
                    if ($tokens[$currentPtr]['code'] === T_COMMA) {
                        // Get the next non-whitespace token
                        $nextNonWhitespace = $phpcsFile->findNext(T_WHITESPACE, $currentPtr + 1, $closeBracketPtr, true);
                        
                        if ($nextNonWhitespace !== false) {
                            // If the next item is on the same line as the comma, it's an error
                            if ($tokens[$currentPtr]['line'] === $tokens[$nextNonWhitespace]['line']) {
                                $error = 'Each item in a multi-item associative array must be on a new line';
                                $phpcsFile->addError($error, $currentPtr, 'AssociativeArrayItemsNotOnNewLines');
                                break;
                            }
                        }
                        
                        $lastCommaLine = $tokens[$currentPtr]['line'];
                    }
                    
                    $currentPtr++;
                }
                
                // Check if the closing bracket is on a new line
                if ($lastCommaLine !== -1 && $lastCommaLine === $tokens[$closeBracketPtr]['line']) {
                    $error = 'The closing bracket of a multi-item associative array must be on a new line';
                    $phpcsFile->addError($error, $closeBracketPtr, 'AssociativeArrayClosingBracketNotOnNewLine');
                }
            }
        }
    }
}