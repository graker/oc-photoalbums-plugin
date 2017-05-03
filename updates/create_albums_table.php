<?php namespace Graker\PhotoAlbums\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateAlbumsTable extends Migration
{

  public function up()
  {
    Schema::create('graker_photoalbums_albums', function($table)
    {
      $table->engine = 'InnoDB';
      $table->increments('id');
      $table->integer('user_id')->unsigned()->nullable()->index();
      $table->integer('front_id')->unsigned()->nullable();
      $table->string('title')->nullable();
      $table->string('slug')->index();
      $table->text('description')->nullable();
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('graker_photoalbums_albums');
  }

}
