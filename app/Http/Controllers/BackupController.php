<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

/**
 * Backup & Restore database (SQL dump).
 * Hanya untuk Super Admin (dibatasi route middleware role:super_admin).
 */
class BackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
    }

    public function index(): View
    {
        File::makeDirectory($this->backupDir, 0755, true, true);

        $files = collect(File::files($this->backupDir))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.sql'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        return view('backup.index', [
            'files' => $files,
            'mysqldumpPath' => config('backup.mysqldump', env('BACKUP_MYSQLDUMP_PATH', 'mysqldump')),
            'driverReady' => $this->binaryExists(config('backup.mysqldump', env('BACKUP_MYSQLDUMP_PATH', 'mysqldump'))),
        ]);
    }

    /**
     * Unduh backup database (mysqldump -> file .sql).
     */
    public function download(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        File::makeDirectory($this->backupDir, 0755, true, true);

        $mysqldump = env('BACKUP_MYSQLDUMP_PATH', 'mysqldump');
        $filename = 'backup-'.date('Ymd-His').'.sql';
        $filepath = $this->backupDir.DIRECTORY_SEPARATOR.$filename;

        $process = new Process([
            $mysqldump,
            '--host='.config('database.connections.mysql.host', '127.0.0.1'),
            '--port='.config('database.connections.mysql.port', '3306'),
            '--user='.config('database.connections.mysql.username', 'root'),
            '--password='.config('database.connections.mysql.password', ''),
            '--single-transaction',
            '--routines',
            '--triggers',
            config('database.connections.mysql.database'),
        ]);
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->with('error', 'Backup gagal. Pastikan binary mysqldump terinstall & ada di PATH. Detail: '
                .trim($process->getErrorOutput()));
        }

        file_put_contents($filepath, $process->getOutput());

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/sql',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Restore database dari file SQL yang diunggah.
     */
    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'mimes:sql,txt', 'max:20480'],
        ], [
            'backup_file.mimes' => 'File backup harus berekstensi .sql.',
        ]);

        $content = File::get($request->file('backup_file')->getRealPath());

        try {
            $statements = $this->splitStatements($content);

            DB::transaction(function () use ($statements) {
                foreach ($statements as $statement) {
                    DB::unprepared($statement);
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Restore gagal: '.$e->getMessage());
        }

        return back()->with('success', 'Restore database berhasil. '.count($this->splitStatements($content)).' perintah SQL dieksekusi.');
    }

    public function destroy(Request $request, string $filename): RedirectResponse
    {
        $safe = basename($filename);
        $path = $this->backupDir.DIRECTORY_SEPARATOR.$safe;

        if (str_ends_with($safe, '.sql') && File::exists($path)) {
            File::delete($path);
        }

        return back()->with('success', 'File backup dihapus.');
    }

    /**
     * Pisahkan isi dump menjadi perintah SQL individual.
     *
     * @return array<int, string>
     */
    private function splitStatements(string $sql): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $sql);
        $clean = [];

        foreach ($lines as $line) {
            $trim = ltrim($line);
            if (str_starts_with($trim, '--') || str_starts_with($trim, '#')) {
                continue;
            }
            $clean[] = $line;
        }

        $sql = implode("\n", $clean);

        $statements = [];
        foreach (explode(';', $sql) as $chunk) {
            $chunk = trim($chunk);
            if ($chunk !== '' && $chunk !== ';') {
                $statements[] = $chunk;
            }
        }

        return $statements;
    }

    private function binaryExists(string $binary): bool
    {
        if (File::exists($binary)) {
            return true;
        }

        // Cari di PATH secara instan (where di Windows, which di OS lain)
        $finder = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';

        try {
            $process = new Process([$finder, $binary]);
            $process->setTimeout(5);
            $process->run();

            return $process->isSuccessful();
        } catch (\Throwable) {
            return false;
        }
    }
}
