<?php namespace Graker\PhotoAlbums\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Graker\PhotoAlbums\Models\Album as AlbumModel;
use Graker\PhotoAlbums\Models\Photo as PhotoModel;
use Redirect;
use Backend;
use Flash;
use Input;
use Request;
use Response;
use Validator;
use ValidationException;
use ApplicationException;
use System\Models\File;
use Lang;

/**
 * Upload Back-end Controller
 */
class Upload extends Controller
{

    /**
     * Display the form
     */
    public function form() {
        $this->pageTitle = Lang::get('graker.photoalbums::lang.plugin.upload_photos');
        $this->addJs('/modules/backend/assets/vendor/dropzone/dropzone.js');
        $this->addJs('/plugins/graker/photoalbums/assets/js/upload.js');
        $this->addCss('/plugins/graker/photoalbums/assets/css/dropzone.css');
        return $this->makePartial('form');
    }


    /**
     * File upload controller
     */
    public function post_files() {
        try {
            if (!Input::hasFile('file')) {
                throw new ApplicationException(Lang::get('graker.photoalbums::lang.errors.no_file'));
            }

            $upload = Input::file('file');

            $validationRules = ['max:' . File::getMaxFilesize()];

            $validation = Validator::make(
              ['file' => $upload],
              ['file' => $validationRules]
            );
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }
            if (!$upload->isValid()) {
                throw new ApplicationException(Lang::get('graker.photoalbums::lang.errors.invalid_file', ['name' => $upload->getClientOriginalName()]));
            }

            $file = new File;
            $file->data = $upload;
            $file->is_public = true;
            $file->save();
            return Response::json(['id' => $file->id], 200);
        } catch (Exception $e) {
            return Response::json($e->getMessage(), 400);
        }
    }


    /**
     * Form save callback
     */
    public function onSave() {
        $input = Input::all();

        $album = AlbumModel::find($input['album']);
        if ($album && !empty($input['file-id'])) {
            $this->savePhotos($album, $input['file-id'], $input['file-title']);
            Flash::success(Lang::get('graker.photoalbums::lang.messages.photos_saved'));
            return Redirect::to(Backend::url('graker/photoalbums/albums/update/' . $album->id));
        }

        Flash::error(Lang::get('graker.photoalbums::lang.errors.album_not_found'));
        return Redirect::to(Backend::url('graker/photoalbums/albums'));
    }


    /**
     * File remove callback
     */
    public function onFileRemove() {
        if (Input::has('file_id')) {
            $file_id = Input::get('file_id');
            $file = File::find($file_id);
            if ($file) {
                $file->delete();
            }
        }
    }


    /**
     *
     * Saves photos with files attached from $file_ids and attaches them to album
     *
     * @param AlbumModel $album
     * @param array $file_ids
     * @param string[] $file_titles arrray of titles
     */
    protected function savePhotos($album, $file_ids, $file_titles) {
        $files = File::whereIn('id', $file_ids)->get();
        $photos = array();
        foreach ($files as $file) {
            $photo = new PhotoModel();
            $photo->title = isset($file_titles[$file->id]) ? $file_titles[$file->id] : '';
            $photo->save();
            $photo->image()->save($file);
            $photos[] = $photo;
        }
        $album->photos()->saveMany($photos);
    }


    /**
     * @return array of [album id => album title] to use in select list
     */
    protected function getAlbumsList() {
        $albums = AlbumModel::orderBy('created_at', 'desc')->get();
        $options = [];

        foreach ($albums as $album) {
            $options[$album->id] = $album->title;
        }

        return $options;
    }


    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Graker.PhotoAlbums', 'photoalbums', 'upload');
    }
}
