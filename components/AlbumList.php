<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\ComponentBase;
use Graker\PhotoAlbums\Models\Album;

class AlbumList extends ComponentBase
{

  /**
   * @return array of component details
   */
  public function componentDetails()
  {
    return [
      'name'        => 'Albums List',
      'description' => 'Lists all photo albums on site'
    ];
  }

  /**
   *
   * Don't have properties yet
   *
   * @return array of component properties
   */
  public function defineProperties()
  {
    return [];
  }


  /**
   *
   * Returns array of site's albums to be used in component
   * Albums are sorted by created date desc, each one loaded with one latest photo
   *
   * @return array
   */
  public function albums() {
    $albums = Album::orderBy('created_at', 'desc')
      ->with(['latestPhoto' => function ($query) {
        $query->with('image');
      }])
      ->get();
    return $albums;
  }
}
