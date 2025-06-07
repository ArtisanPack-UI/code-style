<?php

namespace ArtisanPackUI\Sniffs\Imports;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that imports are ordered correctly:
 * 1. Classes
 * 2. Functions
 * 3. Constants
 */
class ImportOrderingSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return int
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Find all use statements
        $useStatements = [];
        $ptr = $stackPtr;
        
        while (($ptr = $phpcsFile->findNext(T_USE, $ptr + 1)) !== false) {
            // Skip trait use statements inside classes
            if (empty($tokens[$ptr]['conditions'])) {
                $useType = $this->determineUseType($phpcsFile, $ptr);
                $useStatements[] = [
                    'ptr' => $ptr,
                    'type' => $useType,
                    'line' => $tokens[$ptr]['line'],
                ];
            }
        }
        
        // Check if the use statements are in the correct order
        $lastClassLine = 0;
        $lastFunctionLine = 0;
        $lastConstantLine = 0;
        
        foreach ($useStatements as $useStatement) {
            switch ($useStatement['type']) {
                case 'class':
                    if ($lastFunctionLine > 0 || $lastConstantLine > 0) {
                        $error = 'Class imports must come before function and constant imports';
                        $phpcsFile->addError($error, $useStatement['ptr'], 'ClassImportAfterFunctionOrConstant');
                    }
                    $lastClassLine = $useStatement['line'];
                    break;
                    
                case 'function':
                    if ($lastConstantLine > 0) {
                        $error = 'Function imports must come before constant imports';
                        $phpcsFile->addError($error, $useStatement['ptr'], 'FunctionImportAfterConstant');
                    }
                    $lastFunctionLine = $useStatement['line'];
                    break;
                    
                case 'const':
                    $lastConstantLine = $useStatement['line'];
                    break;
            }
        }
        
        // Return the stack pointer to the end of the file to skip processing the rest of the file
        return ($phpcsFile->numTokens - 1);
    }
    
    /**
     * Determines the type of a use statement (class, function, or constant).
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the use token in the stack.
     *
     * @return string
     */
    private function determineUseType(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Look for the keywords 'function' or 'const' after the 'use' keyword
        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);
        
        if ($nextToken !== false) {
            if ($tokens[$nextToken]['code'] === T_FUNCTION) {
                return 'function';
            } elseif ($tokens[$nextToken]['code'] === T_CONST) {
                return 'const';
            }
        }
        
        // Default to class import
        return 'class';
    }
}