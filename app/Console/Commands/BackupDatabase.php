<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup
        {--path= : Carpeta donde se guardaran los backups}
        {--binary=mysqldump : Ejecutable para generar el dump, por ejemplo mysqldump o mariadb-dump}
        {--keep-days=30 : Dias de backups a conservar. Usa 0 para no borrar nada}';

    protected $description = 'Genera un backup SQL de la base de datos MySQL.';

    public function handle(): int
    {
        if (config('database.default') !== 'mysql') {
            $this->error('El comando db:backup solo esta configurado para conexiones MySQL.');

            return self::FAILURE;
        }

        $connection = config('database.connections.mysql');
        $database = $connection['database'] ?? null;
        $username = $connection['username'] ?? null;
        $password = $connection['password'] ?? null;
        $host = $connection['host'] ?? '127.0.0.1';
        $port = (string) ($connection['port'] ?? 3306);

        if (blank($database) || blank($username)) {
            $this->error('Faltan credenciales de base de datos para generar el backup.');

            return self::FAILURE;
        }

        $backupPath = $this->option('path') ?: storage_path('app/backups/database');

        File::ensureDirectoryExists($backupPath);

        $filename = sprintf(
            '%s-%s.sql',
            $database,
            now()->format('Y-m-d_H-i-s')
        );

        $fullPath = rtrim($backupPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;
        $handle = fopen($fullPath, 'wb');

        if ($handle === false) {
            $this->error("No se pudo crear el archivo de backup: {$fullPath}");

            return self::FAILURE;
        }

        $errors = '';
        $binary = (string) $this->option('binary');

        $process = new Process([
            $binary,
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            $database,
        ]);

        if (filled($password)) {
            $process->setEnv(['MYSQL_PWD' => $password]);
        }

        $process->setTimeout(null);

        try {
            $process->run(function (string $type, string $buffer) use ($handle, &$errors): void {
                if ($type === Process::OUT) {
                    fwrite($handle, $buffer);

                    return;
                }

                $errors .= $buffer;
            });
        } finally {
            fclose($handle);
        }

        if (! $process->isSuccessful()) {
            File::delete($fullPath);
            $this->error('No se pudo generar el backup de la base de datos.');
            $this->warn("Verifica que {$binary} este instalado y disponible para PHP/cron.");

            if (filled($errors)) {
                $this->line(trim($errors));
            }

            return self::FAILURE;
        }

        $this->deleteOldBackups($backupPath);

        $this->info("Backup generado: {$fullPath}");

        return self::SUCCESS;
    }

    private function deleteOldBackups(string $backupPath): void
    {
        $keepDays = (int) $this->option('keep-days');

        if ($keepDays <= 0) {
            return;
        }

        $limit = now()->subDays($keepDays)->timestamp;

        foreach (File::files($backupPath) as $file) {
            if ($file->getExtension() === 'sql' && $file->getMTime() < $limit) {
                File::delete($file->getPathname());
            }
        }
    }
}
