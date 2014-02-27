<?php

/**
 * @file
 * Provides info-type hook implementations that are infrequently called.
 *
 * @author Jim Berry ("solotandem", http://drupal.org/user/240748)
 */

/**
 * Implements hook_help().
 */
function _google_tag_help($path, $arg) {
  switch ($path) {
    case 'admin/help#google_tag':
    case 'admin/config/system/google_tag':
      $text = t('Google Tag Manager');
      $link = l($text, 'http://www.google.com/tagmanager/');
      return t('!link is a free service (registration required) to manage the insertion of tags for capturing website analytics.', array('!link' => $link));
  }
}
