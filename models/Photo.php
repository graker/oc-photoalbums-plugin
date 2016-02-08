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

}
