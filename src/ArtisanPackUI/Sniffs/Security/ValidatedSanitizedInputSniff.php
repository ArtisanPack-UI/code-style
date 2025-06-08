<?php

namespace ArtisanPackUI\Sniffs\Security;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that variables are properly sanitized before being used in database operations.
 *
 * This sniff checks that variables are properly sanitized before being added to the database
 * using the artisanpackui/security package's sanitization functions.
 */
class ValidatedSanitizedInputSniff implements Sniff
{
    /**
     * Functions that access superglobals or user input.
     *
     * @var array<string>
     */
    public $inputFunctions = [
        'filter_input',
        'filter_input_array',
        'filter_var',
        'filter_var_array',
        'get_option',
        'get_post_meta',
        'get_user_meta',
        'get_term_meta',
        'get_site_option',
        'get_theme_mod',
        'request',
        'input',
    ];

    /**
     * Superglobals to check for direct access.
     *
     * @var array<string>
     */
    public $superglobals = [
        '$_GET',
        '$_POST',
        '$_REQUEST',
        '$_COOKIE',
        '$_FILES',
        '$_SERVER',
        '$_ENV',
    ];

    /**
     * Database functions that require sanitized input.
     *
     * @var array<string>
     */
    public $dbFunctions = [
        'insert',
        'update',
        'delete',
        'create',
        'save',
        'query',
        'prepare',
        'execute',
        'where',
        'whereIn',
        'whereNotIn',
        'whereNull',
        'whereNotNull',
        'whereBetween',
        'whereNotBetween',
        'whereExists',
        'whereNotExists',
        'whereRaw',
        'DB::insert',
        'DB::update',
        'DB::delete',
        'DB::statement',
        'DB::select',
        'DB::table',
        'DB::raw',
    ];

    /**
     * ArtisanPackUI security package's sanitization functions.
     *
     * @var array<string>
     */
    public $sanitizationFunctions = [
        'sanitize_text',
        'sanitize_textarea',
        'sanitize_html',
        'sanitize_email',
        'sanitize_url',
        'sanitize_number_int',
        'sanitize_number_float',
        'sanitize_key',
        'sanitize_title',
        'sanitize_file_name',
        'sanitize_meta',
        // Add other sanitization functions from artisanpackui/security package
    ];

    /**
     * Functions that validate data and can be considered safe.
     *
     * @var array<string>
     */
    public $validationFunctions = [
        'is_email',
        'is_url',
        'is_ip',
        'is_mac',
        'is_numeric',
        'is_int',
        'is_integer',
        'is_float',
        'is_bool',
        'is_string',
        'is_array',
        'is_object',
        'is_null',
        'validate',
        'validator',
        'filter_var',
        'filter_input',
    ];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_VARIABLE,
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

        // Check for direct superglobal access
        if ($token['code'] === T_VARIABLE && in_array($token['content'], $this->superglobals)) {
            $this->processSuperglobal($phpcsFile, $stackPtr);
            return;
        }

        // Check for input functions
        if ($token['code'] === T_STRING && in_array(strtolower($token['content']), $this->inputFunctions)) {
            $this->processInputFunction($phpcsFile, $stackPtr);
            return;
        }

        // Check for database functions
        if ($token['code'] === T_STRING && in_array(strtolower($token['content']), $this->dbFunctions)) {
            $this->processDbFunction($phpcsFile, $stackPtr);
        }
    }

    /**
     * Process a superglobal variable access.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function processSuperglobal(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $superglobal = $tokens[$stackPtr]['content'];
        
        // Check if the superglobal is being accessed with an index
        $openBracket = $phpcsFile->findNext([T_WHITESPACE], $stackPtr + 1, null, true);
        if ($openBracket === false || $tokens[$openBracket]['code'] !== T_OPEN_SQUARE_BRACKET) {
            return;
        }
        
        // Find the next statement to see if the superglobal is being sanitized
        $semicolon = $phpcsFile->findNext([T_SEMICOLON], $stackPtr + 1);
        if ($semicolon === false) {
            return;
        }
        
        // Check if the superglobal is being sanitized
        $isSanitized = false;
        for ($i = $stackPtr; $i < $semicolon; $i++) {
            if ($tokens[$i]['code'] === T_STRING && in_array(strtolower($tokens[$i]['content']), $this->sanitizationFunctions)) {
                $isSanitized = true;
                break;
            }
        }
        
        if (!$isSanitized) {
            // Check if the superglobal is being assigned to a variable
            $equals = $phpcsFile->findNext([T_EQUAL], $stackPtr + 1, $semicolon);
            if ($equals !== false) {
                // This is an assignment, check if it's being used in a database function later
                $variableName = $phpcsFile->findPrevious([T_VARIABLE], $equals - 1);
                if ($variableName !== false) {
                    $this->checkVariableUsage($phpcsFile, $variableName, $tokens[$variableName]['content']);
                }
            } else {
                // Direct usage without sanitization
                $phpcsFile->addError(
                    'Superglobal %s must be sanitized before use with a function from the artisanpackui/security package.',
                    $stackPtr,
                    'SuperglobalNotSanitized',
                    [$superglobal]
                );
            }
        }
    }

    /**
     * Process an input function call.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function processInputFunction(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $functionName = strtolower($tokens[$stackPtr]['content']);
        
        // Check if this is a function call
        $openParenthesis = $phpcsFile->findNext([T_WHITESPACE], $stackPtr + 1, null, true);
        if ($openParenthesis === false || $tokens[$openParenthesis]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }
        
        // Find the next statement to see if the input is being sanitized
        $semicolon = $phpcsFile->findNext([T_SEMICOLON], $stackPtr + 1);
        if ($semicolon === false) {
            return;
        }
        
        // Check if the input is being sanitized
        $isSanitized = false;
        for ($i = $stackPtr; $i < $semicolon; $i++) {
            if ($tokens[$i]['code'] === T_STRING && in_array(strtolower($tokens[$i]['content']), $this->sanitizationFunctions)) {
                $isSanitized = true;
                break;
            }
        }
        
        if (!$isSanitized) {
            // Check if the input is being assigned to a variable
            $equals = $phpcsFile->findNext([T_EQUAL], $stackPtr + 1, $semicolon);
            if ($equals !== false) {
                // This is an assignment, check if it's being used in a database function later
                $variableName = $phpcsFile->findPrevious([T_VARIABLE], $equals - 1);
                if ($variableName !== false) {
                    $this->checkVariableUsage($phpcsFile, $variableName, $tokens[$variableName]['content']);
                }
            } else {
                // Direct usage without sanitization
                $phpcsFile->addError(
                    'Input from %s must be sanitized before use with a function from the artisanpackui/security package.',
                    $stackPtr,
                    'InputNotSanitized',
                    [$functionName]
                );
            }
        }
    }

    /**
     * Process a database function call.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function processDbFunction(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $functionName = strtolower($tokens[$stackPtr]['content']);
        
        // Check if this is a function call
        $openParenthesis = $phpcsFile->findNext([T_WHITESPACE], $stackPtr + 1, null, true);
        if ($openParenthesis === false || $tokens[$openParenthesis]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }
        
        // Check the arguments of the database function
        $closeParenthesis = $tokens[$openParenthesis]['parenthesis_closer'];
        $argument = $openParenthesis + 1;
        
        while ($argument < $closeParenthesis) {
            if ($tokens[$argument]['code'] === T_VARIABLE) {
                // Check if the variable has been sanitized
                $this->checkVariableSanitization($phpcsFile, $argument, $functionName);
            }
            
            $argument = $phpcsFile->findNext([T_COMMA, T_CLOSE_PARENTHESIS], $argument + 1);
            if ($argument === false || $tokens[$argument]['code'] === T_CLOSE_PARENTHESIS) {
                break;
            }
            
            $argument = $phpcsFile->findNext([T_WHITESPACE], $argument + 1, null, true);
        }
    }

    /**
     * Check if a variable has been sanitized before use.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile    The file being scanned.
     * @param int                         $variablePtr  The position of the variable in the stack.
     * @param string                      $functionName The database function being used.
     *
     * @return void
     */
    private function checkVariableSanitization(File $phpcsFile, $variablePtr, $functionName)
    {
        $tokens = $phpcsFile->getTokens();
        $variableName = $tokens[$variablePtr]['content'];
        
        // Look for sanitization of this variable in the current scope
        $function = $phpcsFile->getCondition($variablePtr, T_FUNCTION);
        if ($function === false) {
            return;
        }
        
        $functionStart = $tokens[$function]['scope_opener'];
        $functionEnd = $tokens[$function]['scope_closer'];
        
        $isSanitized = false;
        for ($i = $functionStart; $i < $variablePtr; $i++) {
            if ($tokens[$i]['code'] === T_VARIABLE && $tokens[$i]['content'] === $variableName) {
                // Found the variable, check if it's being sanitized
                $nextToken = $phpcsFile->findNext([T_WHITESPACE], $i + 1, null, true);
                if ($nextToken !== false && $tokens[$nextToken]['code'] === T_EQUAL) {
                    // This is an assignment, check if it's being sanitized
                    $semicolon = $phpcsFile->findNext([T_SEMICOLON], $nextToken + 1);
                    if ($semicolon !== false) {
                        for ($j = $nextToken; $j < $semicolon; $j++) {
                            if ($tokens[$j]['code'] === T_STRING && 
                                (in_array(strtolower($tokens[$j]['content']), $this->sanitizationFunctions) || 
                                 in_array(strtolower($tokens[$j]['content']), $this->validationFunctions))) {
                                $isSanitized = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        if (!$isSanitized) {
            $phpcsFile->addError(
                'Variable %s must be sanitized before use with %s using a function from the artisanpackui/security package.',
                $variablePtr,
                'VariableNotSanitized',
                [$variableName, $functionName]
            );
        }
    }

    /**
     * Check how a variable is used later in the code.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile   The file being scanned.
     * @param int                         $variablePtr The position of the variable in the stack.
     * @param string                      $variableName The name of the variable.
     *
     * @return void
     */
    private function checkVariableUsage(File $phpcsFile, $variablePtr, $variableName)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Find the function scope
        $function = $phpcsFile->getCondition($variablePtr, T_FUNCTION);
        if ($function === false) {
            return;
        }
        
        $functionEnd = $tokens[$function]['scope_closer'];
        
        // Look for uses of this variable in database functions
        for ($i = $variablePtr + 1; $i < $functionEnd; $i++) {
            if ($tokens[$i]['code'] === T_VARIABLE && $tokens[$i]['content'] === $variableName) {
                // Found the variable, check if it's being used in a database function
                $nextToken = $phpcsFile->findNext([T_WHITESPACE], $i + 1, null, true);
                if ($nextToken !== false) {
                    // Check if it's being passed to a database function
                    $dbFunctionPtr = $phpcsFile->findPrevious([T_STRING], $i - 1, null, false, null, true);
                    if ($dbFunctionPtr !== false && in_array(strtolower($tokens[$dbFunctionPtr]['content']), $this->dbFunctions)) {
                        $phpcsFile->addError(
                            'Variable %s must be sanitized before use with database functions using a function from the artisanpackui/security package.',
                            $i,
                            'UnsanitizedVariableInDb',
                            [$variableName]
                        );
                    }
                }
            }
        }
    }
}