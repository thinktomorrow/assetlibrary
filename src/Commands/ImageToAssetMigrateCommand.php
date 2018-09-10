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
    protected $signature = 'assetlibrary:migrate-image {table} {urlcolumn} {linkedmodel} {idcolumn=id} {ordercolumn?} {--force} {--reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import images from imageurl on a model to assets managed by assetlibrary.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \ini_set('memory_limit', '256M');
        
        $tableName      = $this->argument('table');
        $imageUrlColumn = $this->argument('urlcolumn');
        $modelIdColumn  = $this->argument('idcolumn');
        $ordercolumn    = $this->argument('ordercolumn');
        $model          = $this->argument('linkedmodel');
        $unreachable    = 0;
        $files          = 0;

        if($ordercolumn){
            $results = DB::table($tableName)->select($imageUrlColumn, $modelIdColumn, $ordercolumn)->get();
        }else{
            $results = DB::table($tableName)->select($imageUrlColumn, $modelIdColumn)->orderBy($modelIdColumn)->get();
        }

        $orderedResults = [];
        $results->each(function($result) use($modelIdColumn, $results, &$orderedResults, $imageUrlColumn){
            $orderedResults[$result->$modelIdColumn][] = $result->$imageUrlColumn;
        });

        $orderedResults = collect($orderedResults);

        if($this->option('reset')){
            $this->info('Resetting the asserts on the models');
            $resetbar = $this->output->createProgressBar(count($orderedResults));
            
            $orderedResults->each(function($entry, $key) use($model, $resetbar){
                optional($model::find($key))->deleteAllAssets();
                $resetbar->advance();
            });
            
            $resetbar->finish();
        }
        

        $bar = $this->output->createProgressBar(count($results));
        
        $this->info("\n" . 'Migrating images.');
        foreach($orderedResults as $key => $result)
        {
            $persistedModel = $model::find($key);
            if(!$persistedModel){
                continue;
            }

            foreach($result as $line){
                $bar->advance();
                
                try{
                    $asset = AssetUploader::uploadFromUrl(public_path($line));
                }catch(UnreachableUrl $ex){
                    // increment the amount of unreachable files counter
                    $unreachable++;
                    
                    continue;
                }
                
                $asset->attachToModel($persistedModel);
                
                if($this->option('force')){
                    unlink(public_path($line));
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
}
