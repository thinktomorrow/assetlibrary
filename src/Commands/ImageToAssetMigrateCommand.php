<?php

namespace Thinktomorrow\AssetLibrary\Commands;

use Exception;
use Storage;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;
use League\Flysystem\Filesystem;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnreachableUrl;

class ImageToAssetMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assetlibrary:migrate-image {table} {urlcolumn} {linkedmodel} {idcolumn=id} {ordercolumn?} {--force} {--reset} {--dry}';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \ini_set('memory_limit', '256M');
        
        $unreachable    = 0;
        $files          = 0;

        $this->setArguments();

        $results = $this->getResultsFromDatabase();

        $orderedResults = $results->map(function ($result){
            $formattedResults['images'][] = $result->{$this->urlcolumn};
            $formattedResults['model']    = $this->linkedmodel::find($result->{$this->idcolumn});

            if ($this->ordercolumn) {
                $formattedResults['order'] = $result->{$this->ordercolumn};
            }

            return $formattedResults;
        });

        $this->handleResetFlag($orderedResults);
        
        $this->info("\n" . 'Migrating images.');
        
        $bar = $this->output->createProgressBar(count($results));
        foreach ($orderedResults->toArray() as $result) {

            if (!$persistedModel = $result['model']) {
                continue;
            }

            foreach ($result['images'] as $line) {
                $bar->advance();
                
                if(!$this->option('dry')){
                    try {
                        $asset = AssetUploader::uploadFromUrl(public_path($line));
                    } catch (UnreachableUrl $ex) {
                        // increment the amount of unreachable files counter
                        $unreachable++;
                        
                        continue;
                    }
                    
                    $asset->attachToModel($persistedModel);
                    
                    if ($this->argument('ordercolumn')) {
                        $asset->setOrder($result['order']);
                    }
                    
                    if ($this->option('force')) {
                        unlink(public_path($line));
                    }
                }
                
                // increment the amount of files migrated counter
                $files++;
            }
        }
       
        $bar->finish();

        $this->info('Migrating done.');
        $this->info('Migrated '. $files . ' files.');
        $this->info('Couldn\'t reach '. $unreachable . ' files.');
    }

    private function getResultsFromDatabase()
    {
        if ($this->ordercolumn) {
            $results = DB::table($this->table)->select($this->urlcolumn, $this->idcolumn, $this->ordercolumn)->get();
        } else {
            $results = DB::table($this->table)->select($this->urlcolumn, $this->idcolumn)->orderBy($this->idcolumn)->get();
        }

        return $results;
    }

    private function setArguments()
    {
        $this->table       = $this->argument('table');
        $this->urlcolumn   = $this->argument('urlcolumn');
        $this->linkedmodel = $this->argument('linkedmodel');
        $this->idcolumn    = $this->argument('idcolumn');
        $this->ordercolumn = $this->argument('ordercolumn');
    }

    private function handleResetFlag($orderedResults)
    {
        if ($this->option('reset') && !$this->option('dry')) {
            $this->info('Resetting the assets on the models');
            $resetbar = $this->output->createProgressBar(count($orderedResults));
            
            $orderedResults->each(function ($entry, $key) use ($resetbar) {
                optional($entry['model'])->deleteAllAssets();
                $resetbar->advance();
            });
            
            $resetbar->finish();
        }
    }
}
