<?php

namespace NewsletterSignupKit\Api;

/**
 * The lowest common denominator between newsletter providers, for code that
 * needs to subscribe someone without caring which provider is configured.
 *
 * Provider-specific operations (Campaign Monitor clients, Mailchimp batch
 * status, etc.) stay on the concrete classes.
 */
interface ApiInterface
{
    /**
     * Subscribe a single email address to a list.
     *
     * @param array $args Provider-specific extra fields, passed through as-is
     *
     * @return mixed The provider's raw response
     */
    public function addSubscriber(string $listId, string $email, array $args = []);
}
