<?php namespace Graker\PhotoAlbums\Components;

use Cms\Classes\ComponentBase;
use Graker\PhotoAlbums\Models\Photo as PhotoModel;

class Photo extends ComponentBase
{

  public $photo;

  public function componentDetails()
  {
    return [
      'name'        => 'Photo',
      'description' => 'Single photo component'
    ];
  }

  /**
   *
   * Properties of component
   *
   * @return array
   */
  public function defineProperties()
  {
    return [
      'id' => [
        'title'       => 'ID',
        'description' => 'URL id parameter',
        'default'     => '{{ :id }}',
        'type'        => 'string'
      ],
    ];
  }


  /**
   * Loads photo on onRun event
   */
  public function onRun() {
    $this->photo = $this->loadPhoto();
  }


  /**
   *
   * Loads photo to be displayed in this component
   *
   * @return PhotoModel
   */
  protected function loadPhoto() {
    $id = $this->property('id');
    $photo = PhotoModel::where('id', $id)->with('image')->first();
    return $photo;
  }

}
