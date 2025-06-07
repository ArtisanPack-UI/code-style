<?php

namespace ArtisanPackUI\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that class structure follows the coding standards:
 * - Trait Use statements should be at the top of the class
 * - Visibility should be declared for all properties and methods
 * - Each file should only contain one class
 */
class ClassStructureSniff implements Sniff
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
            T_TRAIT,
            T_INTERFACE,
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
        
        // Check if this file contains more than one class/trait/interface
        $this->checkOneClassPerFile($phpcsFile, $stackPtr);
        
        // Check if trait use statements are at the top of the class
        $this->checkTraitUseStatements($phpcsFile, $stackPtr);
        
        // Check if visibility is declared for all properties and methods
        $this->checkVisibilityDeclarations($phpcsFile, $stackPtr);
    }
    
    /**
     * Check if this file contains more than one class/trait/interface.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkOneClassPerFile(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Find all class/trait/interface declarations in the file
        $classTokens = [T_CLASS, T_TRAIT, T_INTERFACE];
        $classCount = 0;
        $ptr = 0;
        
        while (($ptr = $phpcsFile->findNext($classTokens, $ptr + 1)) !== false) {
            // Skip anonymous classes
            $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $ptr - 1, null, true);
            if ($prevToken !== false && $tokens[$prevToken]['code'] === T_NEW) {
                continue;
            }
            
            $classCount++;
            
            // If this is not the first class and not the current class, report an error
            if ($classCount > 1 && $ptr !== $stackPtr) {
                $error = 'Each file should only contain one class, trait, or interface';
                $phpcsFile->addError($error, $ptr, 'MultipleClassesInFile');
            }
        }
    }
    
    /**
     * Check if trait use statements are at the top of the class.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkTraitUseStatements(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Only check classes, not traits or interfaces
        if ($tokens[$stackPtr]['code'] !== T_CLASS) {
            return;
        }
        
        // Find the opening and closing braces of the class
        if (!isset($tokens[$stackPtr]['scope_opener']) || !isset($tokens[$stackPtr]['scope_closer'])) {
            return;
        }
        
        $openBrace = $tokens[$stackPtr]['scope_opener'];
        $closeBrace = $tokens[$stackPtr]['scope_closer'];
        
        // Find all use statements within the class
        $useStatements = [];
        $ptr = $openBrace;
        
        while (($ptr = $phpcsFile->findNext(T_USE, $ptr + 1, $closeBrace)) !== false) {
            // Make sure this is a trait use statement, not a use for importing
            if (!empty($tokens[$ptr]['conditions']) && key($tokens[$ptr]['conditions']) === $stackPtr) {
                $useStatements[] = [
                    'ptr' => $ptr,
                    'line' => $tokens[$ptr]['line'],
                ];
            }
        }
        
        // If there are no use statements, return
        if (empty($useStatements)) {
            return;
        }
        
        // Find the first non-use statement after the opening brace
        $firstNonUsePtr = $openBrace;
        $foundNonUse = false;
        
        while (($firstNonUsePtr = $phpcsFile->findNext([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], $firstNonUsePtr + 1, $closeBrace, true)) !== false) {
            if ($tokens[$firstNonUsePtr]['code'] !== T_USE) {
                $foundNonUse = true;
                break;
            }
            
            // Skip to the semicolon at the end of the use statement
            $semicolonPtr = $phpcsFile->findNext(T_SEMICOLON, $firstNonUsePtr + 1, $closeBrace);
            if ($semicolonPtr !== false) {
                $firstNonUsePtr = $semicolonPtr;
            } else {
                // If no semicolon is found, move to the next token
                $firstNonUsePtr++;
            }
        }
        
        // If there are no non-use statements, return
        if (!$foundNonUse) {
            return;
        }
        
        // Check if any use statements come after non-use statements
        foreach ($useStatements as $useStatement) {
            if ($useStatement['ptr'] > $firstNonUsePtr) {
                $error = 'Trait Use statements should be at the top of the class';
                $phpcsFile->addError($error, $useStatement['ptr'], 'TraitUseNotAtTop');
            }
        }
    }
    
    /**
     * Check if visibility is declared for all properties and methods.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkVisibilityDeclarations(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Find the opening and closing braces of the class/trait/interface
        if (!isset($tokens[$stackPtr]['scope_opener']) || !isset($tokens[$stackPtr]['scope_closer'])) {
            return;
        }
        
        $openBrace = $tokens[$stackPtr]['scope_opener'];
        $closeBrace = $tokens[$stackPtr]['scope_closer'];
        
        // Find all properties and methods within the class
        $ptr = $openBrace;
        
        while (($ptr = $phpcsFile->findNext([T_VARIABLE, T_FUNCTION], $ptr + 1, $closeBrace)) !== false) {
            // Skip if this is not a class member
            if (empty($tokens[$ptr]['conditions']) || key($tokens[$ptr]['conditions']) !== $stackPtr) {
                continue;
            }
            
            // Check if this is a property declaration
            if ($tokens[$ptr]['code'] === T_VARIABLE) {
                // Skip if this is not a property declaration
                $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $ptr - 1, $openBrace, true);
                if ($prevToken === false || !in_array($tokens[$prevToken]['code'], [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR, T_STATIC, T_SEMICOLON, T_OPEN_CURLY_BRACKET])) {
                    continue;
                }
                
                // Check if visibility is declared
                $hasVisibility = false;
                $visibilityPtr = $ptr;
                
                while (($visibilityPtr = $phpcsFile->findPrevious([T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR], $visibilityPtr - 1, $openBrace)) !== false) {
                    // If we hit a semicolon or opening brace, we've gone too far back
                    $semicolonPtr = $phpcsFile->findPrevious([T_SEMICOLON, T_OPEN_CURLY_BRACKET], $visibilityPtr, $openBrace);
                    if ($semicolonPtr !== false && $semicolonPtr > $visibilityPtr) {
                        break;
                    }
                    
                    $hasVisibility = true;
                    break;
                }
                
                if (!$hasVisibility) {
                    $error = 'Visibility should be declared for property %s';
                    $data = [$tokens[$ptr]['content']];
                    $phpcsFile->addError($error, $ptr, 'MissingPropertyVisibility', $data);
                }
            }
            // Check if this is a method declaration
            elseif ($tokens[$ptr]['code'] === T_FUNCTION) {
                // Check if visibility is declared
                $hasVisibility = false;
                $visibilityPtr = $ptr;
                
                while (($visibilityPtr = $phpcsFile->findPrevious([T_PUBLIC, T_PROTECTED, T_PRIVATE], $visibilityPtr - 1, $openBrace)) !== false) {
                    // If we hit a semicolon or opening brace, we've gone too far back
                    $semicolonPtr = $phpcsFile->findPrevious([T_SEMICOLON, T_OPEN_CURLY_BRACKET], $visibilityPtr, $openBrace);
                    if ($semicolonPtr !== false && $semicolonPtr > $visibilityPtr) {
                        break;
                    }
                    
                    $hasVisibility = true;
                    break;
                }
                
                if (!$hasVisibility) {
                    // Get the method name
                    $namePtr = $phpcsFile->findNext(T_STRING, $ptr + 1, $closeBrace);
                    if ($namePtr !== false) {
                        $methodName = $tokens[$namePtr]['content'];
                        $error = 'Visibility should be declared for method %s';
                        $data = [$methodName];
                        $phpcsFile->addError($error, $ptr, 'MissingMethodVisibility', $data);
                    }
                }
            }
            
            // Move to the next token
            $ptr++;
        }
    }
}