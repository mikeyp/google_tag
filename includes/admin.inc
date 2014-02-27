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
  $form['tabs']['paths'] = google_tag_path_fieldset($form_state);
  $form['tabs']['roles'] = google_tag_role_fieldset($form_state);

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

/**
 * Fieldset builder for the module settings form.
 *
 * Allows user to specify snippet insertion condition based on page path.
 */
function google_tag_path_fieldset(&$form_state) {
  // Gather data.
  $description = t('On this and the following tab, specify the conditions on which the GTM JavaScript snippet will either be included in or excluded from the page response, thereby enabling or disabling tracking and other analytics.');
  $description .= t(' All conditions must be satisfied for the snippet to be included. The snippet will be excluded if any condition is not met.<br /><br />');
  $description .= t(' On this tab, specify the path condition.');

  $list_description = t('Enter one relative path per line using the "*" character as a wildcard. Example paths are: "%blog" for the blog page, "%blog-wildcard" for each individual blog, and "%front" for the front page.', array('%blog' => 'blog', '%blog-wildcard' => 'blog/*', '%front' => '<front>'));

  // Build form elements.
  $fieldset = array(
    '#type' => 'fieldset',
    '#title' => t('Page paths'),
    '#description' => $description,
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => FALSE,
  );

  $fieldset['google_tag_path_toggle'] = array(
    '#type' => 'radios',
    '#title' => t('Add snippet on specific paths'),
    '#options' => array(
      t('All paths except the listed paths'),
      t('Only the listed paths'),
    ),
    '#default_value' => variable_get('google_tag_path_toggle', 0),
  );
  $fieldset['google_tag_path_list'] = array(
    '#type' => 'textarea',
    '#title' => t('Listed paths'),
    '#description' => $list_description,
    '#default_value' => variable_get('google_tag_path_list', GOOGLETAGMANAGER_PATHS),
    '#rows' => 10,
  );

  return $fieldset;
}

/**
 * Fieldset builder for the module settings form.
 *
 * Allows user to specify snippet insertion condition based on user role.
 */
function google_tag_role_fieldset(&$form_state) {
  // Gather data.
  $roles = array_map('check_plain', user_roles());

  // Build form elements.
  $fieldset = array(
    '#type' => 'fieldset',
    '#title' => t('User roles'),
    '#description' => t('On this tab, specify the user role condition.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => FALSE,
  );

  $fieldset['google_tag_role_toggle'] = array(
    '#type' => 'radios',
    '#title' => t('Add snippet for specific roles'),
    '#options' => array(
      t('All roles except the selected roles'),
      t('Only the selected roles'),
    ),
    '#default_value' => variable_get('google_tag_role_toggle', 0),
  );

  $fieldset['google_tag_role_list'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Selected roles'),
    '#default_value' => variable_get('google_tag_role_list', array()),
    '#options' => $roles,
  );

  return $fieldset;
}
