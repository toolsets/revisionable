<?php
/**
 * MigrationCommand.php
 * @package    revisionable
 * @author     raf <raftalks@gmail.com>
 * @copyright  Copyright (c) 2016, raf
 */

namespace Raftalks\Revisionable\Commands;

use Illuminate\Console\Command;

class MigrationCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'revisionable:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the revisionable specifications.';


    public function handle()
    {
        $this->info('Ready to create Migration file needed for raftalks/revisionable Package.');
        if ($this->confirm('Do you wish to continue ? [y|N]')) {
            if($this->createMigration())
            {
                $this->info('Migration successfully created!');

                if ($this->confirm('Do you wish to run the migration command ? [y|N]')) {
                    $this->call('migrate');
                }
            }
            else
            {
                $this->error(
                    "Couldn't create migration.\n Check the write permissions".
                    " within the database/migrations directory."
                );
            }
        }
    }


    /**
     * Create the migration.
     *
     * @param array $data Data with table names.
     *
     * @return bool
     */
    protected function createMigration()
    {
        $migrationFile = base_path('/database/migrations') . '/' . date('Y_m_d_His') . '_revisions_table.php';
        
        $output = file_get_contents(__DIR__ . '../stubs/db_table_revisions.php');

        if (!file_exists($migrationFile) && $fs = fopen($migrationFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }
        return false;
    }


}