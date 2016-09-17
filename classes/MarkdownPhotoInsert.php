<?php
/**
 * Created by PhpStorm.
 * User: graker
 * Date: 17.09.16
 * Time: 20:37
 */

namespace Graker\PhotoAlbums\Classes;

use Graker\PhotoAlbums\Models\Photo;

/**
 * Class MarkdownPhotoInsert
 * Parses Markdown text to replace
 *
 * @package Graker\PhotoAlbums\Classes
 */
class MarkdownPhotoInsert {

  const PHOTO_A_REGEXP = '(href=\"(\[photo\:[0-9]+\])\")';
  const PHOTO_IMG_REGEXP = '(src=\"(\[photo\:[0-9]+\])\")';

  /**
   *
   * Replace [photo:123] placeholders with links to actual images from photo gallery
   * where "photo" is a string and 123 is a photo id
   * Placeholders are allowed (i.e. replaced) only inside `src=""` and `href=""`
   *
   * @param string $text original text
   * @param object $data contains text property with text processed so far
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
   * (replaces [photo:id] with photo's image url)
   *
   * @param $entry
   * @param $placeholder
   * @return string
   */
  protected function getReplacement($entry, $placeholder) {
    list($tag, $id) = explode(':', $placeholder);
    $id = str_replace(']', '', $id);
    $photo = Photo::find($id)->with('image');
    return str_replace($placeholder, $photo->image->path, $entry);
  }

}
