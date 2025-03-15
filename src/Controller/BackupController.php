<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class BackupController extends AbstractController
{
    #[Route('/api/backup', name: 'database_backup', methods: ['GET'])]
    public function backupDatabase(): JsonResponse
    {
        // Define the backup file path
        $backupFile = $this->getParameter('kernel.project_dir') . '/var/backups/backup.sql';

        // Get database credentials from .env
        $dbHost = $_ENV['DATABASE_HOST'] ?? '127.0.0.1';
        $dbUser = $_ENV['DATABASE_USER'] ?? 'root';
        $dbPassword = $_ENV['DATABASE_PASSWORD'] ?? '';
        $dbName = $_ENV['DATABASE_NAME'] ?? 'symfony_test';

        // Create backup directory if not exists
        if (!file_exists(dirname($backupFile))) {
            mkdir(dirname($backupFile), 0777, true);
        }

        // Prepare the mysqldump command
        $mysqlDumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe'; // Adjust based on your MySQL installation
        $command = "$mysqlDumpPath -h $dbHost -u $dbUser " . ($dbPassword ? "-p$dbPassword " : "") . "$dbName > $backupFile";

        //$command = "mysqldump -h $dbHost -u $dbUser " . ($dbPassword ? "-p$dbPassword " : "") . "$dbName > $backupFile";

        // Execute the command
        $process = Process::fromShellCommandline($command);
        $process->run();

        // Check for errors
        if (!$process->isSuccessful()) {
            return new JsonResponse([
                'error' => 'Backup failed',
                'details' => $process->getErrorOutput(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'message' => 'Database backup successful!',
            'backup_file' => $backupFile,
        ]);
    }
}
