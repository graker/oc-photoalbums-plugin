<?php

namespace Graker\PhotoAlbums\Widgets;

use Backend\Classes\WidgetBase;
use Graker\PhotoAlbums\Models\Album;
use Graker\PhotoAlbums\Models\Photo;
use Graker\PhotoAlbums\Models\Settings;
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
        // if we have current album id, open the album, otherwise open albums list
        $album_id = input('album');

        if ($album_id) {
            $this->vars['albums'] = NULL;
            $this->vars['album'] = $this->album($album_id);
        } else {
            $this->vars['albums'] = $this->albums();
            $this->vars['album'] = NULL;
        }


        return $this->makePartial('body');
    }


    /**
     * Loads widget assets
     */
    protected function loadAssets() {
        $this->addJs('js/photoselector.js');
        $this->addCss('css/photoselector.css');
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
          '#listContainer' => $this->makePartial('albums'),
        ];
    }


    /**
     *
     * Callback to generate photos list
     * Photos list is to replace albums list in dialog markup
     *
     * @return array
     */
    public function onAlbumLoad() {
        $album_id = input('id');
        $album = $this->album($album_id);
        $this->vars['album'] = $album;

        return [
          '#listContainer' => $this->makePartial('photos'),
        ];
    }


    /**
     *
     * Returns a collection of all user's albums
     *
     * @return Collection
     */
    protected function albums() {
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
              // TODO implement pagination
          }])
          ->first();

        if ($album) {
            //prepare photo urls and thumbs
            foreach ($album->photos as $photo) {
                // set thumb
                $photo->thumb = $photo->image->getThumb(
                  160,
                  120,
                  ['mode' => 'crop']
                );
                // set code
                $photo->code = $this->createPhotoCode($photo);
            }
        }

        return $album;
    }


    /**
     *
     * Create an insert markdown code for photo from plugin settings
     *
     * @param Photo $photo
     * @return string
     */
    protected function createPhotoCode($photo) {
        $code_template = Settings::get('code', '![%title%]([photo:%id%])');
        $code = str_replace(
          array('%id%', '%title%'),
          array($photo->id, $photo->title),
          $code_template
        );
        return $code;
    }

}
