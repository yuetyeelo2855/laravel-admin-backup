<?php

namespace SherryLo\Laravel\Admin\Backup;

use Encore\Admin\Extension;
use Illuminate\Routing\Router;
use Spatie\Backup\Commands\ListCommand;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class Backup extends Extension
{

    public $name = "sherrylo/laravel-admin-backup";

    /**
     * @return string
     */
    public function views(): string
    {
        return __DIR__ . "/../resources/views";
    }

    /**
     * @return string
     */
    public function trans(): string
    {
        return __DIR__ . '/../resources/lang';
    }

    public static function getInstance()
    {
        return parent::getInstance();
    }

    public function getExists()
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitor_backups'));

        $listCommand = new ListCommand();

        $rows = $statuses
            ->map(function (BackupDestinationStatus $backupDestinationStatus) use ($listCommand) {
                return $listCommand->convertToRow($backupDestinationStatus);
            })
            ->all();

        $statuses
            ->each(function (BackupDestinationStatus $status, int $index) use (&$rows) {
                $name = $status->backupDestination()->backupName();
                $files = array_map('basename', $status->backupDestination()->disk()->allFiles($name));
                $rows[$index]['files'] = array_slice(array_reverse($files), 0, 30);
            });

        return $rows;
    }

    /**
     * Bootstrap this package.
     *
     * @return bool
     */
    public static function boot()
    {
        parent::boot();
        static::registerRoutes();
        return true;
    }

    /**
     * Register routes for laravel-admin.
     *
     * @return void
     */
    protected static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var Router $router */
            $router->get('backup', 'SherryLo\Laravel\Admin\Backup\BackupController@index')->name('backup-list');
            $router->get('backup/download', 'SherryLo\Laravel\Admin\Backup\BackupController@download')->name('backup-download');
            $router->post('backup/run', 'SherryLo\Laravel\Admin\Backup\BackupController@run')->name('backup-run');
            $router->delete('backup/delete', 'SherryLo\Laravel\Admin\Backup\BackupController@delete')->name('backup-delete');
        });
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public static function import()
    {
        parent::createMenu('Backup', 'backup', 'fa-copy');

        parent::createPermission('Backup', 'ext.backup', 'backup*');
    }
}
