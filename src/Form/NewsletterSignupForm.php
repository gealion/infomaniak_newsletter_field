<?php

namespace Drupal\infomaniak_newsletter_field\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\infomaniak_newsletter_field\Services\NewsletterApiServiceInterface;
use Drupal\infomaniak_newsletter_field\Services\NewsletterValidationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Newsletter signup form.
 */
class NewsletterSignupForm extends FormBase {
  use StringTranslationTrait;
  use MessengerTrait;

  private function __construct(
    protected LoggerChannelFactoryInterface $loggerChannelFactory,
    protected RendererInterface $renderer,
    protected NewsletterApiServiceInterface $newsletterApiService,
    protected NewsletterValidationService $newsletterValidationService,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): NewsletterSignupForm|static {
    return new static(
      $container->get('logger.factory'),
      $container->get('renderer'),
      $container->get('infomaniak_newsletter_field.infomaniak_v2'),
      $container->get('infomaniak_newsletter_field.validation_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'newsletter_signup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $mailing_list_id = NULL, $submit_text = NULL): array {
    $form['#prefix'] = '<div class="newsletter-signup-form"><div id="newsletter-signup-form-wrapper">';
    $form['#suffix'] = '</div></div>';

    $form['newsletter_signup_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Enter your email address'),
        'class' => ['newsletter-email-input'],
      ],
    ];

    $form['newsletter_signup_mailing_list_id'] = [
      '#type' => 'hidden',
      '#value' => $mailing_list_id,
    ];

    $form['newsletter_signup_submit'] = [
      '#type' => 'submit',
      '#value' => $submit_text ?: $this->t('Subscribe'),
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'wrapper' => 'newsletter-signup-form-wrapper',
        'effect' => 'fade',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Subscribing...'),
        ],
      ],
      '#attributes' => [
        'class' => ['newsletter-submit-button'],
      ],
    ];
    return $form;
  }

  /**
   * Ajax callback for form submission.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    if (!$form_state->hasAnyErrors()) {
      $message = [
        '#theme' => 'newsletter_signup_message',
        '#status' => 'success',
        '#message' => $this->t('Thank you for subscribing! An Validation email has been sent.'),
      ];

      $response->addCommand(new ReplaceCommand(
        '.newsletter-signup-form',
        $this->renderer->render($message),
      ));
    }
    else {
      $message = [
        '#theme' => 'newsletter_signup_message',
        '#status' => 'error',
        '#message' => FALSE,
        '#errors' => $form_state->getErrors(),
      ];
      $response->addCommand(new ReplaceCommand(
        ' #newsletter-messages__content',
        $this->renderer->render($message),
      ));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state):void {
    if (!filter_var($form_state->getValue('newsletter_signup_email'), FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName(
        'newsletter_signup_email',
        $this->t('Please enter a valid email address.')
      );
    }

    if (empty($form_state->getValue('newsletter_signup_mailing_list_id'))) {
      $this->loggerChannelFactory
        ->get('infomaniak_newsletter_field')
        ->error('Missing mailing list ID in newsletter signup form.');
      $form_state->setErrorByName(
        'newsletter_signup_mailing_list_id',
        $this->t('Configuration error: Missing mailing list ID.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $email = $form_state->getValue('newsletter_signup_email');
    $mailing_list_id = $form_state->getValue('newsletter_signup_mailing_list_id');

    try {
      $this->newsletterValidationService->createSubscription($email, $mailing_list_id);

      $this->loggerChannelFactory
        ->get('infomaniak_newsletter_field')
        ->info('new newsletter() subscription for email: @email',
          [
            '@email' => $email,
          ]
        );

      // Clear the form input.
      $form_state->setValue('email', '');
    }
    catch (\Exception $e) {
      $this->loggerChannelFactory
        ->get('infomaniak_newsletter_field')
        ->error('Newsletter subscription failed for email @email: @message',
          [
            '@email' => $email,
            '@message' => $e->getMessage(),
          ]
        );
    }
  }

}
