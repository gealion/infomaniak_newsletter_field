<?php

namespace Drupal\infomaniak_newsletter_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\infomaniak_newsletter_field\Services\NewsletterApiServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'newsletter_signup_widget' widget.
 */
#[FieldWidget(
  id: "newsletter_signup_widget",
  label: new TranslatableMarkup("Newsletter signup widget"),
  field_types: ["newsletter_signup"]
)]
class NewsletterSignupWidget extends WidgetBase {

  /**
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\infomaniak_newsletter_field\Services\NewsletterApiServiceInterface $newsletterApiService
   *   Custom Service for Interacting with Newsletter API.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    protected NewsletterApiServiceInterface $newsletterApiService,
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('infomaniak_newsletter_field.infomaniak_v2')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['mailing_list_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Mailing List ID'),
      '#default_value' => $items[$delta]->mailing_list_id ?? '',
      '#options' => $this->newsletterApiService->getGroupsOptions(),
      '#required' => TRUE,
      '#description' => $this->t('Select the ailing list ID'),
    ];

    $element['submit_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submit Button Text'),
      '#default_value' => $items[$delta]->submit_text ?? $this->t('Subscribe'),
      '#required' => TRUE,
    ];

    return $element;
  }

}
