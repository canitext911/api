<?php

namespace App\Console\Commands;

use App\Http\Controllers\PsapIndexer\PsapIndexerController;
use Illuminate\Console\Command;

class PsapIndexer extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "psap:index";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Refresh PSAP database";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Running indexer...');

        try {
            $this->info((new PsapIndexerController('storage/app/temp'))->index(false));
        } catch (\Exception $exception) {
            $this->error($exception);
        }

        $this->info('All done! Unicorns.');
    }
}
