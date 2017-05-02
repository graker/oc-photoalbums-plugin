<?php namespace Graker\PhotoAlbums\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddSortOrder extends Migration
{

  public function up()
  {
    Schema::table('graker_photoalbums_photos', function($table)
    {
      $table->integer('sort_order')->unsigned()->nullable();
    });
  }

  public function down()
  {
    Schema::table('graker_photoalbums_photos', function($table)
    {
      $table->dropColumn('sort_order');
    });
  }

}
