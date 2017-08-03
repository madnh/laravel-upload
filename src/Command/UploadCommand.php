<?php


namespace MaDnh\LaravelUpload\Command;


use Illuminate\Console\Command;

class UploadCommand extends Command
{
    protected $signature = 'upload:clear';
    protected $description = 'Clear uploaded temporary files';

    public function handle()
    {
        $this->banner('Clear Uploaded Temporary Files');

        $keep_from = time() - (int)config('upload.temporary_live_time', 86400);
        $files = $this->getFiles($keep_from);

        $this->info('Found <comment>' . count($files) . '</comment> file(s) before <comment>' . date('Y-m-d H:i:s', $keep_from) . "</comment>\n");

        $deleted = !empty($files) ? $this->deleteFiles($files) : 0;

        $this->info("\n\nComplete, removed <comment>" . $deleted . '</comment> file(s)');
    }

    /**
     * Get files to remove
     * @param int $expireAt While before this timestamp will be remove
     * @return array
     */
    protected function getFiles($expireAt)
    {
        $upload_temp_path = rtrim(config('upload.upload_temp_path', storage_path('app/upload_temp')), '\\/') . DIRECTORY_SEPARATOR;
        $founds = glob($upload_temp_path . '*', GLOB_NOSORT);
        $files = [];

        foreach ($founds as $found) {
            $last_mod = filemtime($found);

            if (!$last_mod || $last_mod < $expireAt) {
                $files[] = $found;
            }
        }

        return $files;
    }

    /**
     * @param $files
     * @return int Deleted count
     */
    protected function deleteFiles($files)
    {
        $deleted = 0;
        $index = 0;
        $chars = ['deleted' => "<fg=blue>\xF0\x9F\x97\xB8</>", 'failed' => "<fg=red>\xF0\x9F\x97\x99</>"];

        foreach ($files as $temp_file) {
            $filename = basename($temp_file);
            $filenamePad = str_pad($filename, 60, ' ', STR_PAD_RIGHT) . str_repeat(' ', 5);
            $fileIndex = ' <info>' . str_pad(++$index . '', '3', ' ', STR_PAD_LEFT) . '.</info>  ';

            try {
                if (!\File::delete($temp_file)) {
                    throw new \Exception('Unknown');
                }

                $this->line($fileIndex . $chars['deleted'] . '  ' . $filenamePad);
                $deleted++;
            } catch (\Exception $e) {
                $this->warn($fileIndex . $chars['failed'] . '  ' . $filenamePad . " <error>Error</error> " . $e->getMessage());
            }
        }

        return $deleted;
    }

    protected function banner($message)
    {
        $messSize = strlen($message);
        $pad = 5;
        $lines = [];

        $lines[] = "\n";
        $lines[] = "\n";
        $lines[] = '<info>' . str_repeat(' ', $pad) . $message . '</info>';
        $lines[] = "\n";
        $lines[] = '<fg=yellow>' . str_repeat('#', $messSize + $pad * 2) . '</>';
        $lines[] = "\n";

        $this->line(implode("\n", $lines));
    }
}