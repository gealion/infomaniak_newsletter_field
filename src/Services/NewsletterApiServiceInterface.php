<?php

declare(strict_types=1);

namespace Drupal\infomaniak_newsletter_field\Services;

/**
 * Service class providing Method to use the Infomaniak Newsletter API.
 */
interface NewsletterApiServiceInterface {

  /**
   * Retrieve all Groups.
   *
   * @return array
   *   List of Available Group.
   *
   * @throws \Exception
   */
  public function listGroups(): array;

  /**
   * Fetch the Group detailed information.
   *
   * @param string $groupId
   *   The Mailing List ID.
   *
   * @return array
   *   The detailed Information of the MailingList.
   *
   * @throws \Exception
   */
  public function fetchGroup(string $groupId): array;

  /**
   * Assign Subscribers to Group.
   *
   * @param string $groupId
   *   The GroupId of the Group to assign subscriber to.
   * @param array $subscribers
   *   List of SubscriberId to assign to the Group.
   *
   * @return bool
   *   State of the Request.
   *
   * @throws \Exception
   */
  public function assignSubscriber(string $groupId, array $subscribers): bool;

  /**
   * Upsert Subscriber with provided info.
   *
   * @param string $email
   *   Email of the Subscriber.
   * @param array $fields
   *   Additional Field of the Subscriber.
   *
   * @return string|int
   *   ID of the newly created Subscriber.
   */
  public function createSubscriber(string $email, array $fields): string|int;

  /**
   * Get a Select usabel list of Groups.
   *
   * @return array
   *   Array of GroupID => GroupName.
   */
  public function getGroupsOptions() : array;

}
