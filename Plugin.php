<?php namespace Graker\PhotoAlbums;

use Backend;
use System\Classes\PluginBase;
use Event;
use Backend\Widgets\Form;
use Lang;
use Graker\PhotoAlbums\Widgets\PhotoSelector;

/**
 * PhotoAlbums Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
          'name'        => 'graker.photoalbums::lang.plugin.name',
          'description' => 'graker.photoalbums::lang.plugin.description',
          'author'      => 'Graker',
          'icon'        => 'icon-camera-retro',
          'homepage'    => 'https://github.com/graker/oc-photoalbums-plugin',
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
          'Graker\PhotoAlbums\Components\Photo' => 'singlePhoto',
          'Graker\PhotoAlbums\Components\Album' => 'photoAlbum',
          'Graker\PhotoAlbums\Components\AlbumList' => 'albumList',
          'Graker\PhotoAlbums\Components\RandomPhotos' => 'randomPhotos',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     * At the moment there's one permission allowing overall management of albums and photos
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
          'graker.photoalbums.manage_albums' => [
            'label' => 'graker.photoalbums::lang.plugin.manage_albums',
            'tab' => 'graker.photoalbums::lang.plugin.tab',
          ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
          'photoalbums' => [
            'label' => 'graker.photoalbums::lang.plugin.tab',
            'url' => Backend::url('graker/photoalbums/albums'),
            'icon'        => 'icon-camera-retro',
            'permissions' => ['graker.photoalbums.manage_albums'],
            'order'       => 500,

            'sideMenu' => [
              'upload_photos' => [
                'label'       => 'graker.photoalbums::lang.plugin.upload_photos',
                'icon'        => 'icon-upload',
                'url'         => Backend::url('graker/photoalbums/upload/form'),
                'permissions' => ['graker.photoalbums.manage_albums'],
              ],
              'new_album' => [
                'label'       => 'graker.photoalbums::lang.plugin.new_album',
                'icon'        => 'icon-plus',
                'url'         => Backend::url('graker/photoalbums/albums/create'),
                'permissions' => ['graker.photoalbums.manage_albums'],
              ],
              'albums' => [
                'label'       => 'graker.photoalbums::lang.plugin.albums',
                'icon'        => 'icon-copy',
                'url'         => Backend::url('graker/photoalbums/albums'),
                'permissions' => ['graker.photoalbums.manage_albums'],
              ],
              'new_photo' => [
                'label'       => 'graker.photoalbums::lang.plugin.new_photo',
                'icon'        => 'icon-plus-square-o',
                'url'         => Backend::url('graker/photoalbums/photos/create'),
                'permissions' => ['graker.photoalbums.manage_albums'],
              ],
              'photos' => [
                'label'       => 'graker.photoalbums::lang.plugin.photos',
                'icon'        => 'icon-picture-o',
                'url'         => Backend::url('graker/photoalbums/photos'),
                'permissions' => ['graker.photoalbums.manage_albums'],
              ],
            ],
          ],
        ];
    }


    /**
     *
     * Custom column types definition
     *
     * @return array
     */
    public function registerListColumnTypes() {
        return [
          'is_front' => [$this, 'evalIsFrontListColumn'],
          'image' => [$this, 'evalImageListColumn'],
        ];
    }


    /**
     *
     * Special column to show photo set to be album's front in album's relations list
     *
     * @param $value
     * @param $column
     * @param $record
     * @return string
     */
    public function evalIsFrontListColumn($value, $column, $record) {
        return ($value == $record->id) ? Lang::get('graker.photoalbums::lang.plugin.bool_positive') : '';
    }


    /**
     *
     * Column to render image thumb for Photo model
     *
     * @param $value
     * @param $column
     * @param $record
     * @return string
     */
    function evalImageListColumn($value, $column, $record) {
        if ($record->has('image')) {
            $thumb = $record->image->getThumb(
              isset($column->config['width']) ? $column->config['width'] : 200,
              isset($column->config['height']) ? $column->config['height'] : 200,
              ['mode' => 'auto']
            );
        } else {
            // in case the file attachment was manually deleted for some reason
            $thumb = '';
        }
        return "<img src=\"$thumb\" />";
    }


    /**
     * boot() implementation
     *  - Register listener to markdown.parse
     *  - Add button to blog post form to insert photos from albums
     */
    public function boot() {
        Event::listen('markdown.parse', 'Graker\PhotoAlbums\Classes\MarkdownPhotoInsert@parse');
        $this->extendBlogPostForm();
    }


    /**
     * Extends Blog post form by adding a new button: Insert photo from albums
     */
    protected function extendBlogPostForm() {
        Event::listen('backend.form.extendFields', function (Form $widget) {
            // attach to post forms only
            $controller = $widget->getController();
            if (!($controller instanceof \RainLab\Blog\Controllers\Posts)) {
                return ;
            }
            if (!($widget->model instanceof \RainLab\Blog\Models\Post)) {
                return ;
            }

            // add PhotoSelector widget to Post controller
            $photo_selector = new PhotoSelector($controller);
            $photo_selector->alias = 'photoSelector';
            $photo_selector->bindToController();

            // add javascript extending Markdown editor with new button
            $widget->addJs('/plugins/graker/photoalbums/assets/js/extend-markdown-editor.js');
        });
    }

}
