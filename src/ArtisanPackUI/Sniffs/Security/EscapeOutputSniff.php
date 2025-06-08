<?php

namespace ArtisanPackUI\Sniffs\Security;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that any outputted content is properly escaped.
 *
 * This sniff checks that variables being printed to the page are properly escaped
 * using the artisanpackui/security package's escaping functions.
 */
class EscapeOutputSniff implements Sniff
{
    /**
     * Functions that echo output.
     *
     * @var array<string>
     */
    public $outputFunctions = [
        'echo',
        'print',
        'printf',
        'vprintf',
        'wp_die',
        'die',
        'exit',
    ];

    /**
     * Functions whose output is automatically escaped.
     *
     * @var array<string>
     */
    public $autoEscapedFunctions = [];

    /**
     * ArtisanPackUI security package's escaping functions.
     *
     * @var array<string>
     */
    public $escapingFunctions = [
        'escape_html',
        'escape_attr',
        'escape_url',
        'escape_js',
        'escape_textarea',
        'escape_email',
        // Add other escaping functions from artisanpackui/security package
    ];

    /**
     * Functions that are considered safe for unescaped output.
     *
     * @var array<string>
     */
    public $safeFunctions = [
        'is_null',
        'is_array',
        'is_int',
        'is_float',
        'is_bool',
        'is_string',
        'isset',
        'empty',
        'count',
        'sizeof',
        'in_array',
        'array_key_exists',
        'abs',
        'min',
        'max',
        'rand',
        'mt_rand',
        'time',
        'date',
        'mktime',
        'strtotime',
        'htmlspecialchars',
        'htmlentities',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_ECHO,
            T_PRINT,
            T_EXIT,
            T_STRING,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];

        // Handle echo and print statements
        if ($token['code'] === T_ECHO || $token['code'] === T_PRINT || $token['code'] === T_EXIT) {
            $this->processEchoStatement($phpcsFile, $stackPtr);
            return;
        }

        // Handle function calls like printf, vprintf, etc.
        if ($token['code'] === T_STRING) {
            $this->processFunctionCall($phpcsFile, $stackPtr);
        }
    }

    /**
     * Process an echo or print statement.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function processEchoStatement(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $endOfStatement = $phpcsFile->findNext([T_SEMICOLON], $stackPtr);
        
        if ($endOfStatement === false) {
            return;
        }

        // Check each part of the echo statement
        $current = $phpcsFile->findNext([T_WHITESPACE], $stackPtr + 1, $endOfStatement, true);
        
        while ($current !== false && $current < $endOfStatement) {
            if ($this->isVariableOrNonLiteral($tokens[$current])) {
                $this->checkIfEscaped($phpcsFile, $current, $stackPtr, $endOfStatement);
            }
            
            // Move to the next part of the echo statement
            $current = $phpcsFile->findNext([T_WHITESPACE], $current + 1, $endOfStatement, true);
            
            // Skip over any nested expressions
            if ($current !== false && isset($tokens[$current]['parenthesis_opener'])) {
                $current = $tokens[$current]['parenthesis_closer'] + 1;
            }
        }
    }

    /**
     * Process a function call that outputs content.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function processFunctionCall(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $functionName = strtolower($tokens[$stackPtr]['content']);
        
        // Check if this is a function call
        $openParenthesis = $phpcsFile->findNext([T_WHITESPACE], $stackPtr + 1, null, true);
        if ($openParenthesis === false || $tokens[$openParenthesis]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }
        
        // Only process output functions
        if (!in_array($functionName, $this->outputFunctions)) {
            return;
        }
        
        // For functions like printf, check the first argument
        $firstArg = $phpcsFile->findNext([T_WHITESPACE], $openParenthesis + 1, null, true);
        if ($firstArg === false) {
            return;
        }
        
        if ($this->isVariableOrNonLiteral($tokens[$firstArg])) {
            $this->checkIfEscaped($phpcsFile, $firstArg, $stackPtr, $tokens[$openParenthesis]['parenthesis_closer']);
        }
    }

    /**
     * Check if a variable is properly escaped.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile       The file being scanned.
     * @param int                         $variablePtr     The position of the variable in the stack.
     * @param int                         $statementStart  The position where the statement starts.
     * @param int                         $statementEnd    The position where the statement ends.
     *
     * @return void
     */
    private function checkIfEscaped(File $phpcsFile, $variablePtr, $statementStart, $statementEnd)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Check if the variable is wrapped in an escaping function
        $prev = $phpcsFile->findPrevious([T_WHITESPACE, T_OPEN_PARENTHESIS], $variablePtr - 1, $statementStart, true);
        
        if ($prev !== false && $tokens[$prev]['code'] === T_STRING) {
            $functionName = strtolower($tokens[$prev]['content']);
            
            if (in_array($functionName, $this->escapingFunctions) || in_array($functionName, $this->safeFunctions)) {
                return;
            }
        }
        
        // Check if the variable is part of a function call that is safe
        $next = $phpcsFile->findNext([T_WHITESPACE], $variablePtr + 1, $statementEnd, true);
        if ($next !== false && $tokens[$next]['code'] === T_OPEN_PARENTHESIS) {
            $functionName = strtolower($tokens[$variablePtr]['content']);
            if (in_array($functionName, $this->autoEscapedFunctions) || in_array($functionName, $this->safeFunctions)) {
                return;
            }
        }
        
        // If we get here, the variable is not properly escaped
        $phpcsFile->addError(
            'All output should be run through an escaping function from the artisanpackui/security package. Found: %s',
            $variablePtr,
            'OutputNotEscaped',
            [$tokens[$variablePtr]['content']]
        );
    }

    /**
     * Check if a token is a variable or non-literal value that needs escaping.
     *
     * @param array<string, mixed> $token The token to check.
     *
     * @return bool
     */
    private function isVariableOrNonLiteral($token)
    {
        return $token['code'] === T_VARIABLE
            || $token['code'] === T_STRING
            || $token['code'] === T_DOUBLE_QUOTED_STRING
            || $token['code'] === T_OBJECT_OPERATOR
            || $token['code'] === T_OPEN_SQUARE_BRACKET;
    }
}