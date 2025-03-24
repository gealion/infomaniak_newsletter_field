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
final class InfomaniakApiService implements NewsletterApiServiceInterface {

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
    $baseUrl = $this->buildApiBaseUrl();
    try {
      $response = $this->httpClient->request('GET', $baseUrl . "/groups", [
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
  public function fetchGroup(string $groupId): array {
    $baseUrl = $this->buildApiBaseUrl();
    try {
      $response = $this->httpClient->request('GET', $baseUrl . "/groups/$groupId", [
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
  public function createSubscriber(string $email, array $fields): string|int {
    $baseUrl = $this->buildApiBaseUrl();
    try {
      $response = $this->httpClient->request('POST', $baseUrl . "/subscribers", [
        'headers' => [
          'Authorization' => $this->getCredentials(),
          'Content-Type' => 'application/json',
        ],
        'body' => json_encode([
          'email' => $email,
        ]),
      ]);

      $this->logger
        ->get('infomaniak_newsletter_field')
        ->info('Subscriber successfully created for @email.', [
          '@email' => $email,
        ]);

      $json = json_decode($response->getBody()->getContents());
      return $json->data->id;
    }
    catch (GuzzleException $e) {
      $this->logger->get('infomaniak_newsletter_field')
        ->error('Newsletter API request failed: @message', [
          '@message' => $e->getMessage(),
        ]);
      throw new \Exception('Failed to import contacts: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function assignSubscriber(string $groupId, array $subscribers): bool {
    $baseUrl = $this->buildApiBaseUrl();
    try {
      $this->httpClient->request('POST', $baseUrl . "/groups/$groupId/subscribers/assign", [
        'headers' => [
          'Authorization' => $this->getCredentials(),
          'Content-Type' => 'application/json',
        ],
        'body' => json_encode([
          'subscriber_ids' => $subscribers,
        ]),
      ]);

      $this->logger
        ->get('infomaniak_newsletter_field')
        ->info('Successfully imported @count contacts to newsletter @id', [
          '@count' => count($subscribers),
          '@id' => $groupId,
        ]);

      return TRUE;
    }
    catch (GuzzleException $e) {
      $this->logger->get('infomaniak_newsletter_field')
        ->error('Newsletter API request failed: @message', [
          '@message' => $e->getMessage(),
        ]);
      throw new \Exception('Failed to import contacts: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupsOptions(): array {
    $mailingLists = $this->listGroups();
    $availableMailingLists = [];
    foreach ($mailingLists['data'] as $mailingList) {
      $availableMailingLists[$mailingList['id']] = $mailingList['name'];
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
    $clientToken = $config->get('newsletter_token');
    return 'Bearer ' . $clientToken;
  }

  /**
   * Build the Base of Infomaniak API for Newsletter.
   */
  private function buildApiBaseUrl(): string {
    $config = $this->configFactory->get('infomaniak_newsletter_field.settings');
    return $config->get('newsletter_base_url') . $config->get('newsletter_domain');
  }

}
