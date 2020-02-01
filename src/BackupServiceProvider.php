<?php

namespace SherryLo\Laravel\Admin\Backup;

use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (!Backup::boot()) {
            return;
        }
        $backup = Backup::getInstance();
        $this->loadViewsFrom($backup->views(), 'laravel-admin-backup');
        $this->loadTranslationsFrom($backup->trans(), 'laravel-admin-backup');
    }

}
