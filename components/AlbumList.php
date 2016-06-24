<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Graker\PhotoAlbums\Models\Album as AlbumModel;

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
   * Define properties
   *
   * @return array of component properties
   */
  public function defineProperties()
  {
    return [
      'albumPage' => [
        'title'       => 'Album page',
        'description' => 'Page used to display photo albums',
        'type'        => 'dropdown',
        'default'     => 'photoalbums/album',
      ],
      'thumbMode' => [
        'title'       => 'Thumb mode',
        'description' => 'Mode of thumb generation',
        'type'        => 'dropdown',
        'default'     => 'auto',
      ],
      'thumbWidth' => [
        'title'             => 'Thumb width',
        'description'       => 'Width of the thumb to be generated',
        'default'           => 640,
        'type'              => 'string',
        'validationMessage' => 'Thumb width must be a number',
        'validationPattern' => '^[0-9]+$',
        'required'          => FALSE,
      ],
      'thumbHeight' => [
        'title'             => 'Thumb height',
        'description'       => 'Height of the thumb to be generated',
        'default'           => 480,
        'type'              => 'string',
        'validationMessage' => 'Thumb height must be a number',
        'validationPattern' => '^[0-9]+$',
        'required'          => FALSE,
      ],
    ];
  }


  /**
   *
   * Returns pages list for album page select box setting
   *
   * @return mixed
   */
  public function getAlbumPageOptions() {
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


  /**
   *
   * Returns array of site's albums to be used in component
   * Albums are sorted by created date desc, each one loaded with one latest photo
   *
   * @return array
   */
  public function albums() {
    $albums = AlbumModel::orderBy('created_at', 'desc')
      ->with(['latestPhoto' => function ($query) {
        $query->with('image');
      }])
      ->with('photosCount')
      ->get();

    return $this->prepareAlbums($albums);
  }


  /**
   *
   * Prepares array of album models to be displayed:
   *  - set up album urls
   *  - set up photo counts
   *
   * @param AlbumModel[] $albums - array of albums preparing to display
   * @return AlbumModel[] - prepared array of albums
   */
  protected function prepareAlbums($albums) {
    //set up photo count and url
    foreach ($albums as $album) {
      $album->photo_count = $album->photosCount;
      $album->url = $album->setUrl($this->property('albumPage'), $this->controller);
      $album->latestPhoto->thumb = $album->latestPhoto->image->getThumb(
        $this->property('thumbWidth'),
        $this->property('thumbHeight'),
        ['mode' => $this->property('thumbMode')]
      );
    }

    return $albums;
  }

}
