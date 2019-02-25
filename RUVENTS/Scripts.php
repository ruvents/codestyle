<?php declare(strict_types = 1);

namespace RUVENTS;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Symfony\Component\Finder\Finder;

class Scripts
{
    private const CFG_PROJECTS_DIR = '/Users/opcode/Sites';

    public static function check(Event $event)
    {
        $finder = (new Finder())
            ->in(self::CFG_PROJECTS_DIR)
            ->depth(1);

        // Удалим, возможно оставшиеся, файлы phpcs.temp.xml по проектам.
        echo "Удаление временных файлов...\n";
        foreach ($finder->name('phpcs.temp.xml') as $path) {
            unlink((string) $path);
            echo " - {$path}\n";
        }

        $projects = [];

        echo "Поиск проектов для тестирования...\n";
        foreach ($finder->name('phpcs.xml') as $phpcsConfigPath) {
            $projectPath = dirname((string) $phpcsConfigPath);
            $projectName = basename($projectPath);
            $composerConfigPath = "{$projectPath}/composer.json";

            // Получаем версию используемого PHP Code Sniffer стандарта RUVENTS
            if (false === is_readable($composerConfigPath)) {
                echo " - {$projectName} проект «{$projectPath}» не содержит файл «composer.json»\n";
                continue;
            }

            $composerConfig = json_decode(file_get_contents($composerConfigPath), true);

            if (false === isset($composerConfig['require-dev'])) {
                echo " - {$projectName} проект «{$projectPath}» не содержит секцию «require-dev»\n";
                continue;
            }

            if (false === isset($composerConfig['require-dev']['ruvents/codestyle'])) {
                echo " - {$projectName} проект «{$projectPath}» не содержит зависимость «ruvents/codestyle»\n";
                continue;
            }

            if (isset($composerConfig['name'])) {
                $projectName = $composerConfig['name'];
            }

            $csVersion = $composerConfig['require-dev']['ruvents/codestyle'];

            echo " - {$projectName}:{$csVersion}\n";

            $projects[] = $projectPath;
        }

        foreach ($projects as $project) {
            $customStandartConfigFile = "{$project}/phpcs.temp.xml";

            // Создаём копию phpcs.xml с изменённым путём расположения файлов стандарта.
            file_put_contents($customStandartConfigFile, str_replace(
                '<config name="installed_paths" value="vendor/ruvents/codestyle/RUVENTS"/>',
                '<config name="installed_paths" value="/Users/opcode/Documents/Projects/ruvents.com/codestyle/RUVENTS"/>',
                file_get_contents("{$project}/phpcs.xml")
            ));

            // Запускаем проверки
            system("cd '{$project}' && ./vendor/bin/phpcs --parallel=32 --standard={$customStandartConfigFile} ./");

            // Удаляем временный файл с настройками phpcs
            unlink($customStandartConfigFile);
        }
    }
}