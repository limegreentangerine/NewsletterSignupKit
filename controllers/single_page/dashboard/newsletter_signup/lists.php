<?php

namespace Concrete\Package\NewsletterSignupKit\Controller\SinglePage\Dashboard\NewsletterSignup;

use Doctrine\ORM\EntityManagerInterface;
use NewsletterSignupKit\Provider\Registry;
use NewsletterSignupKit\Entity\AbstractList;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Application\UserInterface\ContextMenu\DropdownMenu;
use Concrete\Core\Application\UserInterface\ContextMenu\Item\LinkItem;
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

    /**
     * The bulk actions dropdown shown in the results header (core search-results pattern, see ConcreteSearchResultsTable).
     */
    private function buildBulkMenu(): DropdownMenu
    {
        $menu = new DropdownMenu();

        foreach (['show' => t('Show in forms'), 'hide' => t('Hide from forms')] as $mode => $label) {
            $menu->addItem(new LinkItem('#', $label, [
                'data-bulk-action' => $mode,
                'data-bulk-action-type' => 'ajax',
                'data-bulk-action-url' => (string) $this->action('bulk', $mode),
            ]));
        }

        return $menu;
    }

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
        $this->set('bulkToken', $this->token->generate('bulk_lists'));
        $this->set('resultsBulkMenu', $this->buildBulkMenu());

        $html = $this->app->make("helper/html");
        $this->addHeaderItem($html->css('dashboard/list.css', 'newsletter_signup_kit'));
    }

    /**
     * Bulk-set showInForms on the ticked lists (called via ajax by the bulk actions dropdown).
     */
    public function bulk(?string $mode = null)
    {
        if (!$this->token->validate('bulk_lists')) {
            return new JsonResponse(['error' => true, 'errors' => [$this->token->getErrorMessage()]]);
        }

        $ids = array_filter((array) $this->request->request->all('ids'), 'is_string');

        if (!in_array($mode, ['show', 'hide'], true) || $ids === []) {
            return new JsonResponse(['error' => true, 'errors' => [t('Select at least one mailing list.')]]);
        }

        $em = $this->app->make(EntityManagerInterface::class);
        $count = 0;

        foreach (array_unique($ids) as $id) {
            $entity = AbstractList::getByID($id);

            if ($entity instanceof AbstractList) {
                $entity->setShowInForms($mode === 'show' ? 1 : 0);
                $em->persist($entity);
                ++$count;
            }
        }

        $em->flush();

        $message = $mode === 'show'
            ? t2('%d mailing list will now show in forms.', '%d mailing lists will now show in forms.', $count)
            : t2('%d mailing list will no longer show in forms.', '%d mailing lists will no longer show in forms.', $count);

        // Shown on the page after the results reload.
        $this->flash('success', $message);

        return new JsonResponse(['error' => false, 'message' => $message]);
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
