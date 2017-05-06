<?php namespace Graker\PhotoAlbums\Models;

use Illuminate\Database\Eloquent\Collection;
use Model;

/**
 * Photo Model
 */
class Photo extends Model
{

    // Photos must be sortable
    use \October\Rain\Database\Traits\Sortable;

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
     *
     * @return Photo
     */
    public function nextPhoto() {
        $next = NULL;
        $current_found = FALSE;

        foreach ($this->album->photos as $photo) {
            if ($current_found) {
                // previous iteration was current photo, so we found the next one
                $next = $photo;
                break;
            }
            if ($photo->id == $this->id) {
                $current_found = TRUE;
            }
        }

        return $next;
    }


    /**
     *
     * Returns previous photo or NULL if this is the first in the album
     *
     * @return Photo
     */
    public function previousPhoto() {
        $previous = NULL;

        foreach ($this->album->photos as $photo) {
            if ($photo->id == $this->id) {
                // found current photo
                break;
            } else {
                $previous = $photo;
            }
        }

        return $previous;
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
