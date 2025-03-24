<?php

namespace Drupal\infomaniak_newsletter_field\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a settings form for Infomaniak Newsletter Field.
 */
class InfomaniakNewsletterSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'infomaniak_newsletter_field_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['infomaniak_newsletter_field.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('infomaniak_newsletter_field.settings');

    $form['api_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Infomaniak API Settings'),
      '#open' => TRUE,
    ];

    $form['api_settings']['newsletter_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Newsletter Base URL'),
      '#default_value' => $config->get('newsletter_base_url') ?: 'https://api.infomaniak.com/1/',
      '#description' => $this->t('The base URL for the Infomaniak Newsletter API.'),
      '#required' => TRUE,
    ];

    $form['api_settings']['newsletter_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Newsletter Token'),
      '#default_value' => $config->get('newsletter_token'),
      '#description' => $this->t('The API token for authentication with Infomaniak.'),
      '#required' => TRUE,
    ];

    $form['api_settings']['newsletter_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Newsletter Domain'),
      '#default_value' => $config->get('newsletter_domain'),
      '#description' => $this->t('Your domain configured in Infomaniak Newsletter.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('infomaniak_newsletter_field.settings')
      ->set('newsletter_base_url', $form_state->getValue('newsletter_base_url'))
      ->set('newsletter_token', $form_state->getValue('newsletter_token'))
      ->set('newsletter_domain', $form_state->getValue('newsletter_domain'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
