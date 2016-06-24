<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Graker\PhotoAlbums\Models\Photo as PhotoModel;
use Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RandomPhotos extends ComponentBase
{

  public function componentDetails()
  {
    return [
      'name'        => 'Random Photos',
      'description' => 'Output predefined number of random photos',
    ];
  }

  public function defineProperties()
  {
    return [
      'photosCount' => [
        'title'             => 'Photos to output',
        'description'       => 'Amount of random photos to output',
        'default'           => 5,
        'type'              => 'string',
        'validationMessage' => 'Photos count must be a number',
        'validationPattern' => '^[0-9]+$',
        'required'          => FALSE,
      ],
      'cacheLifetime' => [
        'title'             => 'Cache Lifetime',
        'description'       => 'Number of minutes selected photos are stored in cache. 0 for no caching.',
        'default'           => 0,
        'type'              => 'string',
        'validationMessage' => 'Cache lifetime must be a number',
        'validationPattern' => '^[0-9]+$',
        'required'          => FALSE,
      ],
      'thumbMode' => [
        'title'       => 'Thumb mode',
        'description' => 'Mode of thumb generation',
        'type'        => 'dropdown',
        'default'     => 'auto',
      ],
      'photoPage' => [
        'title'       => 'Photo page',
        'description' => 'Page used to display a single photo',
        'type'        => 'dropdown',
        'default'     => 'blog/post',
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


  /**
   *
   * Returns an array of photosCount random photos
   * Array is returned if from Cache, Collection otherwise
   *
   * @return array|Collection
   */
  public function photos() {
    $photos = [];
    if ($this->property('cacheLifetime')) {
      $photos = Cache::get('photoalbums_random_photos');
    }

    if (empty($photos)) {
      $photos = $this->getPhotos();
    }

    return $photos;
  }


  /**
   *
   * Returns a collection of random photos
   *
   * @return Collection
   */
  protected function getPhotos() {
    $count = $this->property('photosCount');
    $photos = PhotoModel::orderBy(DB::raw('RAND()'))
      ->with('image')
      ->take($count)
      ->get();

    foreach ($photos as $photo) {
      $photo->url = $photo->setUrl($this->property('photoPage'), $this->controller);
      $photo->thumb = $photo->image->getThumb(640, 480, ['mode' => $this->property('thumbMode')]);
    }

    $this->cachePhotos($photos);

    return $photos;
  }


  /**
   *
   * Cache photos if caching is enabled
   *
   * @param Collection $photos
   */
  protected function cachePhotos($photos) {
    $cache = $this->property('cacheLifetime');
    if ($cache) {
      Cache::put('photoalbums_random_photos', $photos->toArray(), $cache);
    }
  }

}
