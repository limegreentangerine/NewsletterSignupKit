<?php

namespace Concrete\Package\NewsletterSignupKit\Block\CampaignMonitorSubscription;

defined('C5_EXECUTE') or die('Access Denied.');

use Core;
use Exception;
use Concrete\Core\Block\BlockController;
use NewsletterSignupKit\Block\BlockTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use NewsletterSignupKit\Api\CampaignMonitor as Api;
use NewsletterSignupKit\Log\CampaignMonitorLogger as Logger;
use NewsletterSignupKit\Search\ItemList\MailingList\CampaignMonitor as MailingList;
use NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorSubscriber as Subscriber;

class Controller extends BlockController
{
    use BlockTrait;
    protected $btTable = 'btNsCampaignMonitorSubscriptionForm';
    protected $btDefaultSet = 'newsletter_signup_kit';
    protected $btInterfaceWidth = 800;
    protected $btInterfaceHeight = 600;

    protected function loadHelpers()
    {
        $this->set('lists', $this->getLists(new MailingList()));
    }

    public function getBlockTypeName()
    {
        return t('Campaign Monitor Subscription Form');
    }

    public function getBlockTypeDescription()
    {
        return t('Add a Campaign Monitor subscription form block.');
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
            'EmailAddress' => $post['email'],
            'ConsentToTrack' => 'Yes',
            'Resubscribe' => true,
            'Name' => trim(($post['firstName'] ?? '') . ' ' . ($post['lastName'] ?? '')),
        ];

        try {
            // Returns a CS_REST_Wrapper_Result; HTTP errors are reported on it, not thrown
            $response = $api->addSubscriber($post['listId'], $post['email'], $args);
        } catch (Exception $e) {
            $logger->addError('Campaign Monitor Error: ' . $e->getMessage());
            $this->addToBatchSubscribers($post, Subscriber::class, Logger::class);

            return $this->subscriptionFailure($systemError, $isAjax);
        }

        if ($response->was_successful()) {
            return $isAjax ? new JsonResponse(['success' => true]) : $this->successRedirect();
        }

        $logger->addError('Campaign Monitor Error: ' . json_encode($response->response));

        // 400 = Campaign Monitor rejected this subscriber (e.g. invalid email); retrying from the batch queue won't help
        if ((int) $response->http_status_code === 400) {
            return $this->subscriptionFailure($response->response->Message ?? $systemError, $isAjax);
        }
        $this->addToBatchSubscribers($post, Subscriber::class, Logger::class);

        return $this->subscriptionFailure($systemError, $isAjax);
    }
}
