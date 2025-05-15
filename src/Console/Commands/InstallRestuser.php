<?php

namespace Autonic\Restuser\Console\Commands;

use Illuminate\Console\Command;

class InstallRestuser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install-restuser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Restuser package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->info('Installing Restuser package...');

        $routeFiles = [
            'web.php',
            'api.php',
            'auth.php',
        ];

        if (!$this->confirm("Your default route and view files will be deleted.\nMake sure you have made backups of them if needed.\nDo you want to continue with the installation?", true)) {
            $this->warn('Installation aborted.');
            return 1;
        }

        // reset route files
        foreach($routeFiles as $file) {
            $sourcePath = __DIR__ . '/../../../routes/fresh-install/' . $file;
            $destinationPath = base_path('routes/' . $file);

            if (file_exists($sourcePath)) {

                if (file_exists($destinationPath)) {
                    if (unlink($destinationPath)) {
                        $this->line("Deleted: $destinationPath");
                    } else {
                        $this->error("Failed to delete: $destinationPath");
                    }
                }
                if (copy($sourcePath, $destinationPath)) {
                    $this->info("Copied: $sourcePath to $destinationPath");
                } else {
                    $this->error("Failed to copy: $sourcePath to $destinationPath");
                }

            } else {
                $this->error("Source file $sourcePath does not exist.");
                return 1;
            }

        }

        // reset view files
        $viewFiles = [
            'login.blade.php',
            'register.blade.php',
            'forgot-password.blade.php',
            'reset-password.blade.php',
            'verify-email.blade.php',
            'confirm-password.blade.php',
        ];

        $viewPath = resource_path('views/livewire/pages/auth/');
        foreach($viewFiles as $file) {
            $destinationPath = $viewPath . $file;
            if (file_exists($destinationPath) && unlink($destinationPath)) $this->line("Deleted: $destinationPath");
            else $this->error("Failed to delete: $destinationPath");
        }

        // delete the pages/auth folder
        $viewPath = resource_path('views/livewire/pages/');
        if (is_dir($viewPath) && rmdir($viewPath)) $this->line("Deleted folder: $viewPath");
        else $this->error("Failed to delete folder: $viewPath");

        $viewFiles = [
            'navigation.blade.php',
        ];

        $viewPath = resource_path('views/livewire/welcome/');
        foreach($viewFiles as $file) {
            $destinationPath = $viewPath . $file;
            if (file_exists($destinationPath) && unlink($destinationPath)) $this->line("Deleted: $destinationPath");
            else $this->error("Failed to delete: $destinationPath");
        }

        // copy navigation files
        $viewFiles = [
            'navigation-desktop.blade.php',
            'navigation-mobile.blade.php',
        ];

        $sourcePath = __DIR__ . '/../../../resources/views/livewire/layout/';
        $destinationPath = resource_path('views/components/');
        foreach($viewFiles as $file) {
            $fromPath = $sourcePath . $file;
            $toPath = $destinationPath . $file;
            if (file_exists($fromPath) && copy($fromPath, $toPath)) $this->line("Copied: $fromPath to $toPath");
            else $this->error("Failed to copy: $fromPath to $toPath");
        }

        // delete old naviation file
        $path = resource_path('views/livewire/layout/navigation.blade.php');
        if (file_exists($path) && unlink($path)) $this->info("Deleted: $path");
        else $this->error("Failed to delete: $path");

        // delete the livewire/layout folder
        $viewPath = resource_path('views/livewire/layout/');
        if (is_dir($viewPath) && rmdir($viewPath)) $this->line("Deleted folder: $viewPath");
        else $this->error("Failed to delete folder: $viewPath");

        // delete the pages/auth folder
        $viewPath = resource_path('views/livewire/welcome');
        if (is_dir($viewPath) && rmdir($viewPath)) $this->line("Deleted folder: $viewPath");
        $this->error("Failed to delete folder: $viewPath");

        $viewFiles = [
            'overview.blade.php',
            'profile.blade.php',
            'welcome.blade.php',
        ];

        $viewPath = resource_path('views/');
        foreach($viewFiles as $file) {
            $destinationPath = $viewPath . $file;
            if (file_exists($destinationPath) && unlink($destinationPath)) $this->line("Deleted: $destinationPath");
            else $this->error("Failed to delete: $destinationPath");
        }

        // copy overview desktop livewire component
        $toPath = app_path('Http/Livewire/OverviewDesktop.php');
        $fromPath = __DIR__ . '/../../../resources/livewire-components/OverviewDesktop.php';
        if (file_exists($fromPath) && copy($fromPath, $toPath)) $this->info("Copied: $fromPath to $toPath");
        else $this->error("Failed to copy: $fromPath to $toPath");

        // copy overview desktop view
        $toPath = resource_path('views/livewire/overview-desktop.blade.php');
        $fromPath = __DIR__ . '/../../../resources/views/livewire/overview-desktop.blade.php';
        if (copy($fromPath, $toPath)) $this->info("Copied: $fromPath to $toPath");
        else $this->error("Failed to copy: $fromPath to $toPath");

        // copy overview mobile livewire component
        $toPath = app_path('Http/Livewire/OverviewMobile.php');
        $fromPath = __DIR__ . '/../../../resources/livewire-components/OverviewMobile.php';
        if (file_exists($fromPath) && copy($fromPath, $toPath)) $this->info("Copied: $fromPath to $toPath");
        else $this->error("Failed to copy: $fromPath to $toPath");

        // copy overview mobile view
        $toPath = resource_path('views/livewire/overview-mobile.blade.php');
        $fromPath = __DIR__ . '/../../../resources/views/livewire/overview-mobile.blade.php';
        if (copy($fromPath, $toPath)) $this->info("Copied: $fromPath to $toPath");
        else $this->error("Failed to copy: $fromPath to $toPath");

        // copy app layout
        $toPath = resource_path('views/layouts/app.blade.php');
        $fromPath = __DIR__ . '/../../../resources/views/layouts/app.blade.php';
        if (copy($fromPath, $toPath)) $this->info("Copied: $fromPath to $toPath");
        else $this->error("Failed to copy: $fromPath to $toPath");

        // copy guest layout
        $toPath = resource_path('views/layouts/guest.blade.php');
        $fromPath = __DIR__ . '/../../../resources/views/layouts/guest.blade.php';
        if (copy($fromPath, $toPath)) $this->info("Copied: $fromPath to $toPath");
        else $this->error("Failed to copy: $fromPath to $toPath");


        // remove misc files
        $path = app_path('Livewire/Actions/Logout.php');
        if (file_exists($path) && unlink($path)) $this->info("Deleted: $path");
        else $this->error("Failed to delete: $path");

        $path = app_path('Livewire/Actions/');
        if (is_dir($path) && rmdir($path)) $this->info("Deleted: $path");
        else $this->error("Failed to delete: $path");

        $path = app_path('Livewire/Forms/');
        if (is_dir($path) && rmdir($path)) $this->info("Deleted: $path");
        else $this->error("Failed to delete: $path");

        $path = app_path('Http/Controllers/Auth/VerifyEmailController.php');
        if (file_exists($path) && unlink($path)) $this->info("Deleted: $path");
        else $this->error("Failed to delete: $path");

        $path = app_path('Http/Controllers/Auth/');
        if (is_dir($path) && rmdir($path)) $this->info("Deleted: $path");
        else $this->error("Failed to delete: $path");

        $path = resource_path('views/auth/');
        if (is_dir($path) && rmdir($path)) $this->info("Deleted: $path");
        else $this->error("Failed to delete: $path");

        // copy config file
        $copyFrom = __DIR__ . '/../../../config/restuser.php';
        $copyTo = base_path('config/restuser.php');
        if (copy($copyFrom, $copyTo)) $this->info("Copied: $copyFrom to $copyTo");
        else $this->error("Failed to copy: $copyFrom to $copyTo");

        // clear config
        $this->call('config:clear');

        // clear cache
        $this->call('cache:clear');

        // clear view cache
        $this->call('view:clear');

        // cache the routes
        $this->call('route:cache');

        $this->line("");
        $this->info('Restuser package installed successfully.');
        $this->line("");

        return 0;

    }

}
