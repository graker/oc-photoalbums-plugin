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

}
