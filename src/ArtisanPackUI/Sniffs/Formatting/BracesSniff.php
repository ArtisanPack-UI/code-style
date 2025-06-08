<?php

namespace ArtisanPackUI\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that braces are placed correctly in various code constructs.
 */
class BracesSniff implements Sniff
{
    /**
     * Whether the opening brace should be on the same line as the declaration.
     *
     * @var bool
     */
    public $openingBraceOnSameLine = false;

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
            T_IF,
            T_ELSE,
            T_ELSEIF,
            T_FOR,
            T_FOREACH,
            T_WHILE,
            T_DO,
            T_SWITCH,
            T_TRY,
            T_CATCH,
            T_FINALLY,
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

        // Skip if this token doesn't have braces
        if (!isset($token['scope_opener'])) {
            return;
        }

        $openingBrace = $token['scope_opener'];

        // Check if the opening brace is on the correct line
        if ($this->openingBraceOnSameLine) {
            // Opening brace should be on the same line as the declaration
            if ($tokens[$openingBrace]['line'] !== $tokens[$stackPtr]['line']) {
                // For functions, classes, etc., we need to check the parenthesis closing
                if (in_array($token['code'], [T_FUNCTION, T_CLASS, T_INTERFACE, T_TRAIT])) {
                    if (isset($token['parenthesis_closer'])) {
                        $closingParenthesis = $token['parenthesis_closer'];
                        if ($tokens[$openingBrace]['line'] !== $tokens[$closingParenthesis]['line']) {
                            $error = 'Opening brace should be on the same line as the declaration';
                            $phpcsFile->addError($error, $openingBrace, 'BraceOnWrongLine');
                        }
                    }
                } else {
                    $error = 'Opening brace should be on the same line as the declaration';
                    $phpcsFile->addError($error, $openingBrace, 'BraceOnWrongLine');
                }
            }
        } else {
            // Opening brace should be on the next line after the declaration
            if ($tokens[$openingBrace]['line'] === $tokens[$stackPtr]['line']) {
                $error = 'Opening brace should be on the next line after the declaration';
                $phpcsFile->addError($error, $openingBrace, 'BraceOnWrongLine');
            }
        }

        // Check if there's a space before the opening brace
        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($openingBrace - 1), null, true);
        if ($prevToken !== false && $tokens[$prevToken]['line'] === $tokens[$openingBrace]['line']) {
            if ($tokens[$openingBrace]['column'] === ($tokens[$prevToken]['column'] + strlen($tokens[$prevToken]['content']))) {
                $error = 'There should be a space before the opening brace';
                $phpcsFile->addError($error, $openingBrace, 'NoSpaceBeforeBrace');
            }
        }
    }
}
