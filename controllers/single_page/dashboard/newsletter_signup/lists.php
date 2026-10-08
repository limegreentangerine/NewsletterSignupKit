<?php

namespace Concrete\Package\NewsletterSignupKit\Controller\SinglePage\Dashboard\NewsletterSignup;

use Doctrine\ORM\EntityManagerInterface;
use NewsletterSignupKit\Provider\Registry;
use NewsletterSignupKit\Entity\AbstractList;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\NewsletterSignupKit\Controller\Search\MailingLists as SearchController;

/**
 * The imported mailing lists of the configured provider(s). Only showInForms is editable; everything else is
 * owned by the provider and overwritten on the next import.
 */
class Lists extends DashboardPageController
{
    protected $helpers = [
        'form',
        'concrete/ui',
    ];

    public function view()
    {
        $providers = [];
        foreach (Registry::configured() as $key) {
            $providers[$key] = Registry::label($key);
        }

        $this->set('providers', $providers);

        if ($providers === []) {
            return;
        }

        $selected = (string) $this->request->query->get('provider', '');
        $provider = isset($providers[$selected]) ? $selected : array_key_first($providers);

        $reset = false;
        if ($this->request->isPost()) {
            if (!$this->token->validate('list-search')) {
                $this->error->add($this->token->getErrorMessage());
            } else {
                $reset = true;
            }
        }

        $search = $this->app->make(SearchController::class, ['provider' => $provider]);
        $search->search($reset);

        $result = $search->getSearchResultObject();
        $params = $search->getStickyRequest()->getSearchRequest();

        $allowedNumResults = array_combine($search->getAllowedPaginationSizes(), $search->getAllowedPaginationSizes());
        $numResults = (int) ($params['num_results'] ?? 0);
        if (!isset($allowedNumResults[$numResults])) {
            $numResults = $search->getDefaultPaginationSize();
        }

        $this->set('provider', $provider);
        $this->set('result', $result);
        $this->set('items', $result->getItems());
        $this->set('pagination', $result->getPaginationHTML());
        $this->set('name', $params['name'] ?? '');
        $this->set('num_results', $numResults);
        $this->set('allowed_num_results', $allowedNumResults);
        $this->set('token', $this->token->generate('list-search'));
        $this->set('bulkToken', $this->token->output('bulk_lists', true));
    }

    /**
     * Bulk-set showInForms on the ticked lists.
     */
    public function bulk()
    {
        $provider = (string) $this->request->request->get('provider', '');
        $listUrl = $this->app->make('url/manager')->resolve(['/dashboard/newsletter_signup/lists']);
        if (Registry::has($provider)) {
            $listUrl = $listUrl->setQuery(['provider' => $provider]);
        }

        if (!$this->token->validate('bulk_lists')) {
            $this->flash('error', $this->token->getErrorMessage());

            return $this->buildRedirect($listUrl);
        }

        $ids = array_filter((array) $this->request->request->all('ids'), 'is_string');
        $action = (string) $this->request->request->get('bulk_action', '');

        if ($ids === []) {
            $this->flash('error', t('Select at least one mailing list.'));

            return $this->buildRedirect($listUrl);
        }

        if (!in_array($action, ['show', 'hide'], true)) {
            $this->flash('error', t('Choose a bulk action.'));

            return $this->buildRedirect($listUrl);
        }

        $em = $this->app->make(EntityManagerInterface::class);
        $count = 0;

        foreach (array_unique($ids) as $id) {
            $entity = AbstractList::getByID($id);

            if ($entity instanceof AbstractList) {
                $entity->setShowInForms($action === 'show' ? 1 : 0);
                $em->persist($entity);
                ++$count;
            }
        }

        $em->flush();

        $this->flash('success', $action === 'show'
            ? t2('%d mailing list will now show in forms.', '%d mailing lists will now show in forms.', $count)
            : t2('%d mailing list will no longer show in forms.', '%d mailing lists will no longer show in forms.', $count));

        return $this->buildRedirect($listUrl);
    }

    public function details(?string $id = null)
    {
        $entity = $id !== null ? AbstractList::getByID($id) : null;

        if (!$entity instanceof AbstractList) {
            return $this->buildRedirect('/dashboard/newsletter_signup/lists');
        }

        $this->set('entity', $entity);
        $this->set('providerLabel', Registry::label(Registry::keyFor($entity)));
        $this->set('token', $this->token);
    }

    public function save(?string $id = null)
    {
        $listUrl = '/dashboard/newsletter_signup/lists';

        if (!$this->token->validate('save_list')) {
            $this->flash('error', $this->token->getErrorMessage());

            return $this->buildRedirect($listUrl);
        }

        $entity = $id !== null ? AbstractList::getByID($id) : null;

        if (!$entity instanceof AbstractList) {
            $this->flash('error', t('Mailing list not found.'));

            return $this->buildRedirect($listUrl);
        }

        $entity->setShowInForms($this->request->request->get('showInForms') ? 1 : 0);

        $em = $this->app->make(EntityManagerInterface::class);
        $em->persist($entity);
        $em->flush();

        $this->flash('success', t('Mailing list saved.'));

        return $this->buildRedirect(sprintf('%s/details/%s', $listUrl, $id));
    }
}
