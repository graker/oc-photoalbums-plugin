<?php

namespace Graker\PhotoAlbums\Classes;

use Graker\PhotoAlbums\Models\Photo;

/**
 * Class MarkdownPhotoInsert
 * Parses Markdown text to replace [photo:123:640:480:auto] placeholders with links to actual images from photo gallery
 *
 * @package Graker\PhotoAlbums\Classes
 */
class MarkdownPhotoInsert {

    const PHOTO_A_REGEXP = '(href=\"(\[photo\:[0-9]+(?:\:[0-9]+\:[0-9]+(?:\:auto|\:exact|\:portrait|\:landscape|\:crop)?)?\])\")';
    const PHOTO_IMG_REGEXP = '(src=\"(\[photo\:[0-9]+(?:\:[0-9]+\:[0-9]+(?:\:auto|\:exact|\:portrait|\:landscape|\:crop)?)?\])\")';

    /**
     *
     * Replace [photo:123:640:480:auto] placeholders with links to actual images from photo gallery
     * where
     *   - photo is a constant string
     *   - 123 is a photo id
     *   - 640:480 is an optional size of thumbnail to be created
     *   - auto is an optional crop mode to be used
     * Placeholders are allowed (i.e. replaced) only inside `src=""` and `href=""`
     *
     * @param string $text original text
     * @param \stdClass $data contains text property with text processed so far
     */
    public function parse($text, $data) {
        $images = array();
        $links = array();

        preg_match_all(self::PHOTO_A_REGEXP, $data->text, $links, PREG_SET_ORDER);
        preg_match_all(self::PHOTO_IMG_REGEXP, $data->text, $images, PREG_SET_ORDER);

        if (!empty($images)) {
            $data->text = $this->replaceMatches($images, $data->text);
        }

        if (!empty($links)) {
            $data->text = $this->replaceMatches($links, $data->text);
        }
    }


    /**
     *
     * Goes over all matches and replaces them in text
     * Returns processed text
     *
     * @param $matches
     * @param $text
     * @return mixed
     */
    protected function replaceMatches($matches, $text) {
        foreach ($matches as $match) {
            list($entry, $placeholder) = $match;
            $replacement = $this->getReplacement($entry, $placeholder);
            $text = str_replace($entry, $replacement, $text);
        }
        return $text;
    }


    /**
     *
     * Returns replacement for text
     * (replaces [photo:id:width:height:mode] with resulting photo's image path)
     *
     * @param $entry
     * @param $placeholder
     * @return string
     */
    protected function getReplacement($entry, $placeholder) {
        list($id, $width, $height, $mode) = $this->getPhotoParams($placeholder);
        $photo = Photo::where('id', $id)
          ->with('image')
          ->first();
        if (!$photo) {
            return $placeholder;
        } else {
            if ($width && $height) {
                $path = $photo->image->getThumb($width, $height, ['mode' => $mode]);
            } else {
                $path = $photo->image->path;
            }
            return str_replace($placeholder, $path, $entry);
        }
    }


    /**
     *
     * Parses parameters of image from the tag and returns them in array
     * [$id, $width, $height, $mode]
     * Width, height and mode are optional and will return 0 and empty string
     * if omitted in the tag
     *
     * @param string $placeholder
     * @return array
     */
    protected function getPhotoParams($placeholder) {
        // remove brackets
        $values = str_replace('[', '', $placeholder);
        $values = str_replace(']', '', $values);
        // get parameters
        $values = explode(':', $values);
        $id = $values[1];
        $width = isset($values[2]) ? $values[2] : 0;
        $height = isset($values[3]) ? $values[3] : 0;
        $mode = isset($values[4]) ? $values[4] : 'auto';
        return array($id, $width, $height, $mode);
    }

}
