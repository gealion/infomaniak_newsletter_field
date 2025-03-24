<?php

namespace Drupal\infomaniak_newsletter_field\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\infomaniak_newsletter_field\Services\NewsletterApiServiceInterface;

/**
 * Service for handling newsletter subscription operations.
 */
class NewsletterValidationService {
  use StringTranslationTrait;

  /**
   * Constructs a NewsletterService object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   The mail manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Drupal\infomaniak_newsletter_field\Services\NewsletterApiServiceInterface $newsletterApiService
   *   The Newsletter API Connector Service.
   */
  public function __construct(
    protected Connection $database,
    protected MailManagerInterface $mailManager,
    protected LanguageManagerInterface $languageManager,
    protected ConfigFactoryInterface $configFactory,
    protected NewsletterApiServiceInterface $newsletterApiService,
  ) {
  }

  /**
   * Creates a new newsletter subscription.
   *
   * @param string $email
   *   The subscriber's email address.
   * @param string $mailinglist_id
   *   The ID of the mailing list.
   *
   * @return array|bool
   *   The subscription data if successful, FALSE if it fails.
   */
  public function createSubscription(string $email, string $mailinglist_id): array|bool {
    try {
      $timestamp = time();

      $fields = [
        'email' => $email,
        'mailinglist_id' => $mailinglist_id,
        'timestamp' => $timestamp,
        'validation_status' => 0,
      ];

      $this->database->insert('infomaniak_newsletter_field_subscriptions')
        ->fields($fields)
        ->execute();

      $this->sendValidationEmail($fields['email'], $fields['mailinglist_id'], $fields['timestamp']);
      return $fields;
    }
    catch (\Exception $e) {
      // Log the error if needed.
      return FALSE;
    }
  }

  /**
   * Validates a subscription.
   *
   * @param int $timestamp
   *   The subscription timestamp.
   * @param string $email
   *   The subscriber's email.
   * @param string $mailinglist_id
   *   The mailing list ID.
   *
   * @return bool
   *   TRUE if validation successful, FALSE otherwise.
   *
   * @throws \Exception
   */
  public function validateSubscription(int $timestamp, string $email, string $mailinglist_id): bool {
    // Check if subscription exists and is not validated.
    $exists = $this->database->select('infomaniak_newsletter_field_subscriptions', 'ns')
      ->fields('ns')
      ->condition('email', $email)
      ->condition('mailinglist_id', $mailinglist_id)
      ->condition('timestamp', $timestamp)
      ->condition('validation_status', 0)
      ->execute()
      ->fetchAssoc();

    if (!$exists) {
      return FALSE;
    }

    $subscriber = $this->newsletterApiService->createSubscriber($email, []);
    $this->newsletterApiService->assignSubscriber($mailinglist_id, [$subscriber]);

    // Update validation status.
    return (bool) $this->database->update('infomaniak_newsletter_field_subscriptions')
      ->fields(['validation_status' => 1])
      ->condition('email', $email)
      ->condition('mailinglist_id', $mailinglist_id)
      ->condition('timestamp', $timestamp)
      ->execute();
  }

  /**
   * Sends a validation email to the subscriber.
   *
   * @param string $email
   *   The subscriber's email address.
   * @param string $mailinglist_id
   *   The ID of the mailing list.
   * @param int $timestamp
   *   The subscription timestamp.
   *
   * @return bool
   *   TRUE if the email was sent successfully, FALSE otherwise.
   */
  protected function sendValidationEmail(string $email, string $mailinglist_id, int $timestamp): bool {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $site_config = $this->configFactory->get('system.site');

    // Generate validation URL.
    $validation_url = Url::fromRoute('infomaniak_newsletter_field.validate_subscription', [
      'timestamp' => $timestamp,
      'email' => $email,
      'mailinglist_id' => $mailinglist_id,
    ], ['absolute' => TRUE])->toString();

    // Prepare email parameters.
    $params = [
      'subject' => $this->t('Confirm your newsletter subscription'),
      'body' => [
        $this->t('Hello,'),
        '',
        $this->t('Thank you for subscribing to our newsletter. Please confirm your subscription by clicking the link below:'),
        '',
        $validation_url,
        '',
        $this->t('If you did not request this subscription, please ignore this email.'),
        '',
        $this->t('Best regards,'),
        $site_config->get('name'),
      ],
    ];

    // Send the email.
    try {
      $result = $this->mailManager->mail(
        'infomaniak_newsletter_field',
        'subscription_validation',
        $email,
        $langcode,
        $params,
        $site_config->get('mail')
      );

      return (bool) $result['result'];
    }
    catch (\Exception $e) {
      // Log the error if needed.
      return FALSE;
    }
  }

}
