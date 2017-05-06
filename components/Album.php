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
      'name'        => 'graker.photoalbums::lang.plugin.album',
      'description' => 'graker.photoalbums::lang.components.album_description'
    ];
  }

  /**
   * @return array of component properties
   */
  public function defineProperties()
  {
    return [
      'slug' => [
        'title'       => 'graker.photoalbums::lang.plugin.slug_label',
        'description' => 'graker.photoalbums::lang.plugin.slug_description',
        'default'     => '{{ :slug }}',
        'type'        => 'string'
      ],
      'photoPage' => [
        'title'       => 'graker.photoalbums::lang.components.photo_page_label',
        'description' => 'graker.photoalbums::lang.components.photo_page_description',
        'type'        => 'dropdown',
        'default'     => 'photoalbums/album/photo',
      ],
      'thumbMode' => [
        'title'       => 'graker.photoalbums::lang.components.thumb_mode_label',
        'description' => 'graker.photoalbums::lang.components.thumb_mode_description',
        'type'        => 'dropdown',
        'default'     => 'auto',
      ],
      'thumbWidth' => [
        'title'             => 'graker.photoalbums::lang.components.thumb_width_label',
        'description'       => 'graker.photoalbums::lang.components.thumb_width_description',
        'default'           => 640,
        'type'              => 'string',
        'validationMessage' => 'graker.photoalbums::lang.errors.thumb_width_error',
        'validationPattern' => '^[0-9]+$',
        'required'          => FALSE,
      ],
      'thumbHeight' => [
        'title'             => 'graker.photoalbums::lang.components.thumb_height_label',
        'description'       => 'graker.photoalbums::lang.components.thumb_height_description',
        'default'           => 480,
        'type'              => 'string',
        'validationMessage' => 'graker.photoalbums::lang.errors.thumbs_height_error',
        'validationPattern' => '^[0-9]+$',
        'required'          => FALSE,
      ],
      'photosOnPage' => [
        'title'             => 'graker.photoalbums::lang.components.photos_on_page_label',
        'description'       => 'graker.photoalbums::lang.components.photos_on_page_description',
        'default'           => 12,
        'type'              => 'string',
        'validationMessage' => 'graker.photoalbums::lang.errors.photos_on_page_error',
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
    $this->album = $this->page->album = $this->loadAlbum();
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
