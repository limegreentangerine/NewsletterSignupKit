<?php

namespace NewsletterSignupKit\Block;

use Core;
use Concrete\Core\Editor\LinkAbstractor;
use Doctrine\ORM\EntityManagerInterface;
use NewsletterSignupKit\Search\ItemList\MailingList\AbstractMailingList;

trait BlockTrait
{
    /**
     * Get a list of mailing lists available.
     *
     * @return array
     */
    protected function getLists(AbstractMailingList $list): array
    {
        $lists = [
            '' => 'Choose mailing list...',
        ];

        $retrievedLists = $list->getResults();

        foreach ($retrievedLists as $mailingList) {
            $lists[$mailingList->getListID()] = $mailingList->getName();
        }

        return $lists;
    }

    /**
     * Adds a subscriber to the database if they don't already exist
     * for the given mailing list.
     *
     * @param array $post The post data containing the email address and
     *                    mailing list ID of the subscriber to add.
     */
    protected function addToBatchSubscribers(array $post = [], $subscriberClass = null, $loggerClass = null)
    {
        $entityManager = Core::make(EntityManagerInterface::class);
        $subscriber = $subscriberClass::getByEmailAndListID($post['email'], $post['listId']);
        $logger = Core::make($loggerClass)->getLogger();

        if ($subscriber === false || $subscriber->getID() < 1) {
            $subscriber = new $subscriberClass();
            $subscriber->setEmail($post['email']);
            $subscriber->setListId($post['listId']);
            $subscriber->setFirstName($post['firstName'] ?? '');
            $subscriber->setLastName($post['lastName'] ?? '');
            $subscriber->setProcessed(0);

            $entityManager->persist($subscriber);
        }
        $entityManager->flush();
        $logger->addInfo(t('%s added to batch subscriber list [%s] to be added during automated job execution', $post['email'], $post['listId']));
    }


    /**
     * Normalise untrusted subscription form input: strip markup and control
     * characters, cap lengths, and validate the email address.
     *
     * @return array{email: string, listId: string, firstName: string, lastName: string, ajax: bool}
     */
    protected function sanitizeSubscriptionPost(array $post): array
    {
        $clean = static function ($value, int $max): string {
            $value = is_scalar($value) ? (string) $value : '';
            $value = strip_tags($value);
            $value = preg_replace('/[\x00-\x1F\x7F]+/u', '', $value) ?? '';

            return mb_substr(trim($value), 0, $max);
        };

        $email = $clean($post['email'] ?? '', 254);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $email = '';
        }

        $listId = $clean($post['listId'] ?? '', 64);
        if (!preg_match('/^[A-Za-z0-9_-]*$/', $listId)) {
            $listId = '';
        }

        return [
            'email' => $email,
            'listId' => $listId,
            'firstName' => $clean($post['firstName'] ?? '', 100),
            'lastName' => $clean($post['lastName'] ?? '', 100),
            'ajax' => !empty($post['ajax']),
        ];
    }

    public function add()
    {
        $this->loadHelpers();
    }

    public function edit()
    {
        $this->loadHelpers();
        foreach (['content', 'successMessage', 'helpText'] as $field) {
            $this->set($field, LinkAbstractor::translateFromEditMode((string) $this->$field));
        }
    }

    public function view()
    {
        foreach (['content', 'successMessage', 'helpText'] as $field) {
            $this->set($field, LinkAbstractor::translateFrom((string) $this->$field));
        }
    }

    public function save($args)
    {
        $args['ajaxSubmission'] = array_key_exists('ajaxSubmission', $args) ? (int) $args['ajaxSubmission'] : 0;

        // Plain-text fields must never carry markup
        foreach (['title', 'placeholder', 'buttonText'] as $field) {
            if (isset($args[$field])) {
                $args[$field] = trim(strip_tags((string) $args[$field]));
            }
        }
        // Rich-text fields are stored in Concrete's link-abstracted form
        foreach (['content', 'successMessage', 'helpText'] as $field) {
            if (isset($args[$field])) {
                $args[$field] = LinkAbstractor::translateTo((string) $args[$field]);
            }
        }
        parent::save($args);
    }
}
