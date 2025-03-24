<?php

declare(strict_types=1);

namespace Drupal\infomaniak_newsletter_field\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientInterface;

/**
 * Service class providing Method to use the Infomaniak Newsletter API.
 */
final class InfomaniakV2ApiService implements NewsletterApiServiceInterface {

  /**
   * Constructs an InfomaniakApiService object.
   *
   * @param \Psr\Http\Client\ClientInterface $httpClient
   *   Drupal Wrapped Guzzle Service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal config factory Service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger Channel Factory Service.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LoggerChannelFactoryInterface $logger,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function listGroups(): array {
    try {
      $response = $this->httpClient->request('GET', "https://newsletter.infomaniak.com/api/v1/public/mailinglist", [
        'headers' => [
          'Authorization' => $this->getCredentials(),
        ],
      ]);

      return json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (GuzzleException $e) {
      $this->logger
        ->get('infomaniak_newsletter_field')
        ->error('Newsletter API request failed: @message', [
          '@message' => $e->getMessage(),
        ]);
      throw new \Exception('Failed to get Mailing Lists: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetchGroup(string $mailingListId): array {
    try {
      $response = $this->httpClient->request('GET', "https://newsletter.infomaniak.com/api/v1/public/mailinglist/$mailingListId", [
        'headers' => [
          'Authorization' => $this->getCredentials(),
        ],
      ]);
      return json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (GuzzleException $e) {
      $this->logger
        ->get('infomaniak_newsletter_field')
        ->error('Newsletter API request failed: @message', [
          '@message' => $e->getMessage(),
        ]);
      throw new \Exception('Failed to import contacts: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function assignSubscriber(string $mailingListId, array $emails): bool {
    $formattedEmails = [];
    foreach ($emails as $email) {
      $formattedEmails[] = ['email' => $email];
    }
    try {
      $this->httpClient->request('POST', "https://newsletter.infomaniak.com/api/v1/public/mailinglist/$mailingListId/importcontact", [
        'headers' => [
          'Authorization' => $this->getCredentials(),
          'Content-Type' => 'application/json',
        ],
        'body' => json_encode([
          'contacts' => $formattedEmails,
          'update_metas' => TRUE,
        ]),
      ]);

      $this->logger
        ->get('infomaniak_newsletter_field')
        ->info('Successfully imported @count contacts to newsletter @id', [
          '@count' => count($emails),
          '@id' => $mailingListId,
        ]);

      return TRUE;
    }
    catch (GuzzleException $e) {
      $this->logger->get('infomaniak_newsletter_field')->error('Newsletter API request failed: @message', [
        '@message' => $e->getMessage(),
      ]);
      throw new \Exception('Failed to import contacts: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createSubscriber(string $email, array $fields): string|int {
    return $email;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupsOptions(): array {
    $mailingLists = $this->listGroups();
    $availableMailingLists = [];
    foreach ($mailingLists['data']['data'] as $mailingList) {
      if ($mailingList['status'] === 1) {
        $availableMailingLists[$mailingList['id']] = $mailingList['name'];
      }
    }

    return $availableMailingLists;
  }

  /**
   * Get Credentials in Settings.
   *
   * @return string
   *   Infomaniak Credentioals.
   */
  private function getCredentials(): string {
    $config = $this->configFactory->get('infomaniak_newsletter_field.settings');
    $clientApi = $config->get('newsletter_client_api');
    $clientSecret = $config->get('newsletter_client_secret');

    return 'Basic ' . base64_encode($clientApi . ':' . $clientSecret);
  }

}
