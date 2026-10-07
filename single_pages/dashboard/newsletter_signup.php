<?php defined('C5_EXECUTE') or die('Access Denied.');

$active = array_keys(array_filter($providers, fn($provider) => $provider['configured']));
?>

<?php if (count($active) === 0) { ?>
    <div class="alert alert-warning">
        <?php echo t('No newsletter provider is configured. Add its API settings to the site\'s %s file; see %s.', '<code>.env</code>', '<a href="' . h($view->url('/dashboard/newsletter_signup/settings')) . '">' . t('Settings') . '</a>'); ?>
    </div>
<?php } elseif (count($active) > 1) { ?>
    <div class="alert alert-warning">
        <?php echo t('More than one newsletter provider is configured. Sites normally use only one; check the %s file.', '<code>.env</code>'); ?>
    </div>
<?php } else { ?>
    <div class="alert alert-success">
        <?php echo t('This site uses %s.', '<strong>' . h($providers[$active[0]]['label']) . '</strong>'); ?>
    </div>
<?php } ?>

<div class="row row-cols-1 row-cols-lg-2">
    <?php foreach ($providers as $provider) { ?>
        <div class="col mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo h($provider['label']); ?></h5>
                    <?php if ($provider['configured']) { ?>
                        <span class="badge text-bg-success"><?php echo t('Configured'); ?></span>
                    <?php } else { ?>
                        <span class="badge text-bg-secondary"><?php echo t('Not configured'); ?></span>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<p>
    <a href="<?php echo h($view->url('/dashboard/newsletter_signup/settings')); ?>" class="btn btn-secondary"><?php echo t('Settings'); ?></a>
    <a href="<?php echo h($view->url('/dashboard/newsletter_signup/lists')); ?>" class="btn btn-secondary"><?php echo t('Mailing Lists'); ?></a>
</p>
