<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Graker\PhotoAlbums\Models\Album as AlbumModel;
use Illuminate\Database\Eloquent\Collection;
use Redirect;

class AlbumList extends ComponentBase
{

  /**
   * @var Collection of albums to display
   */
  public $albums;


  /**
   * @return int current page number
   */
  public $currentPage;


  /**
   * @var int last page number
   */
  public $lastPage;


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
      'albumsOnPage' => [
        'title'             => 'Albums on page',
        'description'       => 'Amount of albums on one page (to use in pagination)',
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
   * OnRun implementation
   * Setup pager
   * Load albums
   */
  public function onRun() {
    if (!$this->setCurrentPage()) {
      return Redirect::to($this->currentPageUrl() . '?page=1');
    }
    $this->albums = $this->loadAlbums();
    $this->prepareAlbums();

    $this->lastPage = $this->albums->lastPage();
    //if current page is greater than number of pages, redirect to the last page
    if ($this->currentPage > $this->lastPage) {
      return Redirect::to($this->currentPageUrl() . '?page=' . $this->lastPage);
    }
  }


  /**
   *
   * Returns array of site's albums to be used in component
   * Albums are sorted by created date desc, each one loaded with one latest photo
   *
   * @return array
   */
  protected function loadAlbums() {
    $albums = AlbumModel::orderBy('created_at', 'desc')
      ->with(['latestPhoto' => function ($query) {
        $query->with('image');
      }])
      ->with('photosCount')
      ->paginate($this->property('albumsOnPage'), $this->currentPage);

    return $albums;
  }


  /**
   *
   * Prepares array of album models to be displayed:
   *  - set up album urls
   *  - set up photo counts
   */
  protected function prepareAlbums() {
    //set up photo count and url
    foreach ($this->albums as $album) {
      $album->photo_count = $album->photosCount;
      $album->url = $album->setUrl($this->property('albumPage'), $this->controller);
      $album->latestPhoto->thumb = $album->latestPhoto->image->getThumb(
        $this->property('thumbWidth'),
        $this->property('thumbHeight'),
        ['mode' => $this->property('thumbMode')]
      );
    }
  }

}
