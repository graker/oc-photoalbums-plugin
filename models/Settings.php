<?php

/**
 * PhotoAlbums settings model
 */

namespace Graker\PhotoAlbums\Models;

use Model;

class Settings extends Model {

    public $implement = ['System.Behaviors.SettingsModel '];

    /**
     * @var string unique code to access settings
     */
    public $settingsCode = 'photoalbums_settings';

    /**
     * @var string file with setting fields
     */
    public $settingsFields = 'fields.yaml';

}
