<?php

namespace Concrete\Package\NewsletterSignupKit\Block\CampaignMonitorSubscription;

defined('C5_EXECUTE') or die('Access Denied.');

use Core;
use Exception;
use Concrete\Core\Http\Response;
use Concrete\Core\Block\BlockController;
use NewsletterSignupKit\Block\BlockTrait;
use Concrete\Core\Routing\RedirectResponse;
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
            $message = t('Please enter a valid email address.');
            if ($isAjax) {
                return new JsonResponse(['success' => false, 'message' => $message]);
            }
            $this->set('error', true);
            $this->set('errorMessage', $message);

            return;
        }

        $api = new Api();
        $args = [
            'EmailAddress' => $post['email'],
            'ConsentToTrack' => 'Yes',
            'Resubscribe' => true,
            'Name' => trim(($post['firstName'] ?? '') . ' ' . ($post['lastName'] ?? '')),
        ];

        try {
            $response = $api->addSubscriber($post['listId'], $post['email'], $args);
            if (property_exists($response, 'id')) {
                if ($isAjax) {
                    return new JsonResponse(['success' => true]);
                }
                $this->set('success', true);
                return (new RedirectResponse(\URL::to('/thank_you'), Response::HTTP_TEMPORARY_REDIRECT))->send();
            }

            $this->addToBatchSubscribers($post, Subscriber::class, Logger::class);
            $errorMessage = $response['title'] ?? 'Unknown error';
            $logger->addError('Campaign Monitor Error: ' . json_encode($response));

            if ($isAjax) {
                return new JsonResponse(['success' => false, 'message' => $errorMessage]);
            }

            $this->set('error', true);
            $this->set('errorMessage', $errorMessage);

        } catch (Exception $e) {
            $this->addToBatchSubscribers($post, Subscriber::class, Logger::class);
            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'message' => t('A system error occurred. Please try again later.'),
                ]);
            }
            throw $e;
        }
    }
}
