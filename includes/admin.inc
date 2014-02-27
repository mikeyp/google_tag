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
  $form['tabs']['general'] = google_tag_general_fieldset($form_state);

  return system_settings_form($form);
}

/**
 * Fieldset builder for the module settings form.
 */
function google_tag_general_fieldset(&$form_state) {
  // Gather data.
  $text = t('sign up for GTM');
  $link = l($text, 'http://www.google.com/tagmanager/web/');

  // Build form elements.
  $fieldset = array(
    '#type' => 'fieldset',
    '#title' => t('General'),
    '#tree' => FALSE,
  );

  $fieldset['google_tag_container_id'] = array(
    '#title' => t('Container ID'),
    '#type' => 'textfield',
    '#default_value' => variable_get('google_tag_container_id', ''),
    '#attributes' => array('placeholder' => array('GTM-xxxxxx')),
    '#size' => 10,
    '#maxlength' => 15,
    '#required' => TRUE,
    '#description' => t('The ID assigned by Google Tag Manager (GTM) for this website container. To get a container ID, !link and create a container for your website.', array('!link' => $link)),
  );

  return $fieldset;
}
