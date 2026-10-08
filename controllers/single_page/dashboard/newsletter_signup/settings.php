<?php

namespace Concrete\Package\NewsletterSignupKit\Controller\SinglePage\Dashboard\NewsletterSignup;

use NewsletterSignupKit\Provider\Registry;
use Concrete\Core\Page\Controller\DashboardPageController;

/**
 * Read-only: the provider settings live in the site's .env file, so this page only reports which are populated.
 */
class Settings extends DashboardPageController
{
    public function view()
    {
        $providers = [];
        foreach (Registry::keys() as $key) {
            $config = Registry::config($key);
            $providers[$key] = [
                'label' => Registry::label($key),
                'configured' => $config->isConfigured(),
                'settings' => array_map(
                    fn(array $setting) => ['label' => t($setting['label'])] + $setting,
                    $config->describeSettings(),
                ),
            ];
        }

        $this->set('providers', $providers);
    }
}
