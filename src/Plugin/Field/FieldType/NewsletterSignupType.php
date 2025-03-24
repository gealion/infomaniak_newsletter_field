<?php

namespace Drupal\infomaniak_newsletter_field\Plugin\Field\FieldType;

use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'newsletter_signup' field type.
 */
#[FieldType(
  id: "newsletter_signup",
  label: new TranslatableMarkup("Newsletter Signup"),
  description: new TranslatableMarkup("Creates a newsletter signup form"),
  default_widget: "newsletter_signup_widget",
  default_formatter: "newsletter_signup_formatter"
)]
class NewsletterSignupType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['mailing_list_id'] = DataDefinition::create('string')
      ->setLabel(t('Mailing List ID'))
      ->setRequired(TRUE);

    $properties['submit_text'] = DataDefinition::create('string')
      ->setLabel(t('Submit Button Text'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'mailing_list_id' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'submit_text' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->get('mailing_list_id')->getValue());
  }

}
