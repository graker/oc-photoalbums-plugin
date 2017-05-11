<?php

namespace Graker\PhotoAlbums\Tests;

use PluginTestCase;
use Graker\PhotoAlbums\Components\RandomPhotos;
use Graker\PhotoAlbums\Models\Photo;
use Graker\PhotoAlbums\Models\Album;
use Cms\Classes\ComponentManager;
use Cms\Classes\Page;
use Cms\Classes\Layout;
use Cms\Classes\Controller;
use Cms\Classes\Theme;
use Cms\Classes\CodeParser;
use Faker;
use Storage;

class RandomPhotosTest extends PluginTestCase {

    /**
     * Tests that random photos are generated
     */
    public function testRandomPhotos() {
        // create album and 7 photos
        $album = $this->createAlbum();
        $photos[] = $this->createPhoto($album);
        $photos[] = $this->createPhoto($album);
        $photos[] = $this->createPhoto($album);
        $photos[] = $this->createPhoto($album);
        $photos[] = $this->createPhoto($album);
        $photos[] = $this->createPhoto($album);
        $photos[] = $this->createPhoto($album);

        // get random photos
        $component = $this->createRandomPhotosComponent();
        $random_photos = $component->photos();

        // assert all photos are from generated array
        self::assertEquals(5, count($random_photos), 'There are 5 random photos');
        $found_all = TRUE;
        foreach ($random_photos as $random_photo) {
            $found = FALSE;
            foreach ($photos as $photo) {
                if ($photo->id == $random_photo->id) {
                    $found = TRUE;
                    break;
                }
            }
            if (!$found) {
                $found_all = FALSE;
                break;
            }
        }
        self::assertTrue($found_all, 'All photos exist in original array');
    }


    /**
     *
     * Creates album model
     *
     * @return \Graker\PhotoAlbums\Models\Album
     */
    protected function createAlbum() {
        $faker = Faker\Factory::create();
        $album = new Album();
        $album->title = $faker->sentence(3);
        $album->slug = str_slug($album->title);
        $album->description = $faker->text();
        $album->save();
        return $album;
    }


    /**
     *
     * Creates photo model and put it into album
     *
     * @param \Graker\PhotoAlbums\Models\Album $album
     * @return \Graker\PhotoAlbums\Models\Photo
     */
    protected function createPhoto(Album $album) {
        $faker = Faker\Factory::create();
        $photo = new Photo();
        $photo->title = $faker->sentence(3);
        $photo->description = $faker->text();
        $photo->image = $faker->image();
        $photo->album = $album;
        $photo->save();
        return $photo;
    }


    /**
     *
     * Creates randomPhotos component to test
     *
     * @return \Graker\PhotoAlbums\Components\RandomPhotos
     */
    protected function createRandomPhotosComponent() {
        // Spoof all the objects we need to make a page object
        $theme = Theme::load('test');
        $page = Page::load($theme, 'index.htm');
        $layout = Layout::load($theme, 'content.htm');
        $controller = new Controller($theme);
        $parser = new CodeParser($page);
        $pageObj = $parser->source($page, $layout, $controller);
        $manager = ComponentManager::instance();
        $object = $manager->makeComponent('randomPhotos', $pageObj);
        return $object;
    }
}
