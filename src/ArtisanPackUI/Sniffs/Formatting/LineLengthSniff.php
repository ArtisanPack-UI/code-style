<?php

namespace ArtisanPackUI\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that lines do not exceed a specified length.
 */
class LineLengthSniff implements Sniff
{
    /**
     * The maximum line length.
     *
     * @var int
     */
    public $lineLimit = 120;

    /**
     * The limit for comments.
     *
     * @var int
     */
    public $commentLineLimit = 120;

    /**
     * The number of spaces to replace a tab with.
     *
     * @var int
     */
    public $tabWidth = 4;

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

        // Get the content of the file
        $content = $phpcsFile->getTokensAsString(0, count($tokens));
        $lines = explode("\n", $content);

        // Check each line
        foreach ($lines as $lineNumber => $line) {
            // Skip empty lines
            if (trim($line) === '') {
                continue;
            }

            // Replace tabs with spaces based on configured tab width
            $tabReplacement = str_repeat(' ', $this->tabWidth);
            $lineWithExpandedTabs = str_replace("\t", $tabReplacement, $line);
            $lineLength = mb_strlen($lineWithExpandedTabs);

            // Check if it's a comment line
            $isComment = false;
            if (preg_match('/^\s*\/\//', $line) || preg_match('/^\s*\/\*/', $line) || preg_match('/^\s*\*/', $line)) {
                $isComment = true;
                $limit = $this->commentLineLimit;
            } else {
                $limit = $this->lineLimit;
            }

            // Check if the line exceeds the limit
            if ($lineLength > $limit) {
                $error = 'Line exceeds %s characters; contains %s characters (tabs expanded to ' . $this->tabWidth . ' spaces)';
                $data = [
                    $limit,
                    $lineLength,
                ];
                $phpcsFile->addError($error, $lineNumber + 1, $isComment ? 'CommentExceedsLimit' : 'ExceedsLimit', $data);
            }
        }

        // Return the stack pointer to the end of the file to skip processing the rest of the file
        return ($phpcsFile->numTokens - 1);
    }
}
