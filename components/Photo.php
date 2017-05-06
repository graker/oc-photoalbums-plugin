<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Graker\PhotoAlbums\Models\Photo as PhotoModel;

class Photo extends ComponentBase
{

    public $photo;

    public function componentDetails()
    {
        return [
          'name'        => 'graker.photoalbums::lang.plugin.photo',
          'description' => 'graker.photoalbums::lang.components.photo_description'
        ];
    }

    /**
     *
     * Properties of component
     *
     * @return array
     */
    public function defineProperties()
    {
        return [
          'id' => [
            'title'       => 'graker.photoalbums::lang.components.id_label',
            'description' => 'graker.photoalbums::lang.components.id_description',
            'default'     => '{{ :id }}',
            'type'        => 'string'
          ],
          'albumPage' => [
            'title'       => 'graker.photoalbums::lang.components.album_page_label',
            'description' => 'graker.photoalbums::lang.components.album_page_description',
            'type'        => 'dropdown',
            'default'     => 'photoalbums/album',
          ],
          'photoPage' => [
            'title'       => 'graker.photoalbums::lang.components.photo_page_label',
            'description' => 'graker.photoalbums::lang.components.photo_page_description',
            'type'        => 'dropdown',
            'default'     => 'photoalbums/album/photo',
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
     * Returns pages list for photo page select box setting
     *
     * @return mixed
     */
    public function getPhotoPageOptions() {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }


    /**
     * Loads photo on onRun event
     */
    public function onRun() {
        $this->photo = $this->page->photo = $this->loadPhoto();
    }


    /**
     *
     * Loads photo to be displayed in this component
     *
     * @return PhotoModel
     */
    protected function loadPhoto() {
        $id = $this->property('id');
        $photo = PhotoModel::where('id', $id)
          ->with('image')
          ->with('album')
          ->first();

        if ($photo) {
            // set url so we can have back link to the parent album
            $photo->album->url = $photo->album->setUrl($this->property('albumPage'), $this->controller);

            //set next and previous photos
            $photo->next = $photo->nextPhoto();
            if ($photo->next) {
                $photo->next->url = $photo->next->setUrl($this->property('photoPage'), $this->controller);
            }
            $photo->previous = $photo->previousPhoto();
            if ($photo->previous) {
                $photo->previous->url = $photo->previous->setUrl($this->property('photoPage'), $this->controller);
            }
        }

        return $photo;
    }

}
