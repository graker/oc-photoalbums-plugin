<?php namespace Graker\PhotoAlbums\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePhotosTable extends Migration
{

    public function up()
    {
        Schema::create('graker_photoalbums_photos', function($table)
        {
          $table->engine = 'InnoDB';
          $table->increments('id');
          $table->integer('user_id')->unsigned()->nullable()->index();
          $table->integer('album_id')->unsigned()->nullable()->index();
          $table->integer('sort_order')->unsigned()->nullable();
          $table->string('title')->nullable();
          $table->text('description')->nullable();
          $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('graker_photoalbums_photos');
    }

}
