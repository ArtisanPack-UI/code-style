<?php

namespace ArtisanPackUI\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures that equal signs are aligned for variable assignments
 * and array item definitions that are right next to each other.
 */
class AlignmentSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [
            T_EQUAL,
            T_DOUBLE_ARROW,
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
        
        // Only process if this is a variable assignment or array item definition
        if ($token['code'] === T_EQUAL) {
            $this->checkVariableAssignmentAlignment($phpcsFile, $stackPtr);
        } elseif ($token['code'] === T_DOUBLE_ARROW) {
            $this->checkArrayItemAlignment($phpcsFile, $stackPtr);
        }
    }
    
    /**
     * Check alignment of variable assignments.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkVariableAssignmentAlignment(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Find the variable being assigned
        $variablePtr = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if ($variablePtr === false || $tokens[$variablePtr]['code'] !== T_VARIABLE) {
            return;
        }
        
        // Find adjacent variable assignments
        $prevAssignment = $this->findAdjacentAssignment($phpcsFile, $stackPtr, -1);
        $nextAssignment = $this->findAdjacentAssignment($phpcsFile, $stackPtr, 1);
        
        if ($prevAssignment !== false || $nextAssignment !== false) {
            // This is part of a group of variable assignments
            // Check if the equal signs are aligned
            $thisEqualColumn = $tokens[$stackPtr]['column'];
            
            if ($prevAssignment !== false) {
                $prevEqualColumn = $tokens[$prevAssignment]['column'];
                if ($prevEqualColumn !== $thisEqualColumn) {
                    $error = 'Equal signs in adjacent variable assignments must be aligned; expected column %d, found column %d';
                    $data = [$prevEqualColumn, $thisEqualColumn];
                    $phpcsFile->addError($error, $stackPtr, 'VariableAssignmentNotAligned', $data);
                }
            }
            
            if ($nextAssignment !== false) {
                $nextEqualColumn = $tokens[$nextAssignment]['column'];
                if ($nextEqualColumn !== $thisEqualColumn && 
                    ($prevAssignment === false || $tokens[$prevAssignment]['column'] === $thisEqualColumn)) {
                    $error = 'Equal signs in adjacent variable assignments must be aligned; expected column %d, found column %d';
                    $data = [$thisEqualColumn, $nextEqualColumn];
                    $phpcsFile->addError($error, $nextAssignment, 'VariableAssignmentNotAligned', $data);
                }
            }
        }
    }
    
    /**
     * Check alignment of array item definitions.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     *
     * @return void
     */
    private function checkArrayItemAlignment(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        
        // Find adjacent array item definitions
        $prevArrow = $this->findAdjacentArrayItem($phpcsFile, $stackPtr, -1);
        $nextArrow = $this->findAdjacentArrayItem($phpcsFile, $stackPtr, 1);
        
        if ($prevArrow !== false || $nextArrow !== false) {
            // This is part of a group of array item definitions
            // Check if the double arrows are aligned
            $thisArrowColumn = $tokens[$stackPtr]['column'];
            
            if ($prevArrow !== false) {
                $prevArrowColumn = $tokens[$prevArrow]['column'];
                if ($prevArrowColumn !== $thisArrowColumn) {
                    $error = 'Double arrows in adjacent array item definitions must be aligned; expected column %d, found column %d';
                    $data = [$prevArrowColumn, $thisArrowColumn];
                    $phpcsFile->addError($error, $stackPtr, 'ArrayItemNotAligned', $data);
                }
            }
            
            if ($nextArrow !== false) {
                $nextArrowColumn = $tokens[$nextArrow]['column'];
                if ($nextArrowColumn !== $thisArrowColumn && 
                    ($prevArrow === false || $tokens[$prevArrow]['column'] === $thisArrowColumn)) {
                    $error = 'Double arrows in adjacent array item definitions must be aligned; expected column %d, found column %d';
                    $data = [$thisArrowColumn, $nextArrowColumn];
                    $phpcsFile->addError($error, $nextArrow, 'ArrayItemNotAligned', $data);
                }
            }
        }
    }
    
    /**
     * Find an adjacent variable assignment.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     * @param int                         $direction The direction to search (-1 for previous, 1 for next).
     *
     * @return int|false
     */
    private function findAdjacentAssignment(File $phpcsFile, $stackPtr, $direction)
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[$stackPtr]['line'];
        
        $targetLine = $line + $direction;
        $targetPtr = $stackPtr;
        
        while (true) {
            if ($direction === -1) {
                $targetPtr = $phpcsFile->findPrevious(T_EQUAL, ($targetPtr - 1), null, false, null, true);
            } else {
                $targetPtr = $phpcsFile->findNext(T_EQUAL, ($targetPtr + 1), null, false, null, true);
            }
            
            if ($targetPtr === false) {
                return false;
            }
            
            if ($tokens[$targetPtr]['line'] === $targetLine) {
                // Check if this is a variable assignment
                $variablePtr = $phpcsFile->findPrevious(T_WHITESPACE, ($targetPtr - 1), null, true);
                if ($variablePtr !== false && $tokens[$variablePtr]['code'] === T_VARIABLE) {
                    return $targetPtr;
                }
            } elseif (($direction === -1 && $tokens[$targetPtr]['line'] < $targetLine - 1) ||
                     ($direction === 1 && $tokens[$targetPtr]['line'] > $targetLine + 1)) {
                // If we've gone beyond the adjacent line, stop searching
                return false;
            }
            
            // Update the target line for the next iteration
            $targetLine = $tokens[$targetPtr]['line'] + $direction;
        }
    }
    
    /**
     * Find an adjacent array item definition.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the stack.
     * @param int                         $direction The direction to search (-1 for previous, 1 for next).
     *
     * @return int|false
     */
    private function findAdjacentArrayItem(File $phpcsFile, $stackPtr, $direction)
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[$stackPtr]['line'];
        
        $targetLine = $line + $direction;
        $targetPtr = $stackPtr;
        
        while (true) {
            if ($direction === -1) {
                $targetPtr = $phpcsFile->findPrevious(T_DOUBLE_ARROW, ($targetPtr - 1), null, false, null, true);
            } else {
                $targetPtr = $phpcsFile->findNext(T_DOUBLE_ARROW, ($targetPtr + 1), null, false, null, true);
            }
            
            if ($targetPtr === false) {
                return false;
            }
            
            if ($tokens[$targetPtr]['line'] === $targetLine) {
                return $targetPtr;
            } elseif (($direction === -1 && $tokens[$targetPtr]['line'] < $targetLine - 1) ||
                     ($direction === 1 && $tokens[$targetPtr]['line'] > $targetLine + 1)) {
                // If we've gone beyond the adjacent line, stop searching
                return false;
            }
            
            // Update the target line for the next iteration
            $targetLine = $tokens[$targetPtr]['line'] + $direction;
        }
    }
}