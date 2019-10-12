<?php

namespace Thinktomorrow\AssetLibrary\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;
use Thinktomorrow\AssetLibrary\Models\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Models\Application\DeleteAsset;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnreachableUrl;

class ImageToAssetMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assetlibrary:migrate-image
                                    {table}
                                    {urlcolumn}
                                    {linkedmodel}
                                    {idcolumn=id}
                                    {ordercolumn?}
                                    {localecolumn?}
                                    {--force}
                                    {--reset}
                                    {--dry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import images from imageurl on a model to assets managed by assetlibrary.';

    private $table;
    private $urlcolumn;
    private $linkedmodel;
    private $idcolumn;
    private $ordercolumn;
    private $localecolumn;

    private $nomodel     = 0;
    private $unreachable = 0;
    private $files       = 0;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \ini_set('memory_limit', '256M');

        $this->setArguments();

        $results        = $this->getResultsFromDatabase();
        $bar            = $this->output->createProgressBar(count($results));
        $orderedResults = $this->mapResults($results);
        $isDry          = $this->option('dry');

        $this->handleResetFlag($orderedResults);

        $this->info("\n".'Migrating images.');

        $orderedResults->each(function($result) use($bar, $isDry){
            $this->processLines($result, $bar, $isDry);
        });

        $bar->finish();

        $this->info('Migrating done.');
        $this->info('Migrated '.$this->files.' files.');
        $this->info('Couldn\'t reach '.$this->unreachable.' files.');
        $this->info('Couldn\'t find '.$this->nomodel.' model(s).');
    }

    private function processLines($result, $bar, $isDry)
    {
        foreach ($result['images'] as $line) {
            $bar->advance();

            if(! $line) {
                $this->unreachable++;
                continue;
            }

            if($isDry){
                $this->files++;
                continue;
            }

            try {
                $asset = AssetUploader::uploadFromUrl(public_path($line));
            } catch (UnreachableUrl $ex) {
                // increment the amount of unreachable files counter
                $this->unreachable++;

                continue;
            }

            app(AddAsset::class)->setOrder($result['order'])->add($result['model'], $asset);

            if ($this->option('force')) {
                unlink(public_path($line));
            }

            // increment the amount of files migrated counter
            $this->files++;
        }
    }

    private function getResultsFromDatabase()
    {
        $columns = [$this->urlcolumn, $this->idcolumn, $this->ordercolumn, $this->localecolumn];

        $builder = DB::table($this->table)->select($columns);

        if (! $this->ordercolumn) {
            $builder = $builder->orderBy($this->idcolumn);
        }

        $results = $builder->get();

        return $results;
    }

    private function setArguments()
    {
        $this->table        = $this->argument('table');
        $this->urlcolumn    = $this->argument('urlcolumn');
        $this->linkedmodel  = $this->argument('linkedmodel');
        $this->idcolumn     = $this->argument('idcolumn');
        $this->ordercolumn  = $this->argument('ordercolumn');
        $this->localecolumn = $this->argument('localecolumn');
    }

    private function handleResetFlag($orderedResults)
    {
        if ($this->option('reset') && ! $this->option('dry')) {
            $this->info('Resetting the assets on the models');
            $resetbar = $this->output->createProgressBar(count($orderedResults));

            $orderedResults->each(function ($entry) use ($resetbar) {
                app(DeleteAsset::class)->deleteAll($entry['model']);
                $resetbar->advance();
            });

            $resetbar->finish();
        }
    }

    private function mapResults($results)
    {
        return $results->map(function ($result) {
            $formattedResults = [];

            $formattedResults['images'][] = $result->{$this->urlcolumn};
            $formattedResults['model']    = $this->linkedmodel::find($result->{$this->idcolumn});

            if ($this->ordercolumn) {
                $formattedResults['order'] = $result->{$this->ordercolumn};
            }else{
                $formattedResults['order'] = null;
            }

            return $formattedResults;
        })->reject(function ($value) {
            if ($result = $value['model'] == null) {
                $this->nomodel++;
            }

            return $result;
        });
    }
}
