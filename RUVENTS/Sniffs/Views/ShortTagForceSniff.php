<?php

declare(strict_types = 1);

namespace RUVENTS\Sniffs\Views;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Форсирует '<?if():?>' вместо '<?php if (): ?>'
 */
class ShortTagForceSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_OPEN_TAG,
        ];
    }

    /**
     * @inheritDoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Пропускаем уже короткие тэги.
        if (trim($tokens[$stackPtr]['content']) !== '<?php') {
            return;
        }

        // Пропускаем <?php в начале файлов.
        if ($tokens[$stackPtr]['line'] === 1 && $tokens[$stackPtr]['column'] === 1) {
            return;
        }

        $closeTag = $phpcsFile->findNext([T_CLOSE_TAG], $stackPtr);

        // Если открывающий и закрывающие теги находятся в разных строках, то ничего не делаем.
        if ($tokens[$stackPtr]['line'] !== $tokens[$closeTag]['line']) {
            return;
        }

        if ($phpcsFile->addFixableError('Необходимо использовать короткие теги', $stackPtr, 'ShortTagForce')) {
            $phpcsFile->fixer->replaceToken($stackPtr, '<? ');
        }
    }
}
