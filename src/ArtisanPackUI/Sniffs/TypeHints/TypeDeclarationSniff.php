<?php

namespace ArtisanPackUI\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that all functions, parameters, and properties have type declarations
 * unless it's not possible. Also ensures that PHP magic functions are uppercase.
 */
class TypeDeclarationSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_FUNCTION,
            T_VARIABLE,
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
        $token  = $tokens[$stackPtr];

        if ($token['code'] === T_FUNCTION) {
            $this->checkFunctionTypeDeclaration($phpcsFile, $stackPtr);
        } elseif ($token['code'] === T_VARIABLE) {
            $this->checkPropertyTypeDeclaration($phpcsFile, $stackPtr);
        }
    }

    /**
     * Check type declarations for functions and their parameters.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkFunctionTypeDeclaration(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Get the function name
        $namePtr = $phpcsFile->findNext(T_STRING, $stackPtr + 1, null, false, null, true);
        if ($namePtr === false) {
            return;
        }

        $functionName = $tokens[$namePtr]['content'];

        // Check if this is a magic method
        $isMagicMethod = strpos($functionName, '__') === 0;

        // Check if magic method name is uppercase
        if ($isMagicMethod) {
            $magicMethodName = strtoupper($functionName);
            if ($functionName !== $magicMethodName) {
                $error = 'PHP magic function names should be uppercase; expected %s, found %s';
                $data = [$magicMethodName, $functionName];
                $phpcsFile->addError($error, $namePtr, 'MagicMethodNotUppercase', $data);
            }
        }

        // Find the opening and closing parentheses
        $openParenPtr = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $namePtr + 1, null, false, null, true);
        if ($openParenPtr === false || !isset($tokens[$openParenPtr]['parenthesis_closer'])) {
            return;
        }

        $closeParenPtr = $tokens[$openParenPtr]['parenthesis_closer'];

        // Check parameters for type declarations
        $paramPtr = $openParenPtr;
        while (($paramPtr = $phpcsFile->findNext(T_VARIABLE, $paramPtr + 1, $closeParenPtr)) !== false) {
            // Check if there's a type declaration before the parameter
            $typePtr = $phpcsFile->findPrevious([T_WHITESPACE, T_COMMA], $paramPtr - 1, $openParenPtr, true);

            if ($typePtr === false || !in_array($tokens[$typePtr]['code'], [T_STRING, T_ARRAY_HINT, T_CALLABLE, T_SELF, T_PARENT, T_STATIC, T_FALSE, T_NULL, T_TRUE])) {
                $error = 'Parameter %s should have a type declaration';
                $data = [$tokens[$paramPtr]['content']];
                $phpcsFile->addError($error, $paramPtr, 'MissingParameterTypeDeclaration', $data);
            }
        }

        // Check for return type declaration
        $colonPtr = $phpcsFile->findNext(T_COLON, $closeParenPtr + 1, null, false, null, true);

        // Skip checking return type for magic methods that don't typically have return types
        $skipReturnTypeCheck = $isMagicMethod && in_array(strtolower($functionName), [
            '__construct', '__destruct', '__set', '__unset', '__isset', '__clone', '__debuginfo'
        ]);

        if (!$skipReturnTypeCheck && $colonPtr === false) {
            $error = 'Function %s should have a return type declaration';
            $data = [$functionName];
            $phpcsFile->addError($error, $namePtr, 'MissingReturnTypeDeclaration', $data);
        }
    }

    /**
     * Check type declarations for class properties.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkPropertyTypeDeclaration(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Only check class properties, not variables inside functions
        $conditions = $tokens[$stackPtr]['conditions'];
        if (empty($conditions)) {
            return;
        }

        $lastCondition = array_pop($conditions);
        if ($lastCondition !== T_CLASS && $lastCondition !== T_TRAIT && $lastCondition !== T_INTERFACE) {
            return;
        }

        // Check if this is a property declaration (not a use statement or other variable)
        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);
        if ($prevToken === false || !in_array($tokens[$prevToken]['code'], [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR, T_STATIC])) {
            return;
        }

        // Skip check for Laravel Model classes
        if ($this->isLaravelModelClass($phpcsFile, $stackPtr)) {
            return;
        }

        // Check if there's a type declaration before the property
        $typePtr = $phpcsFile->findPrevious([T_WHITESPACE, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR, T_STATIC], $stackPtr - 1, null, true);

        if ($typePtr === false || !in_array($tokens[$typePtr]['code'], [T_STRING, T_ARRAY_HINT, T_CALLABLE, T_SELF, T_PARENT, T_STATIC, T_FALSE, T_NULL, T_TRUE])) {
            $error = 'Property %s should have a type declaration';
            $data = [$tokens[$stackPtr]['content']];
            $phpcsFile->addError($error, $stackPtr, 'MissingPropertyTypeDeclaration', $data);
        }
    }
    /**
     * Check if the class is a Laravel Model class.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return bool
     */
    private function isLaravelModelClass(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Find the class token
        $classPtr = null;
        foreach ($tokens[$stackPtr]['conditions'] as $ptr => $type) {
            if ($type === T_CLASS) {
                $classPtr = $ptr;
                break;
            }
        }

        if ($classPtr === null) {
            return false;
        }

        // Find the extends keyword
        $extendsPtr = $phpcsFile->findNext(T_EXTENDS, $classPtr + 1, null, false, null, true);
        if ($extendsPtr === false) {
            return false;
        }

        // Find the parent class name
        $parentClassPtr = $phpcsFile->findNext(T_STRING, $extendsPtr + 1, null, false, null, true);
        if ($parentClassPtr === false) {
            return false;
        }

        $parentClassName = $tokens[$parentClassPtr]['content'];

        // Check if the class extends Model directly
        if ($parentClassName === 'Model') {
            return true;
        }

        // Check if the class extends a class that ends with "Model"
        if (substr($parentClassName, -5) === 'Model') {
            return true;
        }

        // Check for fully qualified class names
        $namespaceSeparatorPtr = $phpcsFile->findPrevious(T_NS_SEPARATOR, $parentClassPtr - 1, $extendsPtr, false);
        if ($namespaceSeparatorPtr !== false) {
            // This is a fully qualified class name, check if it contains "Model"
            $fullClassName = '';
            $currentPtr = $namespaceSeparatorPtr;

            // Go back to find the beginning of the namespace
            while ($currentPtr !== false) {
                if ($tokens[$currentPtr]['code'] === T_STRING || $tokens[$currentPtr]['code'] === T_NS_SEPARATOR) {
                    $fullClassName = $tokens[$currentPtr]['content'] . $fullClassName;
                    $currentPtr = $currentPtr - 1;
                } else {
                    break;
                }
            }

            $fullClassName .= $parentClassName;

            // Check if it's a Laravel Model
            if (strpos($fullClassName, 'Model') !== false) {
                return true;
            }
        }

        return false;
    }
}
