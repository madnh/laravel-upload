<?php


namespace MaDnh\LaravelUpload\Command;


use Illuminate\Console\Command;
use MaDnh\LaravelCommandUtil\CommandUtil;

class UploadCommand extends Command
{
    use CommandUtil;

    protected $signature = 'upload:clear';
    protected $description = 'Clear uploaded temporary files';

    public function handle()
    {
        $this->banner('Clear Uploaded Temporary Files');


        $upload_temp_path = config('upload.upload_temp_path', storage_path('app/upload_temp'));
        $upload_temp_path = rtrim($upload_temp_path, '\\/') . DIRECTORY_SEPARATOR;
        $upload_temp_path_length = strlen($upload_temp_path);
        $temp_files = glob($upload_temp_path . '*', GLOB_NOSORT);
        $live_time = (int)config('upload.temporary_live_time', 86400);
        $keep_from = time() - $live_time;
        $delete_total = 0;
        $chars = ['deleted' => "<fg=blue>\xF0\x9F\x97\xB8</>", 'failed' => "<fg=red>\xF0\x9F\x97\x99</>"];

        $this->info('Found <comment>' . count($temp_files) . '</comment> file(s) before <comment>' . date('Y-m-d H:i:s', $keep_from) . '</comment>');

        foreach ($temp_files as $temp_file) {
            $filename = substr($temp_file, $upload_temp_path_length);
            $filenamePad = str_pad($filename, 60, ' ', STR_PAD_RIGHT) . str_repeat(' ', 5);

            try {
                $last_mod = filemtime($temp_file);

                if (!$last_mod || $last_mod < $keep_from) {
                    if (rand(0, 10) < 5) {
                        throw new \Exception('Random delete failed reason (haha)');
                    }
                    if (!\File::delete($temp_file)) {
                        throw new \Exception('Unknown');
                    }

                    $this->line($this->getListIndex() . $chars['deleted'] . '  ' . $filenamePad);
                    $delete_total++;
                }
            } catch (\Exception $e) {
                $this->warn($this->getListIndex() . $chars['failed'] . '  ' . $filenamePad . " <error>Error</error> " . $e->getMessage());
            }
        }

        $this->info("\n\nComplete, removed <comment>" . $delete_total . '</comment> file(s)');
    }
}