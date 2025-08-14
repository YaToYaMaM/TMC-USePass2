<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    public function backupData()
    {
        try {
            // Use config() instead of env() - this reads from cached config
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host', 'localhost');

            \Log::info('Backup started', [
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'db_host' => $dbHost,
                'has_password' => !empty($dbPass)
            ]);

            if (empty($dbName) || empty($dbUser)) {
                return response()->json([
                    'error' => 'Database configuration missing',
                    'db_name' => $dbName,
                    'db_user' => $dbUser
                ], 500);
            }

            $dumpPath = 'C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysqldump.exe';

            if (!file_exists($dumpPath)) {
                return response()->json([
                    'error' => 'mysqldump not found',
                    'path' => $dumpPath
                ], 500);
            }

            $fileName = 'backup-' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = storage_path('app/' . $fileName);

            $command = sprintf(
                '"%s" --user=%s --password="%s" --host=%s %s',
                $dumpPath,
                $dbUser,
                $dbPass,
                $dbHost,
                $dbName
            );

            exec($command . ' > "' . $filePath . '" 2>&1', $output, $resultCode);

            \Log::info('Command executed', [
                'command' => $command,
                'result_code' => $resultCode,
                'output' => $output,
                'file_exists' => file_exists($filePath),
                'file_size' => file_exists($filePath) ? filesize($filePath) : 0
            ]);

            if ($resultCode !== 0) {
                return response()->json([
                    'error' => 'mysqldump failed',
                    'command' => $command,
                    'output' => $output,
                    'result_code' => $resultCode
                ], 500);
            }

            if (!file_exists($filePath) || filesize($filePath) == 0) {
                return response()->json([
                    'error' => 'Backup file not created or empty',
                    'file_path' => $filePath
                ], 500);
            }

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Backup exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => 'Backup failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function restoreData(Request $request)
    {
        try {
            $request->validate([
                'backup_file' => 'required|file|max:102400|mimetypes:text/plain,application/sql'
            ]);

            $uploadedFile = $request->file('backup_file');
            $sqlContent = file_get_contents($uploadedFile->getPathname());

            // Clean the SQL content by removing mysqldump warnings
            $sqlContent = preg_replace('/^mysqldump:.*$/m', '', $sqlContent);
            $sqlContent = trim($sqlContent);

            if (empty($sqlContent)) {
                return response()->json(['error' => 'Uploaded file is empty or only contained warnings'], 500);
            }

            // Database config
            $dbHost = config('database.connections.mysql.host', '127.0.0.1');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            // Use mysql from PATH if not found at hardcoded location
            $mysqlPath = 'mysql';
            if (file_exists('C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql.exe')) {
                $mysqlPath = 'C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql.exe';
            }

            // Create temporary file with cleaned SQL
            $tempFile = tempnam(sys_get_temp_dir(), 'sqlrestore');
            file_put_contents($tempFile, $sqlContent);

            // Build command
            $command = sprintf(
                '"%s" --host=%s --user=%s %s %s < "%s" 2>&1',
                $mysqlPath,
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                empty($dbPass) ? '' : '--password='.escapeshellarg($dbPass),
                escapeshellarg($dbName),
                $tempFile
            );

            \Log::debug("Executing command: ".$command);
            exec($command, $output, $resultCode);

            // Clean up
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            if ($resultCode === 0) {
                return response()->json(['success' => 'Database restored successfully']);
            } else {
                \Log::error("Restore failed", [
                    'output' => $output,
                    'result_code' => $resultCode,
                    'command' => $command
                ]);
                return response()->json([
                    'error' => 'Restore failed',
                    'output' => implode("\n", $output),
                    'result_code' => $resultCode
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error("Restore exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Restore failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
