<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\ComponentBase;

class Album extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Album Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}