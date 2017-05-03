<?php namespace Graker\PhotoAlbums\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Graker\PhotoAlbums\Models\Photo;

class UpdateSortOrderOnExistingPhotos extends Migration
{

  public function up()
  {
    // fill sort_order values for existing photos with photo ids
    foreach (Photo::all() as $photo) {
      $photo->sort_order = $photo->id;
      $photo->save();
    }
  }

  public function down()
  {
  }

}
