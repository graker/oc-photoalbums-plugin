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

/**
 * Upload Back-end Controller
 */
class Upload extends Controller
{

  /**
   * Display the form
   */
  public function form() {
    $this->pageTitle = 'Upload photos';
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
        throw new ApplicationException('No file in request');
      }

      $upload = Input::file('file');

      $validationRules = ['max:'.File::getMaxFilesize()];

      $validation = Validator::make(
        ['file' => $upload],
        ['file' => $validationRules]
      );
      if ($validation->fails()) {
        throw new ValidationException($validation);
      }
      if (!$upload->isValid()) {
        throw new ApplicationException(sprintf('File %s is not valid.', $upload->getClientOriginalName()));
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
      $this->savePhotos($album, $input['file-id']);
      Flash::success('Photos are saved!');
      return Redirect::to(Backend::url('graker/photoalbums/albums/update/' . $album->id));
    }

    Flash::error('Album was not found.');
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
   */
  protected function savePhotos($album, $file_ids) {
    $files = File::whereIn('id', $file_ids)->get();
    $photos = array();
    foreach ($files as $file) {
      $photo = new PhotoModel();
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
