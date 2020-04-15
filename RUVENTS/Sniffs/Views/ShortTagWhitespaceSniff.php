<?php

declare(strict_types = 1);

namespace RUVENTS\Sniffs\Views;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Запрещает использование <?= $some ?> и форсирует <?=$some?>.
 */
class ShortTagWhitespaceSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_OPEN_TAG_WITH_ECHO,
        ];
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

        if ($tokens[$stackPtr + 1]['code'] === T_WHITESPACE && $phpcsFile->addFixableError('Пробелы в коротких тегах не используются', $stackPtr + 1, 'ShortTagWhitespace')) {
            $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
        }

        $closeTag = $phpcsFile->findNext([T_CLOSE_TAG], $stackPtr + 1);

        if ($tokens[$closeTag - 1]['code'] === T_WHITESPACE && $phpcsFile->addFixableError('Пробелы в коротких тегах не используются', $closeTag - 1, 'ShortTagWhitespace')) {
            $phpcsFile->fixer->replaceToken($closeTag - 1, '');
        }
    }
}
