<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RebuildStorageLink extends Command
{
    protected $signature = 'storage:rebuild';
    protected $description = 'Remove and recreate the storage symlink';

    public function handle()
    {
        // Remove existing symlink if it exists
        if (file_exists(public_path('storage'))) {
            try {
                if (is_link(public_path('storage'))) {
                    unlink(public_path('storage'));
                } else {
                    File::deleteDirectory(public_path('storage'));
                }
                $this->info('Removed existing storage link/directory');
            } catch (\Exception $e) {
                $this->error('Failed to remove storage link: '.$e->getMessage());
                return 1;
            }
        }

        // Create new symlink
        try {
            $this->call('storage:link');
            $this->info('Storage symlink created successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create storage symlink: '.$e->getMessage());
            return 1;
        }
    }
}