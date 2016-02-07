<?php namespace Graker\Photoalbums\Models;

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
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}