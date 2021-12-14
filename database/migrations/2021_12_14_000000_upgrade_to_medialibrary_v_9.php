<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UpgradeToMedialibraryV9 extends Migration
{
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique();
            $table->string('conversions_disk')->nullable();
            $table->json('generated_conversions')->default('{}');
        });

        Media::cursor()->each(
            fn (Media $media) => $media->update(['uuid' => Str::uuid()])
        );

        Media::cursor()->each(
            fn (Media $media) => $media->update(['conversions_disk' => $media->disk])
        );

        Media::query()
            ->update([
                'generated_conversions' => DB::raw('JSON_EXTRACT(custom_properties, "$.generated_conversions")'),
                'custom_properties' => DB::raw("JSON_REMOVE(custom_properties, '$.generated_conversions')")
            ]);
    }

    public function down()
    {
    }
}
