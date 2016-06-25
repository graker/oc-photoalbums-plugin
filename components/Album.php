<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Graker\PhotoAlbums\Models\Album as AlbumModel;
use Redirect;

class Album extends ComponentBase
{

  /**
   * @var AlbumModel reference to album being displayed
   */
  public $album;


  /**
   * @return int current page number
   */
  public $currentPage;


  /**
   * @var int last page number
   */
  public $lastPage;


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
      'photosOnPage' => [
        'title'             => 'Photos on page',
        'description'       => 'Amount of photos on one page (to use in pagination)',
        'default'           => 12,
        'type'              => 'string',
        'validationMessage' => 'Photos on page value must be a number',
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
   * Get photo page number from query
   */
  protected function setCurrentPage() {
    if (isset($_GET['page'])) {
      if (ctype_digit($_GET['page']) && ($_GET['page'] > 0)) {
        $this->currentPage = $_GET['page'];
      } else {
        return FALSE;
      }
    } else {
      $this->currentPage = 1;
    }
    return TRUE;
  }


  /**
   * Loads album on onRun event
   */
  public function onRun() {
    if (!$this->setCurrentPage()) {
      // if page parameter is invalid, redirect to the first page
      return Redirect::to($this->currentPageUrl() . '?page=1');
    }
    $this->album = $this->loadAlbum();
    // if current page is greater than number of pages, redirect to the last page
    // check for > 1 to avoid infinite redirect when there are no photos
    if (($this->currentPage > 1) && ($this->currentPage > $this->lastPage)) {
      return Redirect::to($this->currentPageUrl() . '?page=' . $this->lastPage);
    }
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
        $query->paginate($this->property('photosOnPage'), $this->currentPage);
      }])
      ->first();

    if ($album) {
      //prepare photo urls and thumbs
      foreach ($album->photos as $photo) {
        $photo->url = $photo->setUrl($this->property('photoPage'), $this->controller);
        $photo->thumb = $photo->image->getThumb(
          $this->property('thumbWidth'),
          $this->property('thumbHeight'),
          ['mode' => $this->property('thumbMode')]
        );
      }
      //setup page numbers
      $this->lastPage = ceil($album->photosCount / $this->property('photosOnPage'));
    }

    return $album;
  }

}
