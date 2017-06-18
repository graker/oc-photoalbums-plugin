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
     * Callback for when the dialog is open
     *
     * @return string
     */
    public function onDialogOpen() {
        return $this->render();
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
        $this->vars['id'] = $album_id;
        $this->vars['photos'] = $this->photos($album_id);

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
     * Returns a collection of album photos
     *
     * @param int $album_id
     * @return Collection
     */
    protected function photos($album_id) {
        $photos = Photo::where('album_id', $album_id)
          ->with('image')
          ->get();

        foreach ($photos as $photo) {
            $photo->thumb = $photo->image->getThumb(
              160,
              120,
              ['mode' => 'crop']
            );
        }

        return $photos;
    }

}
