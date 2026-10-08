<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<p class="text-muted">
    <?php echo t('These settings are read from the site\'s %s file and cannot be changed here.', '<code>.env</code>'); ?>
</p>

<?php foreach ($providers as $provider) { ?>
    <fieldset class="mb-4 <?php echo $provider['configured'] ? '' : 'opacity-75'; ?>">
        <legend>
            <?php echo h($provider['label']); ?>
            <?php if ($provider['configured']) { ?>
                <span class="badge text-bg-success fs-6"><?php echo t('Configured'); ?></span>
            <?php } else { ?>
                <span class="badge text-bg-secondary fs-6"><?php echo t('Not configured'); ?></span>
            <?php } ?>
        </legend>
        <?php foreach ($provider['settings'] as $setting) { ?>
            <?php if ($setting['populated']) { ?>
                <div class="alert alert-success">
                    <strong><?php echo h($setting['label']); ?></strong>
                    <code><?php echo h($setting['env']); ?></code>:
                    <?php echo h($setting['display']); ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-warning">
                    <strong><?php echo h($setting['label']); ?></strong>
                    <?php echo t('has not been populated. Add %s to your .env file.', '<code>' . h($setting['env']) . '</code>'); ?>
                </div>
            <?php } ?>
        <?php } ?>
    </fieldset>
<?php } ?>
