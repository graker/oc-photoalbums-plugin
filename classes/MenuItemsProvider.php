<?php

namespace Graker\PhotoAlbums\Classes;

use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use RainLab\Pages\Classes\MenuItem;
use Graker\PhotoAlbums\Models\Album;
use Graker\PhotoAlbums\Models\Photo;
use Url;

/**
 * Class MenuItemsProvider
 * RainLab.Pages plugin integration (for menu items and/or XML sitemap)
 */
class MenuItemsProvider {

    /**
     *
     * Returns array of items info for sitemap
     *
     * @return array
     */
    public static function listTypes() {
        return [
          // TODO localize
          'all-photo-albums' => 'All Photo Albums',
          'all-photos' => 'All Photos',
          'photo-album' => 'Photo Album',
        ];
    }


    /**
     *
     * Returns an array of info about menu item type
     *
     * @param string $type item name
     * @return array
     */
    public static function getMenuTypeInfo($type) {
        switch ($type) {
            case 'all-photo-albums' :
                $result = self::getAllAlbumsInfo();
                break;
            case 'all-photos' :
                $result = self::getAllPhotosInfo();
                break;
            case 'photo-album' :
                $result = self::getSingleAlbumInfo();
                break;
            default:
                $result = [];
        }

        return $result;
    }


    /**
     *
     * Returns information about a menu item
     *
     * @param string $type
     * @param MenuItem $item
     * @param string $url
     * @param Theme $theme
     * @return array
     */
    public static function resolveMenuItem($type, $item, $url, $theme) {
        $result = [];

        switch ($type) {
            case 'all-photo-albums' :
                $result = self::resolveAllAlbumsItem($item, $url, $theme);
                break;
            case 'all-photos' :
                $result = self::resolveAllPhotosItem($item, $url, $theme);
                break;
            case 'photo-album' :
                $result = self::resolveSingleAlbumItem($item, $url, $theme);
                break;
            default:
                $result = [];
        }

        return $result;
    }


    /**
     *
     * Generates url for the item to be resolved
     *
     * @param int $year - year number
     * @param string $pageCode - page code to be used
     * @param $theme
     * @return string
     */
    protected static function getUrl($year, $pageCode, $theme) {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return '';

        $properties = $page->getComponentProperties('blogArchive');
        if (!isset($properties['yearParam'])) {
            return '';
        }

        // get year url param and strip it of {{ :<name> }} to get pure name
        $paramName = str_replace(array('{', '}', ' ', ':'), '', $properties['yearParam']);
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $year]);

        return $url;
    }


    /**
     *
     * Returns menu type info for all-photo-albums menu item
     *
     * @return array
     */
    protected static function getAllAlbumsInfo() {
        $result = ['dynamicItems' => TRUE,];
        $result['cmsPages'] = self::getCmsPages('photoAlbum');
        return $result;
    }


    /**
     *
     * Returns menu type info for all-photo-albums menu item
     *
     * @return array
     */
    protected static function getSingleAlbumInfo() {
        $result = [
          'dynamicItems' => FALSE,
          'nesting' => FALSE,
        ];
        $result['cmsPages'] = self::getCmsPages('photoAlbum');

        $references = [];
        $albums = Album::all();
        foreach ($albums as $album) {
            $references[$album->id] = $album->title;
        }
        $result['references'] = $references;

        return $result;
    }


    /**
     *
     * Returns menu type info for all-photos menu item
     *
     * @return array
     */
    protected static function getAllPhotosInfo() {
        $result = ['dynamicItems' => true,];
        $result['cmsPages'] = self::getCmsPages('singlePhoto');
        return $result;
    }


    /**
     *
     * Return array of Cms pages having $component attached
     *
     * @param string $component
     * @return array
     */
    protected static function getCmsPages($component) {
        $theme = Theme::getActiveTheme();

        $pages = CmsPage::listInTheme($theme, true);
        $cmsPages = [];

        foreach ($pages as $page) {
            if (!$page->hasComponent($component)) {
                continue;
            }

            $cmsPages[] = $page;
        }

        return $cmsPages;
    }


    /**
     *
     * Resolves All Albums menu item
     *
     * @param MenuItem $item
     * @param string $url
     * @param Theme $theme
     * @return array
     */
    protected static function resolveAllAlbumsItem($item, $url, $theme) {
        $result = [
          'items' => [],
        ];

        $albums = Album::all();
        foreach ($albums as $album) {
            $item = [
                'title' => $album->title,
                'url' => self::getAlbumUrl($album, $item->cmsPage, $theme),
                'mtime' => $album->updated_at,
            ];
            $item['isActive'] = ($item['url'] == $url);
            $result['items'][] = $item;
        }

        return $result;
    }


    /**
     *
     * Resolves single Album menu item
     *
     * @param MenuItem $item
     * @param string $url
     * @param Theme $theme
     * @return array
     */
    protected static function resolveSingleAlbumItem($item, $url, $theme) {
        $result = [];

        if (!$item->reference || !$item->cmsPage) {
            return [];
        }

        $album = Album::find($item->reference);
        if (!$album) {
            return [];
        }

        $pageUrl = self::getAlbumUrl($album, $item->cmsPage, $theme);
        if (!$pageUrl) {
            return [];
        }
        $pageUrl = Url::to($pageUrl);

        $result['url'] = $pageUrl;
        $result['isActive'] = ($pageUrl == $url);
        $result['mtime'] = $album->updated_at;

        return $result;
    }


    /**
     *
     * Resolves All Photos menu item
     *
     * @param MenuItem $item
     * @param string $url
     * @param Theme $theme
     * @return array
     */
    protected static function resolveAllPhotosItem($item, $url, $theme) {
        $result = [
          'items' => [],
        ];

        $photos = Photo::all();
        foreach ($photos as $photo) {
            $item = [
              'title' => $photo->title,
              'url' => self::getPhotoUrl($photo, $item->cmsPage, $theme),
              'mtime' => $photo->updated_at,
            ];
            $item['isActive'] = ($item['url'] == $url);
            $result['items'][] = $item;
        }

        return $result;
    }


    /**
     *
     * Generates url for album
     *
     * @param Album $album
     * @param string $pageCode
     * @param Theme $theme
     * @return string
     */
    protected static function getAlbumUrl($album, $pageCode, $theme) {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return '';

        $properties = $page->getComponentProperties('photoAlbum');
        if (!isset($properties['slug'])) {
            return '';
        }

        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['slug'], $matches)) {
            return '';
        }

        $paramName = substr(trim($matches[1]), 1);
        $params = [
          $paramName => $album->slug,
          'id' => $album->id,
        ];
        $url = CmsPage::url($page->getBaseFileName(), $params);

        return $url;
    }


    /**
     *
     * Generates url for photo
     *
     * @param Photo $photo
     * @param string $pageCode
     * @param Theme $theme
     * @return string
     */
    protected static function getPhotoUrl($photo, $pageCode, $theme) {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return '';

        $properties = $page->getComponentProperties('singlePhoto');
        if (!isset($properties['id'])) {
            return '';
        }

        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['id'], $matches)) {
            return '';
        }

        $paramName = substr(trim($matches[1]), 1);
        $params = [
          $paramName => $photo->id,
        ];
        $url = CmsPage::url($page->getBaseFileName(), $params);

        return $url;
    }

}
