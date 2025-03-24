<?php

declare(strict_types=1);

namespace Drupal\infomaniak_newsletter_field\Services;

/**
 * Service class providing Method to use the Infomaniak Newsletter API.
 */
final class MockupApiService implements NewsletterApiServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function listGroups(): array {
    return json_decode('
      {
        "result":"success",
        "data":[
          {
          "id": 1337,
          "name": "My first mailinglist",
          "updated_at":1730509011
          },
          {
            "id": 1338,
            "name": "My second mailinglist"
          }
        ],
        "total":2,
        "page":1,
        "pages":1,
        "items_per_page":15
      }
      ',
     TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchGroup(string $groupId): array {
    return json_decode('
          {
          "id": ' . $groupId . ',
          "name": "My first mailinglist",
          "updated_at":1730509011
          }
    ', TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function createSubscriber(string $email, array $fields): string|int {
    return 7662;
  }

  /**
   * {@inheritdoc}
   */
  public function assignSubscriber(string $groupId, array $subscribers): bool {
    return TRUE;

  }

  /**
   * {@inheritdoc}
   */
  public function getGroupsOptions(): array {
    $listGroups = $this->listGroups()['data'];
    $options = [];
    foreach ($listGroups as $group) {
      $options[$group['id']] = $group['name'];
    }
    return $options;
  }

}
