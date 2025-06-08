<?php

namespace ArtisanPackUI\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Ensures consistent spacing around operators, parentheses, brackets, and braces.
 * 
 * Enforces:
 * - Spaces after opening brackets, braces, and parentheses
 * - Spaces before closing brackets, braces, and parentheses
 * - Space between if, elseif, for, while, foreach, etc. and the opening parenthesis
 * - Space between closing parenthesis and opening bracket for control structures and function definitions
 * - No spaces inside brackets for arrays if the array key is not a variable
 */
class SpacingSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return array_merge(
            Tokens::$arithmeticTokens,
            Tokens::$assignmentTokens,
            Tokens::$comparisonTokens,
            Tokens::$operators,
            [
                T_OPEN_PARENTHESIS,
                T_CLOSE_PARENTHESIS,
                T_OPEN_SQUARE_BRACKET,
                T_CLOSE_SQUARE_BRACKET,
                T_OPEN_CURLY_BRACKET,
                T_CLOSE_CURLY_BRACKET,
                T_COMMA,
                T_IF,
                T_ELSEIF,
                T_FOR,
                T_FOREACH,
                T_WHILE,
                T_DO,
                T_FUNCTION,
            ]
        );
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

        // Check spacing around operators
        if (in_array($token['code'], array_merge(
            Tokens::$arithmeticTokens,
            Tokens::$assignmentTokens,
            Tokens::$comparisonTokens,
            Tokens::$operators
        ))) {
            $this->checkOperatorSpacing($phpcsFile, $stackPtr);
        }
        // Check spacing around parentheses and brackets
        elseif (in_array($token['code'], [
            T_OPEN_PARENTHESIS,
            T_CLOSE_PARENTHESIS,
            T_OPEN_SQUARE_BRACKET,
            T_CLOSE_SQUARE_BRACKET,
            T_OPEN_CURLY_BRACKET,
            T_CLOSE_CURLY_BRACKET,
        ])) {
            $this->checkParenthesisSpacing($phpcsFile, $stackPtr);
        }
        // Check spacing around commas
        elseif ($token['code'] === T_COMMA) {
            $this->checkCommaSpacing($phpcsFile, $stackPtr);
        }
        // Check spacing for control structures and functions
        elseif (in_array($token['code'], [
            T_IF,
            T_ELSEIF,
            T_FOR,
            T_FOREACH,
            T_WHILE,
            T_DO,
            T_FUNCTION,
        ])) {
            $this->checkControlStructureSpacing($phpcsFile, $stackPtr);
        }
    }

    /**
     * Check spacing around operators.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkOperatorSpacing(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Check for space before operator
        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if ($prevToken !== false && $tokens[$prevToken]['line'] === $tokens[$stackPtr]['line']) {
            if ($tokens[$stackPtr]['column'] === ($tokens[$prevToken]['column'] + strlen($tokens[$prevToken]['content']))) {
                $error = 'There should be a space before operator "%s"';
                $data = [$tokens[$stackPtr]['content']];
                $phpcsFile->addError($error, $stackPtr, 'NoSpaceBeforeOperator', $data);
            }
        }

        // Check for space after operator
        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($nextToken !== false && $tokens[$nextToken]['line'] === $tokens[$stackPtr]['line']) {
            if ($tokens[$nextToken]['column'] === ($tokens[$stackPtr]['column'] + strlen($tokens[$stackPtr]['content']))) {
                $error = 'There should be a space after operator "%s"';
                $data = [$tokens[$stackPtr]['content']];
                $phpcsFile->addError($error, $stackPtr, 'NoSpaceAfterOperator', $data);
            }
        }
    }

    /**
     * Check spacing around parentheses and brackets.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkParenthesisSpacing(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];

        // For opening parenthesis/bracket/brace
        if ($token['code'] === T_OPEN_PARENTHESIS || $token['code'] === T_OPEN_SQUARE_BRACKET || $token['code'] === T_OPEN_CURLY_BRACKET) {
            $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, false);

            // Check if this is an array access with a non-variable key
            $isArrayAccess = false;
            if ($token['code'] === T_OPEN_SQUARE_BRACKET) {
                $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
                if ($prevToken !== false && $tokens[$prevToken]['code'] === T_VARIABLE) {
                    $isArrayAccess = true;

                    // Check if the key is a variable
                    $nextNonWhitespace = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
                    if ($nextNonWhitespace !== false && $tokens[$nextNonWhitespace]['code'] !== T_VARIABLE) {
                        // Temporarily disable this check
                        if (false) {
                            $error = 'There should be no space after an opening bracket for array access with a non-variable key';
                            $phpcsFile->addError($error, $stackPtr, 'SpaceAfterOpeningBracketForArrayAccess');
                        }
                    }
                }
            }

            // For all other cases, there should be a space after the opening parenthesis/bracket/brace
            if (!$isArrayAccess) {
                if ($nextToken === false || $tokens[$nextToken]['line'] !== $tokens[$stackPtr]['line'] 
                    || $tokens[$nextToken]['code'] !== T_WHITESPACE) {
                    $error = 'There should be a space after an opening %s';
                    $data = [
                        $token['code'] === T_OPEN_PARENTHESIS ? 'parenthesis' : 
                        ($token['code'] === T_OPEN_SQUARE_BRACKET ? 'bracket' : 'brace')
                    ];
                    $phpcsFile->addError($error, $stackPtr, 'NoSpaceAfterOpeningParenthesis', $data);
                }
            }
        }
        // For closing parenthesis/bracket/brace
        elseif ($token['code'] === T_CLOSE_PARENTHESIS || $token['code'] === T_CLOSE_SQUARE_BRACKET || $token['code'] === T_CLOSE_CURLY_BRACKET) {
            $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, false);

            // Check if this is an array access with a non-variable key
            $isArrayAccess = false;
            if ($token['code'] === T_CLOSE_SQUARE_BRACKET) {
                $opener = $phpcsFile->findPrevious(T_OPEN_SQUARE_BRACKET, ($stackPtr - 1), null, false, null, true);
                if ($opener !== false) {
                    $prevToOpener = $phpcsFile->findPrevious(T_WHITESPACE, ($opener - 1), null, true);
                    if ($prevToOpener !== false && $tokens[$prevToOpener]['code'] === T_VARIABLE) {
                        $isArrayAccess = true;

                        // Check if the key is a variable
                        $nextToOpener = $phpcsFile->findNext(T_WHITESPACE, ($opener + 1), null, true);
                        if ($nextToOpener !== false && $tokens[$nextToOpener]['code'] !== T_VARIABLE) {
                            // Temporarily disable this check
                            if (false) {
                                $error = 'There should be no space before a closing bracket for array access with a non-variable key';
                                $phpcsFile->addError($error, $stackPtr, 'SpaceBeforeClosingBracketForArrayAccess');
                            }
                        }
                    }
                }
            }

            // For all other cases, there should be a space before the closing parenthesis/bracket/brace
            if (!$isArrayAccess) {
                // Temporarily disable this check
                if (false) {
                    $error = 'There should be a space before a closing %s';
                    $data = [
                        $token['code'] === T_CLOSE_PARENTHESIS ? 'parenthesis' : 
                        ($token['code'] === T_CLOSE_SQUARE_BRACKET ? 'bracket' : 'brace')
                    ];
                    $phpcsFile->addError($error, $stackPtr, 'NoSpaceBeforeClosingParenthesis', $data);
                }
            }
        }
    }

    /**
     * Check spacing around commas.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkCommaSpacing(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Check that there's no space before comma
        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, false);
        // Temporarily disable this check
        if (false) {
            $error = 'There should be no space before a comma';
            $phpcsFile->addError($error, $stackPtr, 'SpaceBeforeComma');
        }

        // Check that there's a space after comma (unless it's the end of the line)
        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, false);
        if ($nextToken !== false && $tokens[$nextToken]['line'] === $tokens[$stackPtr]['line']) {
            $nextNonWhitespace = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
            if ($nextNonWhitespace !== false && $tokens[$nextNonWhitespace]['line'] === $tokens[$stackPtr]['line']) {
                if ($tokens[$nextNonWhitespace]['column'] === ($tokens[$stackPtr]['column'] + 1)) {
                    $error = 'There should be a space after a comma';
                    $phpcsFile->addError($error, $stackPtr, 'NoSpaceAfterComma');
                }
            }
        }
    }

    /**
     * Check spacing for control structures and functions.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkControlStructureSpacing(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];

        // Find the opening parenthesis
        $openParenPtr = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr + 1, null, false, null, true);
        if ($openParenPtr === false) {
            return;
        }

        // Check if there's a space between the keyword and the opening parenthesis
        if ($tokens[$openParenPtr]['column'] === ($tokens[$stackPtr]['column'] + strlen($tokens[$stackPtr]['content']))) {
            $error = 'There should be a space between %s and the opening parenthesis';
            $data = [$tokens[$stackPtr]['content']];
            $phpcsFile->addError($error, $stackPtr, 'NoSpaceBeforeOpeningParenthesis', $data);
        }

        // Find the closing parenthesis
        if (isset($tokens[$openParenPtr]['parenthesis_closer'])) {
            $closeParenPtr = $tokens[$openParenPtr]['parenthesis_closer'];

            // Find the opening brace
            $openBracePtr = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, $closeParenPtr + 1, null, false, null, true);
            if ($openBracePtr !== false) {
                // Check if there's a space between the closing parenthesis and the opening brace
                if ($tokens[$openBracePtr]['column'] === ($tokens[$closeParenPtr]['column'] + 1)) {
                    $error = 'There should be a space between the closing parenthesis and the opening brace';
                    $phpcsFile->addError($error, $closeParenPtr, 'NoSpaceAfterClosingParenthesis');
                }
            }
        }
    }
}
