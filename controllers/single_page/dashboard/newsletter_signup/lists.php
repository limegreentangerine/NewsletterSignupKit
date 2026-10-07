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
