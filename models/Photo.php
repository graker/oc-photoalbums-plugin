<?php namespace Graker\PhotoAlbums\Models;

use Model;

/**
 * Photo Model
 */
class Photo extends Model
{

  /**
   * @var string The database table used by the model.
   */
  public $table = 'graker_photoalbums_photos';

  /**
   * @var array of validation rules
   */
  public $rules = [
    'title' => 'required',
  ];

  /**
   * @var array of fillable fields to use in mass assignment
   */
  protected $fillable = [
    'title', 'description',
  ];

  /**
   * @var array Relations
   */
  public $belongsTo = [
    'user' => ['Backend\Models\User'],
    'album' => ['Graker\PhotoAlbums\Models\Album'],
  ];
  public $attachOne = [
    'image' => ['System\Models\File'],
  ];


  /**
   *
   * Returns next photo or NULL if this is the last in the album
   * TODO when there is a mass uploader implemented, we have to invent better sorting since created_at will be equal for multiple loaded photos
   *
   * @return Photo
   */
  public function nextPhoto() {
    $photo = Photo::where('created_at', '>', $this->created_at)
      ->where('album_id', '=', $this->album_id)
      ->orderBy('created_at', 'asc')
      ->first();
    return $photo;
  }


  /**
   *
   * Returns previous photo or NULL if this is the first in the album
   *
   * @return Photo
   */
  public function previousPhoto() {
    $photo = Photo::where('created_at', '<', $this->created_at)
      ->where('album_id', '=', $this->album_id)
      ->orderBy('created_at', 'desc')
      ->first();
    return $photo;
  }


  /**
   *
   * Sets and returns url for this model using provided page name and controller
   * For now we expose photo id and album's slug
   *
   * @param string $pageName
   * @param CMS\Classes\Controller $controller
   * @return string
   */
  public function setUrl($pageName, $controller) {
    $params = [
      'id' => $this->id,
      'album_slug' => $this->album->slug,
    ];

    return $this->url = $controller->pageUrl($pageName, $params);
  }


  /**
   * beforeDelete() event
   * Using it to delete attached
   */
  public function beforeDelete() {
    if ($this->image) {
      $this->image->delete();
    }
  }

}
