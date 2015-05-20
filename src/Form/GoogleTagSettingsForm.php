<?php

/**
 * @file
 * Contains \Drupal\google_tag\Form\GoogleTagSettingsform.
 */

namespace Drupal\google_tag\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GoogleTagSettingsForm
 * @package Drupal\google_tag\Form
 */
class GoogleTagSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_tag_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['google_tag.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('google_tag.settings');

    // Build form elements.
    $form['settings'] = [
      '#type' => 'vertical_tabs',
      '#attributes' => ['class' => ['google-tag']],
      '#attached' => [
        'library' => ['google_tag/drupal.settings_form'],
      ],
    ];

    // General tab
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#group' => 'settings',
    ];

    $form['general']['google_tag_container_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Container ID'),
      '#description' => $this->t('The ID assigned by Google Tag Manager (GTM) for this website container. To get a container ID, <a href="http://www.google.com/tagmanager/web/">sign up for GTM</a> and create a container for your website.'),
      '#default_value' => '', //variable_get('google_tag_container_id', ''),
      '#attributes' => ['placeholder' => ['GTM-xxxxxx']],
      '#size' => 11,
      '#maxlength' => 15,
      '#required' => TRUE,
    ];

    // Page paths tab
    $description = $this->t('On this and the following tab, specify the conditions on which the GTM JavaScript snippet will either be included in or excluded from the page response, thereby enabling or disabling tracking and other analytics.');
    $description .= $this->t(' All conditions must be satisfied for the snippet to be included. The snippet will be excluded if any condition is not met.<br /><br />');
    $description .= $this->t(' On this tab, specify the path condition.');

    $form['paths'] = [
      '#type' => 'details',
      '#title' => $this->t('Page paths'),
      '#group' => 'settings',
      '#description' => $description,
    ];

    $form['paths']['google_tag_path_toggle'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add snippet on specific paths'),
      '#options' => [
        $this->t('All paths except the listed paths'),
        $this->t('Only the listed paths'),
      ],
      '#default_value' => 0, //variable_get('google_tag_path_toggle', 0),
    ];
    $form['paths']['google_tag_path_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Listed paths'),
      '#description' => $this->t('Enter one relative path per line using the "*" character as a wildcard. Example paths are: "%blog" for the blog page, "%blog-wildcard" for each individual blog, and "%front" for the front page.', ['%blog' => 'blog', '%blog-wildcard' => 'blog/*', '%front' => '<front>']),
      '#default_value' => '', //variable_get('google_tag_path_list', GOOGLETAGMANAGER_PATHS),
      '#rows' => 10,
    ];

    // User roles tab
    $form['roles'] = [
      '#type' => 'details',
      '#title' => $this->t('User roles'),
      '#description' => $this->t('On this tab, specify the user role condition.'),
      '#group' => 'settings',
    ];

    $form['roles']['google_tag_role_toggle'] = [
      '#type' => 'radios',
      '#title' => t('Add snippet for specific roles'),
      '#options' => [
        t('All roles except the selected roles'),
        t('Only the selected roles'),
      ],
      '#default_value' => 0, //variable_get('google_tag_role_toggle', 0),
    ];

    $user_roles = array_map(function($role) {
      return $role->label();
    }, user_roles());

    $form['roles']['google_tag_role_list'] = [
      '#type' => 'checkboxes',
      '#title' => t('Selected roles'),
      '#default_value' => [], //variable_get('google_tag_role_list', array()),
      '#options' => $user_roles,
    ];

    // Status tab
    $list_description = t('Enter one response status per line. For more information, refer to the <a href="http://en.wikipedia.org/wiki/List_of_HTTP_status_codes">list of HTTP status codes</a>.');

    $form['statuses'] = [
      '#type' => 'details',
      '#title' => $this->t('Response statuses'),
      '#group' => 'settings',
      '#description' => t('On this tab, specify the page response status condition. If enabled, this condition overrides the page path condition. In other words, if the HTTP response status is one of the listed statuses, then the page path condition is ignored.'),
    ];


    $form['statuses']['google_tag_status_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override path condition for listed response statuses'),
      '#description' => $this->t('If checked, then the path condition will be ingored for a listed page response status.'),
      '#default_value' => 0, //variable_get('google_tag_status_toggle', 0),
    ];

    $form['statuses']['google_tag_status_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Listed statuses'),
      '#description' => $list_description,
      '#default_value' => '', //variable_get('google_tag_status_list', GOOGLETAGMANAGER_STATUSES),
      '#rows' => 5,
    ];

    // Advanced tab
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#group' => 'settings',
    ];

    $form['advanced']['google_tag_compact_tag'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Compact the JavaScript snippet'),
      '#description' => $this->t('If checked, then the JavaScript snippet will be compacted to remove unnecessary whitespace. This is <strong>recommended on production sites</strong>. Leave unchecked to output a snippet that can be examined using a JavaScript debugger in the browser.'),
      '#default_value' => 1, //variable_get('google_tag_compact_tag', 1),
    ];



    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Trim the text values.
    $values['google_tag_container_id'] = trim($values['google_tag_container_id']);
    $values['google_tag_path_list'] = trim($values['google_tag_path_list']);
    $values['google_tag_status_list'] = trim($values['google_tag_status_list']);

    // Replace all types of dashes (n-dash, m-dash, minus) with a normal dash.
    $values['google_tag_container_id'] = str_replace(['–', '—', '−'], '-', $values['google_tag_container_id']);

    if (!preg_match('/^GTM-\w{4,}$/', $values['google_tag_container_id'])) {
      // @todo Is there a more specific regular expression that applies?
      // @todo Is there a way to "test the connection" to determine a valid ID for
      // a container? It may be valid but not the correct one for the website.
      form_set_error('google_tag_container_id', t('A valid container ID is case sensitive and formatted like GTM-xxxxxx.'));
    }


    parent::validateForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {


    parent::submitForm($form, $form_state);
  }


}
