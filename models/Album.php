<?php namespace Graker\PhotoAlbums\Models;

use Model;

/**
 * Album Model
 */
class Album extends Model
{

  /**
   * @var string The database table used by the model.
   */
  public $table = 'graker_photoalbums_albums';

  /**
   * @var array of validation rules
   */
  public $rules = [
    'title' => 'required',
    'slug' => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:graker_photoalbums_albums'],
  ];

  /**
   * @var array Relations
   */
  public $hasMany = [
    'photos' => 'Graker\PhotoAlbums\Models\Photo'
  ];
  public $belongsTo = [
    'user' => ['Backend\Models\User'],
  ];


  /**
   *
   * This relation allows us to eager-load 1 latest photo per album
   *
   * @return mixed
   */
  public function latestPhoto() {
    return $this->hasOne('Graker\PhotoAlbums\Models\Photo')->latest();
  }

}
