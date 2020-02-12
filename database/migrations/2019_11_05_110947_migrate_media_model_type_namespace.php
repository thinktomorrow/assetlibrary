<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\MediaLibrary\Models\Media;

class MigrateMediaModelTypeNamespace extends Migration
{
    public function up()
    {
        Media::where('model_type', 'Thinktomorrow\AssetLibrary\Models\Asset')->update(['model_type' => 'Thinktomorrow\AssetLibrary\Asset']);
    }

    public function down()
    {
    }
}
