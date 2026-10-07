<?php

namespace Concrete\Package\NewsletterSignupKit\Controller\SinglePage\Dashboard;

use NewsletterSignupKit\Provider\Registry;
use Concrete\Core\Page\Controller\DashboardPageController;

/**
 * Read-only: shows which newsletter provider the site's .env file configures.
 */
class NewsletterSignup extends DashboardPageController
{
    public function view()
    {
        $providers = [];
        foreach (Registry::keys() as $key) {
            $providers[$key] = [
                'label' => Registry::label($key),
                'configured' => Registry::config($key)->isConfigured(),
            ];
        }

        $this->set('providers', $providers);
    }
}
