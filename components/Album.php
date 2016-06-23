<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Graker\PhotoAlbums\Models\Album as AlbumModel;

class Album extends ComponentBase
{

  /**
   * @var AlbumModel reference to album being displayed
   */
  public $album;

  public function componentDetails()
  {
    return [
      'name'        => 'Album',
      'description' => 'Component to output one photo album with all its photos'
    ];
  }

  /**
   * @return array of component properties
   */
  public function defineProperties()
  {
    return [
      'slug' => [
        'title'       => 'Slug',
        'description' => 'URL slug parameter',
        'default'     => '{{ :slug }}',
        'type'        => 'string'
      ],
      'photoPage' => [
        'title'       => 'Photo page',
        'description' => 'Page used to display a single photo',
        'type'        => 'dropdown',
        'default'     => 'photoalbums/album/photo',
      ],
      'thumbMode' => [
        'title'       => 'Thumb mode',
        'description' => 'Mode of thumb generation',
        'type'        => 'dropdown',
        'default'     => 'auto',
      ],
    ];
  }

  /**
   *
   * Returns pages list for album page select box setting
   *
   * @return mixed
   */
  public function getPhotoPageOptions() {
    return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
  }


  /**
   *
   * Returns thumb resize mode options for thumb mode select box setting
   *
   * @return array
   */
  public function getThumbModeOptions() {
    return [
      'auto' => 'Auto',
      'exact' => 'Exact',
      'portrait' => 'Portrait',
      'landscape' => 'Landscape',
      'crop' => 'Crop',
    ];
  }

  //TODO introduce photos pagination

  /**
   * Loads album on onRun event
   */
  public function onRun() {
    $this->album = $this->loadAlbum();
  }


  /**
   *
   * Loads album model with it's photos
   *
   * @return AlbumModel
   */
  protected function loadAlbum() {
    $slug = $this->property('slug');
    $album = AlbumModel::where('slug', $slug)
      ->with(['photos' => function ($query) {
        $query->orderBy('created_at', 'desc');
        $query->with('image');
      }])
      ->first();

    if ($album) {
      //prepare photo urls and thumbs
      foreach ($album->photos as $photo) {
        $photo->url = $photo->setUrl($this->property('photoPage'), $this->controller);
        $photo->thumb = $photo->image->getThumb(640, 480, ['mode' => $this->property('thumbMode')]);
      }
    }

    return $album;
  }

}
