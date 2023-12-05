<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DbBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database command';

    protected $filesystem;

    protected $formattedTime;

    protected $days = 1;

    protected $connections = [];

    protected $results = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Backing up databases');

        $this->setup();

        if (!count($this->connections)) {
            return;
        }

        foreach ($this->connections as $connection) {
            try {
                $result = $this->backup($connection);

                $this->line('Backup ' . basename($result) . ' stored to ' . $result);
            } catch (ProcessFailedException $e) {
                $this->removeFailedBackup();

                return $this->error($e->getMessage());
            }
        }

        // try {
        //     foreach ($this->results as $result) {
        //         $this->sendToRemote($result);
        //         $this->line('Backup ' . basename($result) . ' sent to slack channel');
        //     }

        //     $this->removeOldBackupFromRemote();
        // } catch (Exception $e) {
        //     return $this->error($e);
        // }

        $this->info('Successfully backed up databases');

        return $this->results;
    }

    protected function setup()
    {
        $this->results = [];

        $time = Carbon::now();

        $this->formattedTime = $time->format('Y_m_d_His');

        $default = config('database.connections.' . config('database.default'));
        // $territory = config('database.connections.territory');

        $key = $default['host'] . $default['port'] . $default['database'] . $default['username'] . $default['password'];

        $connections = [
            $key => $default,
        ];

        // $territoryKey = $territory['host'].$territory['port'].$territory['database'].$territory['username'].$territory['password'];

        // if (! isset($connection[$territoryKey])) {
        //     $connections[$territoryKey] = $territory;
        // }

        $connections = array_values($connections);
        $this->connections = [];

        foreach ($connections as $connection) {
            if (($connection['driver'] ?? '') === 'mysql') {
                $this->connections[] = $connection;
            }
        }

        if (!count($this->connections)) {
            return;
        }

        $this->filesystem->ensureDirectoryExists($this->baseBackupPath());

        $existingFiles = $this->filesystem->files($this->baseBackupPath());

        if (count($existingFiles) > $this->days) {
            foreach ($existingFiles as $i => $file) {
                $this->removeBackup($file->getPathname());

                if ($i === 1) {
                    break;
                }
            }
        }
    }

    protected function backup(array $connection)
    {
        $path = $this->buildPath($connection);

        if (! is_writable($this->baseBackupPath())) {
            $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->buildFilename($connection);
        }

        $command = [
            'mysqldump',
            '-u',
            $connection['username'],
            '-p' . $connection['password'],
            '--databases',
            $connection['database'],
            '-n',
            '--events',
            '--routines',
            '--triggers',
            '>',
            $path,
        ];

        $process = Process::fromShellCommandline(implode(' ', $command));

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->results[] = $path;

        return $path;
    }

    protected function removeFailedBackup()
    {
        foreach ($this->connections as $connection) {
            $path = $this->buildPath($connection);

            $this->removeBackup($path);
        }

        $this->results = [];
    }

    protected function removeBackup($path)
    {
        if ($this->filesystem->exists($path)) {
            $this->filesystem->delete($path);
        }
    }

    protected function buildFilename(array $connection)
    {
        return $this->formattedTime . '__' . $connection['database'] . '.sql';
    }

    protected function buildPath(array $connection)
    {
        $path = $this->buildFilename($connection);

        return $this->baseBackupPath($path);
    }

    protected function baseBackupPath($path = '')
    {
        return storage_path('framework' . DIRECTORY_SEPARATOR . 'backup' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    // protected function sendToRemote($path)
    // {
    //     $filename = basename($path);

    //     $this->slack->driver('backup')->fileUpload([
    //         'channels' => $this->slack->getBackupChannels(),
    //         'filetype' => 'sql',
    //         'filename' => $filename,
    //         'file' => file_get_contents($path),
    //     ]);
    // }

    // protected function removeOldBackupFromRemote()
    // {
    //     list($response) = $this->slack->driver('backup')->getMessages([
    //         'channel' => $this->slack->getBackupChannels(),
    //         'latest' => Carbon::now()->subDay(7)->timestamp,
    //         'limit' => 4
    //     ]);

    //     $responses = [];

    //     $messages = $response['messages'] ?? [];

    //     foreach ($messages as $message) {
    //         if (isset($message['upload']) && $message['upload']) {
    //             list($res) = $this->slack->driver('backup')->removeMessage([
    //                 'channel' => $this->slack->getBackupChannels(),
    //                 'ts' => $message['ts'],
    //             ]);

    //             $responses[] = $res;
    //         }
    //     }
    // }
}
