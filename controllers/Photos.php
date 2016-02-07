<?php namespace Graker\PhotoAlbums\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Photos Back-end Controller
 */
class Photos extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Graker.PhotoAlbums', 'photoalbums', 'photos');
    }
}