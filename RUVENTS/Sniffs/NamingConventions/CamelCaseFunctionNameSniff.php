<?php declare(strict_types = 1);

namespace RUVENTS\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Common;

/**
 * Требует именования функци в стиле CamelCase, с исключением для ID-функций.
 */
class CamelCaseFunctionNameSniff implements Sniff
{
    /**
     * Включает режим кастомной обработки ID-функций.
     *
     * @var bool
     */
    public $useCustomId = false;

    /**
     * A list of all PHP magic methods.
     *
     * @var array<string, bool>
     */
    protected $magicMethods = [
        'construct' => true,
        'destruct' => true,
        'call' => true,
        'callstatic' => true,
        'get' => true,
        'set' => true,
        'isset' => true,
        'unset' => true,
        'sleep' => true,
        'wakeup' => true,
        'tostring' => true,
        'set_state' => true,
        'clone' => true,
        'invoke' => true,
        'debuginfo' => true,
    ];

    /**
     * A list of all PHP non-magic methods starting with a double underscore.
     *
     * These come from PHP modules such as SOAPClient.
     *
     * @var array<string, bool>
     */
    protected $methodsDoubleUnderscore = [
        'dorequest' => true,
        'getcookies' => true,
        'getfunctions' => true,
        'getlastrequest' => true,
        'getlastrequestheaders' => true,
        'getlastresponse' => true,
        'getlastresponseheaders' => true,
        'gettypes' => true,
        'setcookie' => true,
        'setlocation' => true,
        'setsoapheaders' => true,
        'soapcall' => true,
    ];

    /**
     * If TRUE, the string must not have two capital letters next to each other.
     *
     * @var bool
     */
    public $strict = true;

    /**
     * {@inheritdoc}
     *
     * @return array|int[]
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $name = $phpcsFile->getDeclarationName($stackPtr);

        if ($name === null) {
            return;
        }

        // Is this a magic function. i.e., it is prefixed with "__".
        if (0 !== preg_match('/^__[^_]/', $name)) {
            $magicPart = strtolower(substr($name, 2));

            if (isset($this->magicMethods[$magicPart])) {
                return;
            }

            $error = 'Function name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
            $phpcsFile->addError($error, $stackPtr, 'FunctionDoubleUnderscore', [$name]);
        }

        // Ignore first underscore in functions prefixed with "_".
        $name = ltrim($name, '_');

        if (false === Common::isCamelCaps($name, false, true, $this->strict)) {
            // Разрешаем названию функции заканчиваться на ID.
            if ($this->useCustomId && Common::isCamelCaps(self::idDown($name), false, true, $this->strict)) {
                return;
            }

            $phpcsFile->addError('Function name "%s" is not in camel caps format', $stackPtr, 'NotCamelCaps', [$name]);
            $phpcsFile->recordMetric($stackPtr, 'CamelCase function name', 'no');
        } else {
            // Не разрешаем названию функции заканчиваться на Id, требуем ID.
            if ($this->useCustomId && preg_match('/Id$/', $name)) {
                $phpcsFile->addError('Необходимо переименовать функцию в ' . self::idUp($name), $stackPtr, 'NotCamelCaps', [$name]);
            }

            $phpcsFile->recordMetric($stackPtr, 'CamelCase method name', 'yes');
        }
    }

    private static function idDown(string $name): string
    {
        return preg_replace('/ID$/', 'Id', $name);
    }

    private static function idUp(string $name): string
    {
        return preg_replace('/Id$/', 'ID', $name);
    }
}
