<?php namespace Graker\PhotoAlbums\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Input;
use Graker\PhotoAlbums\Models\Album;
use Graker\PhotoAlbums\Models\Photo;
use ApplicationException;
use Response;
use Request;

use League\Flysystem\Exception;

/**
 * Albums Back-end Controller
 */
class Albums extends Controller
{
  public $implement = [
    'Backend.Behaviors.FormController',
    'Backend.Behaviors.ListController',
    'Backend.Behaviors.RelationController',
  ];

  public $formConfig = 'config_form.yaml';
  public $listConfig = 'config_list.yaml';
  public $relationConfig = 'config_relation.yaml';

  public function __construct()
  {
    parent::__construct();

    BackendMenu::setContext('Graker.PhotoAlbums', 'photoalbums', 'albums');
  }


  /**
   * Ajax callback to set Photo as Album's front photo
   *
   * @param null|int $recordId album id
   * @return string relation refresh or error string in json if there's error
   */
  public function update_onRelationButtonSetFront($recordId = NULL) {
    // get album
    $album = Album::find($recordId);
    if (!$album) {
      // album not found
      return Response::json('Album not found!', 400);
    }

    // get first checked photo
    $input = Input::all();
    $checked = $input['checked'];
    $photo_id = array_shift($checked);

    // validate photo
    $photo = Photo::find($photo_id);
    try {
      if (!$photo) {
        throw new ApplicationException('Can\'t find selected photo!');
      }
      if ($photo->album_id != $album->id) {
        // attempt to use other album's photo
        throw new ApplicationException('Selected photo doesn\'t belong to this album!');
      }
    } catch (Exception $e) {
      return Response::json($e->getMessage(), 400);
    }

    // set front id
    $album->front_id = $photo->id;
    $album->save();

    $this->initRelation($album, 'photos');
    return $this->relationRefresh('photos');
  }


  /**
   *
   * Returns path to reorder current album (should be in formModel)
   *
   * @return string
   */
  protected function getReorderPath() {
    if (!isset($this->vars['formModel']->id)) {
      return '';
    }

    $uri = \Backend::url('graker/photoalbums/reorder/album/' . $this->vars['formModel']->id);
    return $uri;
  }

}
