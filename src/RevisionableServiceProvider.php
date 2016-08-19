<?php
/**
 * RevisionableServiceProvider.php
 * @package    revisionable
 * @author     raf <raftalks@gmail.com>
 * @copyright  Copyright (c) 2016, raf
 */

namespace Raftalks\Revisionable;


use Illuminate\Support\ServiceProvider;
use Raftalks\Revisionable\Commands\MigrationCommand;

class RevisionableServiceProvider extends ServiceProvider
{

    protected $commands = [
            MigrationCommand::class
        ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->commands($this->commands);
    }
}