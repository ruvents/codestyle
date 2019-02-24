<?php

namespace RUVENTS\Sniffs\CodeAnalysis;


use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class EmptyStatementSniff extends \PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\EmptyStatementSniff
{
    /**
     * Позволяет отключить поиск пустых catch блоков, что сделает проверку рабочей для старых проектов.
     *
     * @var bool
     */
    public $ignoreEmptyCatchStatements = false;

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $tokens = [
            T_TRY,
            T_FINALLY,
            T_DO,
            T_ELSE,
            T_ELSEIF,
            T_FOR,
            T_FOREACH,
            T_IF,
            T_SWITCH,
            T_WHILE,
        ];

        if ($this->ignoreEmptyCatchStatements === false) {
            $tokens[] = T_CATCH;
        }

        return $tokens;
    }
}