<?php

namespace Concrete\Package\NewsletterSignupKit\Block\MailchimpSubscription;

defined('C5_EXECUTE') or die('Access Denied.');

use Core;
use Exception;
use Concrete\Core\Block\BlockController;
use NewsletterSignupKit\Block\BlockTrait;
use GuzzleHttp\Exception\RequestException;
use NewsletterSignupKit\Api\Mailchimp as Api;
use Symfony\Component\HttpFoundation\JsonResponse;
use NewsletterSignupKit\Log\MailchimpLogger as Logger;
use NewsletterSignupKit\Entity\Mailchimp\MailchimpSubscriber as Subscriber;
use NewsletterSignupKit\Search\ItemList\MailingList\Mailchimp as MailingList;

class Controller extends BlockController
{
    use BlockTrait;

    protected $btTable = 'btNsMailchimpSubscriptionForm';
    protected $btDefaultSet = 'newsletter_signup_kit';
    protected $btInterfaceWidth = 800;
    protected $btInterfaceHeight = 600;

    protected function loadHelpers()
    {
        $this->set('lists', $this->getLists(new MailingList()));
    }

    public function getBlockTypeName()
    {
        return t('Mailchimp Subscription Form');
    }

    public function getBlockTypeDescription()
    {
        return t('Add a Mailchimp subscription form block.');
    }

    public function action_subscribe()
    {
        if (!$this->request->post()) {
            return $this->view();
        }
        $logger = Core::make(Logger::class)->getLogger();

        $post = $this->sanitizeSubscriptionPost($this->request->request->all());
        // Never trust a client-supplied list; always use the block's configured one
        $post['listId'] = (string) $this->subscriptionListId;
        $this->set('formData', $post);
        $isAjax = $post['ajax'];

        if ($post['email'] === '' || $post['listId'] === '') {
            return $this->subscriptionFailure(t('Please enter a valid email address.'), $isAjax);
        }

        $systemError = t('A system error occurred. Please try again later.');
        $api = new Api();
        $args = [
            'status_if_new' => 'subscribed',
            'email_address' => $post['email'],
            'merge_fields' => [
                'FNAME' => $post['firstName'] ?? '',
                'LNAME' => $post['lastName'] ?? '',
            ],
            'tags' => ['website-sign-up'],
        ];

        try {
            // Returns the member object on success; throws on any non-2xx response
            $response = $api->addMember($post['listId'], $post['email'], $args, true);
        } catch (RequestException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $body = $e->hasResponse() ? json_decode((string) $e->getResponse()->getBody()) : null;
            $logger->addError('Mailchimp Error: ' . ($body !== null ? json_encode($body) : $e->getMessage()));

            // 400 = Mailchimp rejected this subscriber (e.g. fake email); retrying from the batch queue won't help
            if ($status === 400) {
                return $this->subscriptionFailure($body->detail ?? $body->title ?? $systemError, $isAjax);
            }
            $this->addToBatchSubscribers($post, Subscriber::class, Logger::class);

            return $this->subscriptionFailure($systemError, $isAjax);
        } catch (Exception $e) {
            $logger->addError('Mailchimp Error: ' . $e->getMessage());
            $this->addToBatchSubscribers($post, Subscriber::class, Logger::class);

            return $this->subscriptionFailure($systemError, $isAjax);
        }

        if (!is_object($response) || !isset($response->id)) {
            $logger->addError('Mailchimp Error: unexpected response ' . json_encode($response));
            $this->addToBatchSubscribers($post, Subscriber::class, Logger::class);

            return $this->subscriptionFailure($systemError, $isAjax);
        }

        return $isAjax ? new JsonResponse(['success' => true]) : $this->successRedirect();
    }
}
