<?php defined('C5_EXECUTE') or die('Access Denied.');

if ($controller->getAction() === 'details') {
    View::element('dashboard/mailing_list/details', [
        'entity' => $entity,
        'providerLabel' => $providerLabel,
        'token' => $token,
        'view' => $view,
    ], 'newsletter_signup_kit');
} else {
    View::element('dashboard/mailing_list/list', [
        'providers' => $providers,
        'provider' => $provider ?? null,
        'items' => $items ?? [],
        'result' => $result ?? null,
        'pagination' => $pagination ?? '',
        'name' => $name ?? '',
        'num_results' => $num_results ?? null,
        'allowed_num_results' => $allowed_num_results ?? [],
        'token' => $token ?? null,
        'view' => $view,
    ], 'newsletter_signup_kit');
}
