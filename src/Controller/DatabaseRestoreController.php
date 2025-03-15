<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class DatabaseRestoreController extends AbstractController
{
    #[Route('/api/restore', name: 'restore_database', methods: ['POST'])]
    public function restoreDatabase(EntityManagerInterface $entityManager): JsonResponse
    {
        // Database credentials
        $dbHost = '';  // Change to your database host
        $dbUser = 'root';       // Change to your database username
        $dbPassword = '';       // Change to your database password
        $dbName = 'symfony_test'; // Change to your database name
        $backupFile = $this->getParameter('kernel.project_dir') . '/var/backups/backup.sql';

        // Check if backup file exists
        if (!file_exists($backupFile)) {
            return new JsonResponse(['error' => 'Backup file not found'], Response::HTTP_NOT_FOUND);
        }

        // Restore database command
        $mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe'; // Adjust for your system
        $command = "$mysqlPath -h$dbHost -u$dbUser " . ($dbPassword ? "-p$dbPassword " : "") . "$dbName < $backupFile";

        // Execute the command
        $process = Process::fromShellCommandline($command);
        $process->run();

        // Check for errors
        if (!$process->isSuccessful()) {
            return new JsonResponse(['error' => 'Database restore failed', 'details' => $process->getErrorOutput()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'Database restored successfully']);
    }
}
