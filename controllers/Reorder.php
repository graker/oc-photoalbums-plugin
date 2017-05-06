<?php namespace Graker\PhotoAlbums\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Graker\PhotoAlbums\Models\Album;
use Graker\PhotoAlbums\Models\Photo;
use Lang;

/**
 * Reorder Back-end Controller
 */
class Reorder extends Controller
{

    /**
     *
     * Album model reference
     *
     * @var null
     */
    public $model = NULL;


    /**
     *
     * Display the reorder interface for album
     *
     * @param null|int $album_id
     * @return mixed|string
     */
    public function album($album_id = NULL) {
        $album = Album::find($album_id);
        if (!$album) {
            return '';
        }

        $this->model = $album;
        $this->addJs('/modules/backend/behaviors/reordercontroller/assets/js/october.reorder.js', 'core');

        $this->pageTitle = Lang::get('graker.photoalbums::lang.plugin.reorder_title', ['name' => $album->title]);

        return $this->makePartial('reorder', ['reorderRecords' => $this->model->photos,]);
    }


    /**
     * Callback to save reorder information
     * Calls function from Sortable trait on the model
     */
    public function onReorder() {
        if (!$ids = post('record_ids')) return;
        if (!$orders = post('sort_orders')) return;

        $model = new Photo();
        $model->setSortableOrder($ids, $orders);
    }


    /**
     * Reorder constructor
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Graker.PhotoAlbums', 'photoalbums', 'albums');
    }

}
