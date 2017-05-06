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
          'name'        => 'graker.photoalbums::lang.components.albums_list',
          'description' => 'graker.photoalbums::lang.components.albums_list_description'
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
            'title'       => 'graker.photoalbums::lang.components.album_page_label',
            'description' => 'graker.photoalbums::lang.components.album_page_description',
            'type'        => 'dropdown',
            'default'     => 'photoalbums/album',
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
            'validationMessage' => 'graker.photoalbums::lang.errors.thumb_height_error',
            'validationPattern' => '^[0-9]+$',
            'required'          => FALSE,
          ],
          'albumsOnPage' => [
            'title'             => 'graker.photoalbums::lang.components.albums_on_page_label',
            'description'       => 'graker.photoalbums::lang.components.albums_on_page_description',
            'default'           => 12,
            'type'              => 'string',
            'validationMessage' => 'graker.photoalbums::lang.errors.albums_on_page_error',
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
        // if current page is greater than number of pages, redirect to the last page
        // only if lastPage > 0 to avoid redirect loop when there are no elements
        if ($this->lastPage && ($this->currentPage > $this->lastPage)) {
            return Redirect::to($this->currentPageUrl() . '?page=' . $this->lastPage);
        }
    }


    /**
     *
     * Returns array of site's albums to be used in component
     * Albums are sorted by created date desc, each one loaded with one latest photo (or photo set to be front)
     * Empty albums won't be displayed
     *
     * @return array
     */
    protected function loadAlbums() {
        $albums = AlbumModel::orderBy('created_at', 'desc')
          ->has('photos')
          ->with(['latestPhoto' => function ($query) {
              $query->with('image');
          }])
          ->with(['front' => function ($query) {
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
     *  - set up album thumb
     */
    protected function prepareAlbums() {
        //set up photo count and url
        foreach ($this->albums as $album) {
            $album->photo_count = $album->photosCount;
            $album->url = $album->setUrl($this->property('albumPage'), $this->controller);

            // prepare thumb from $album->front if it is set or from latestPhoto otherwise
            $image = ($album->front) ? $album->front->image : $album->latestPhoto->image;
            $album->latestPhoto->thumb = $image->getThumb(
              $this->property('thumbWidth'),
              $this->property('thumbHeight'),
              ['mode' => $this->property('thumbMode')]
            );
        }
    }

}
