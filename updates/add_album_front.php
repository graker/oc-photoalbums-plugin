<?php namespace Graker\PhotoAlbums\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddAlbumFront extends Migration
{

  public function up()
  {
    Schema::table('graker_photoalbums_albums', function($table)
    {
      $table->integer('front_id')->unsigned()->nullable();
    });
  }

  public function down()
  {
    Schema::table('graker_photoalbums_albums', function($table)
    {
      $table->dropColumn('front_id');
    });
  }

}
