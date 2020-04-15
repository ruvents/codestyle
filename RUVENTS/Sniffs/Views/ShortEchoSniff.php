<?php

declare(strict_types = 1);

namespace RUVENTS\Sniffs\Views;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Форсирует "<?=" вместо "<?php echo"'
 */
class ShortEchoSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * @inheritDoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        // Обрабатываем только шаблоны представлений.
        if (false === strpos($phpcsFile->getFilename(), '/views/')) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $nextToken = $phpcsFile->findNext(Tokens::$emptyTokens, $stackPtr + 1, null, true);

        if ($tokens[$nextToken]['code'] === T_ECHO) {
            $phpcsFile->addError('Необходимо использовать "<?=" вместо "<?php echo"', $stackPtr, 'ShortEchoTag');
        }
    }
}
