<?php

namespace Graker\PhotoAlbums\Widgets;

use Backend\Classes\WidgetBase;
use Graker\PhotoAlbums\Models\Album;
use Graker\PhotoAlbums\Models\Photo;
use Illuminate\Support\Collection;

/**
 * Class PhotoSelector
 * Creates a widget allowing to navigate through albums and photos
 * in order to select one of the photos (to insert it into text, for example)
 *
 * @package Graker\PhotoAlbums\Widgets
 */
class PhotoSelector extends WidgetBase {

    // TODO implement photo selection
    // TODO add footer buttons
    // TODO add spinners for waiting
    // TODO implement insert
    // TODO try to remember last position so user won't be selecting the same album over and over again
    // TODO we probably could add markdown to each photo right here
    // TODO create plugin settings to store default markdown to be inserted

    /**
     * @var string unique widget alias
     */
    protected $defaultAlias = 'photoSelector';


    /**
     *
     * Render the widget
     *
     * @return string
     */
    public function render() {
        $this->vars['albums'] = $this->albums();

        return $this->makePartial('body');
    }


    /**
     * Loads widget assets
     */
    protected function loadAssets() {
        $this->addJs('js/photoselector.js');
    }


    /**
     *
     * Callback for when the dialog is initially open
     *
     * @return string
     */
    public function onDialogOpen() {
        return $this->render();
    }


    /**
     *
     * Callback to generate albums list
     *
     * @return array
     */
    public function onAlbumListLoad() {
        $this->vars['albums'] = $this->albums();

        return [
            '#photosList' => $this->makePartial('albums'),
        ];
    }


    /**
     *
     * Callback to generate photos list
     * Photos list is to replace albumsList in dialog markup
     *
     * @return array
     */
    public function onAlbumLoad() {
        $album_id = input('id');
        $album = $this->album($album_id);
        $this->vars['album_title'] = $album->title;
        $this->vars['photos'] = $album->photos;

        return [
            '#albumsList' => $this->makePartial('photos'),
        ];
    }


    /**
     *
     * Returns a collection of all user's albums
     *
     * @return Collection
     */
    protected function albums() {
        // TODO duplicate (almost) code here and in albumlist component, refactor?
        $albums = Album::orderBy('created_at', 'desc')
          ->has('photos')
          ->with(['latestPhoto' => function ($query) {
              $query->with('image');
          }])
          ->with(['front' => function ($query) {
              $query->with('image');
          }])
          ->get();

        foreach ($albums as $album) {
            // prepare thumb from $album->front if it is set or from latestPhoto otherwise
            $image = ($album->front) ? $album->front->image : $album->latestPhoto->image;
            $album->thumb = $image->getThumb(
              160,
              120,
              ['mode' => 'crop']
            );
        }

        return $albums;
    }


    /**
     *
     * Returns album with its photos loaded and prepared for display in dialog
     *
     * @param int $album_id
     * @return Album
     */
    protected function album($album_id) {
        $album = Album::where('id', $album_id)
          ->with(['photos' => function ($query) {
              $query->orderBy('sort_order', 'desc');
              $query->with('image');
              // TODO add pagination
              // $query->paginate($this->property('photosOnPage'), $this->currentPage);
          }])
          ->first();

        if ($album) {
            //prepare photo urls and thumbs
            foreach ($album->photos as $photo) {
                $photo->thumb = $photo->image->getThumb(
                  160,
                  120,
                  ['mode' => 'crop']
                );
            }
        }

        return $album;
    }

}
