<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Graker\PhotoAlbums\Models\Photo as PhotoModel;
use Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RandomPhotos extends ComponentBase
{

    public function componentDetails()
    {
        return [
          'name'        => 'graker.photoalbums::lang.components.random_photos',
          'description' => 'graker.photoalbums::lang.components.random_photos_description',
        ];
    }

    public function defineProperties()
    {
        return [
          'photosCount' => [
            'title'             => 'graker.photoalbums::lang.components.photos_count_label',
            'description'       => 'graker.photoalbums::lang.components.photos_count_description',
            'default'           => 5,
            'type'              => 'string',
            'validationMessage' => 'graker.photoalbums::lang.errors.photos_count_error',
            'validationPattern' => '^[0-9]+$',
            'required'          => FALSE,
          ],
          'cacheLifetime' => [
            'title'             => 'graker.photoalbums::lang.components.cache_lifetime_label',
            'description'       => 'graker.photoalbums::lang.components.cache_lifetime_description',
            'default'           => 0,
            'type'              => 'string',
            'validationMessage' => 'graker.photoalbums::lang.errors.cache_lifetime_error',
            'validationPattern' => '^[0-9]+$',
            'required'          => FALSE,
          ],
          'thumbMode' => [
            'title'       => 'graker.photoalbums::lang.components.thumb_mode_label',
            'description' => 'graker.photoalbums::lang.components.thumb_mode_description',
            'type'        => 'dropdown',
            'default'     => 'auto',
          ],
          'thumbWidth' => [
            'title'             => 'graker.photoalbums::lang.components.thumb_width_label',
            'description'       => 'graker.photoalbums::lang.components.thumb_width_description',
            'default'           => 640,
            'type'              => 'string',
            'validationMessage' => 'graker.photoalbums::lang.errors.thumb_width_error',
            'validationPattern' => '^[0-9]+$',
            'required'          => FALSE,
          ],
          'thumbHeight' => [
            'title'             => 'graker.photoalbums::lang.components.thumb_height_label',
            'description'       => 'graker.photoalbums::lang.components.thumb_height_description',
            'default'           => 480,
            'type'              => 'string',
            'validationMessage' => 'graker.photoalbums::lang.errors.thumb_height_error',
            'validationPattern' => '^[0-9]+$',
            'required'          => FALSE,
          ],
          'photoPage' => [
            'title'       => 'graker.photoalbums::lang.components.photo_page_label',
            'description' => 'graker.photoalbums::lang.components.photo_page_description',
            'type'        => 'dropdown',
            'default'     => 'blog/post',
          ],
        ];
    }


    /**
     *
     * Returns pages list for album page select box setting
     *
     * @return mixed
     */
    public function getPhotoPageOptions() {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }


    /**
     *
     * Returns thumb resize mode options for thumb mode select box setting
     *
     * @return array
     */
    public function getThumbModeOptions() {
        return [
          'auto' => 'Auto',
          'exact' => 'Exact',
          'portrait' => 'Portrait',
          'landscape' => 'Landscape',
          'crop' => 'Crop',
        ];
    }


    /**
     *
     * Returns an array of photosCount random photos
     * Array is returned if from Cache, Collection otherwise
     *
     * @return array|Collection
     */
    public function photos() {
        $photos = [];
        if ($this->property('cacheLifetime')) {
            $photos = Cache::get('photoalbums_random_photos');
        }

        if (empty($photos)) {
            $photos = $this->getPhotos();
        }

        return $photos;
    }


    /**
     *
     * Returns a collection of random photos
     *
     * @return Collection
     */
    protected function getPhotos() {
        $count = $this->property('photosCount');
        $photos = PhotoModel::orderBy(DB::raw('RAND()'))
          ->with('image')
          ->take($count)
          ->get();

        foreach ($photos as $photo) {
            $photo->url = $photo->setUrl($this->property('photoPage'), $this->controller);
            $photo->thumb = $photo->image->getThumb(
              $this->property('thumbWidth'),
              $this->property('thumbHeight'),
              ['mode' => $this->property('thumbMode')]
            );
        }

        $this->cachePhotos($photos);

        return $photos;
    }


    /**
     *
     * Cache photos if caching is enabled
     *
     * @param Collection $photos
     */
    protected function cachePhotos($photos) {
        $cache = $this->property('cacheLifetime');
        if ($cache) {
            Cache::put('photoalbums_random_photos', $photos->toArray(), $cache);
        }
    }

}
