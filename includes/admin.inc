<?php

/**
 * @file
 * Contains the administrative page and form callbacks.
 *
 * @author Jim Berry ("solotandem", http://drupal.org/user/240748)
 */

/**
 * Form constructor for the module settings form.
 *
 * @see google_tag_settings_form_validate()
 *
 * @ingroup forms
 */
function google_tag_settings_form($form, &$form_state) {
  // Gather data.
  // @todo Set this on validation errors.
  $default_tab = !empty($form_state['default_tab']) ? $form_state['default_tab'] : '';

  // Build form elements.
  $form['tabs'] = array(
    '#type' => 'vertical_tabs',
    '#default_tab' => $default_tab ? $default_tab : 'edit-general',
    '#attributes' => array('class' => array('google-tag')),
  );

  return system_settings_form($form);
}
