/**
 * @file
 * Behaviors and utility functions for administrative pages.
 *
 * @author Jim Berry ("solotandem", http://drupal.org/user/240748)
 */

(function ($) {

  "use strict";

  /**
   * Provides summary information for the vertical tabs.
   */
  Drupal.behaviors.gtmInsertionSettings = {
    attach: function (context) {
      var $context = $(context);

      $context.find('#edit-paths').drupalSetSummary(function (context) {
        var $radio = $context.find('input[name="google_tag_path_toggle"]:checked');
        if ($radio.val() == 0) {
          if (!$context.find('textarea[name="google_tag_path_list"]').val()) {
            return Drupal.t('All paths');
          }
          else {
            return Drupal.t('All paths except listed paths');
          }
        }
        else {
          if (!$context.find('textarea[name="google_tag_path_list"]').val()) {
            return Drupal.t('No paths');
          }
          else {
            return Drupal.t('Only listed paths');
          }
        }
      });

      $context.find('#edit-roles').drupalSetSummary(function (context) {
        var vals = [];
        $('input[type="checkbox"]:checked', context).each(function () {
          vals.push($.trim($(this).next('label').text()));
        });
        var $radio = $('input[name="google_tag_role_toggle"]:checked', context);
        if ($radio.val() == 0) {
          if (!vals.length) {
            return Drupal.t('All roles');
          }
          else {
            return Drupal.t('All roles except selected roles');
          }
        }
        else {
          if (!vals.length) {
            return Drupal.t('No roles');
          }
          else {
            return Drupal.t('Only selected roles');
          }
        }
      });

      $context.find('#edit-statuses').drupalSetSummary(function (context) {
        var $checkbox = $context.find('input[name="google_tag_status_toggle"]:checked');
        if ($checkbox.is(':checked')) {
          if (!$context.find('textarea[name="google_tag_status_list"]').val()) {
            return Drupal.t('No statuses');
          }
          else {
            return Drupal.t('Listed statuses');
          }
        }
        else {
          return Drupal.t('No statuses');
        }
      });
    }
  };

})(jQuery);
