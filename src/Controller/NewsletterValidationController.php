<?php

namespace Drupal\infomaniak_newsletter_field\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\infomaniak_newsletter_field\Services\NewsletterValidationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for newsletter subscription validation.
 */
class NewsletterValidationController extends ControllerBase {

  /**
   * NewsletterValidationController constructor.
   *
   * @param \Drupal\infomaniak_newsletter_field\Services\NewsletterValidationService $newsletterService
   *   The newsletter service.
   */
  public function __construct(
    protected NewsletterValidationService $newsletterService,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('infomaniak_newsletter_field.validation_service')
    );
  }

  /**
   * Validates a newsletter subscription.
   *
   * @param int $timestamp
   *   The subscription timestamp.
   * @param string $email
   *   The subscriber's email.
   * @param string $mailinglist_id
   *   The mailing list ID.
   *
   * @return array
   *   Render array response indicating success or failure.
   *
   * @throws \Exception
   */
  public function validateSubscription($timestamp, $email, $mailinglist_id) {
    //$result = $this->newsletterService->validateSubscription($timestamp, $email, $mailinglist_id);

    //if (!$result) {
    //  throw new NotFoundHttpException('Subscription not found or already validated.');
    //}

    return [
      '#theme' => 'newsletter_signup_validation',
      '#message' => $this->t('Your email address has been successfully validated. Thank you for your subscription.'),
    ];
  }

}
