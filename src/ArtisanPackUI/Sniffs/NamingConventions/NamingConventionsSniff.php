<?php

namespace ArtisanPackUI\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that naming conventions are followed:
 * - Classes: PascalCase
 * - Functions: camelCase
 * - Variables: camelCase
 * - Table columns: snake_case
 */
class NamingConventionsSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
            T_FUNCTION,
            T_VARIABLE,
            T_STRING,
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
        $token = $tokens[$stackPtr];
        
        // Check class, interface, and trait names (PascalCase)
        if ($token['code'] === T_CLASS || $token['code'] === T_INTERFACE || $token['code'] === T_TRAIT) {
            $namePtr = $phpcsFile->findNext(T_STRING, $stackPtr + 1);
            if ($namePtr !== false) {
                $name = $tokens[$namePtr]['content'];
                if (!$this->isPascalCase($name)) {
                    $type = $token['code'] === T_CLASS ? 'Class' : ($token['code'] === T_INTERFACE ? 'Interface' : 'Trait');
                    $error = '%s names must be in PascalCase; found %s';
                    $data = [
                        $type,
                        $name,
                    ];
                    $phpcsFile->addError($error, $namePtr, 'NotPascalCase', $data);
                }
            }
        }
        
        // Check function names (camelCase)
        if ($token['code'] === T_FUNCTION) {
            $namePtr = $phpcsFile->findNext(T_STRING, $stackPtr + 1);
            if ($namePtr !== false) {
                $name = $tokens[$namePtr]['content'];
                // Skip magic methods
                if (strpos($name, '__') !== 0 && !$this->isCamelCase($name)) {
                    $error = 'Function names must be in camelCase; found %s';
                    $data = [$name];
                    $phpcsFile->addError($error, $namePtr, 'NotCamelCase', $data);
                }
            }
        }
        
        // Check variable names (camelCase)
        if ($token['code'] === T_VARIABLE) {
            $name = ltrim($token['content'], '$');
            // Skip special variables like $this
            if ($name !== 'this' && !$this->isCamelCase($name)) {
                $error = 'Variable names must be in camelCase; found %s';
                $data = [$name];
                $phpcsFile->addError($error, $stackPtr, 'NotCamelCase', $data);
            }
        }
        
        // Check for table column references (snake_case)
        // This is a bit tricky as we need to identify table column references
        // For simplicity, we'll check strings that appear to be database column references
        if ($token['code'] === T_STRING) {
            $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
            if ($prevToken !== false && in_array($tokens[$prevToken]['code'], [T_OBJECT_OPERATOR, T_DOUBLE_COLON])) {
                $name = $tokens[$stackPtr]['content'];
                // Check if this looks like a database column reference
                // This is a heuristic and might need adjustment based on your codebase
                if (preg_match('/^(id|name|created_at|updated_at|.*_id)$/', $name) && !$this->isSnakeCase($name)) {
                    $error = 'Table column names must be in snake_case; found %s';
                    $data = [$name];
                    $phpcsFile->addError($error, $stackPtr, 'NotSnakeCase', $data);
                }
            }
        }
    }
    
    /**
     * Checks if a string is in PascalCase.
     *
     * @param string $string The string to check.
     * @return bool
     */
    private function isPascalCase($string)
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $string) === 1;
    }
    
    /**
     * Checks if a string is in camelCase.
     *
     * @param string $string The string to check.
     * @return bool
     */
    private function isCamelCase($string)
    {
        return preg_match('/^[a-z][a-zA-Z0-9]*$/', $string) === 1;
    }
    
    /**
     * Checks if a string is in snake_case.
     *
     * @param string $string The string to check.
     * @return bool
     */
    private function isSnakeCase($string)
    {
        return preg_match('/^[a-z][a-z0-9_]*$/', $string) === 1;
    }
}