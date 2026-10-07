<?php defined('C5_EXECUTE') or die('Access Denied.');
/**
 * @var \NewsletterSignupKit\Entity\AbstractList $entity
 * @var string                                   $providerLabel
 * @var \Concrete\Core\Validation\CSRF\Token     $token
 */
?>

<div class="ccm-dashboard-header-buttons">
    <a href="<?php echo h($view->url('/dashboard/newsletter_signup/lists')); ?>" class="btn btn-secondary"><?php echo t('Back to Lists'); ?></a>
</div>

<p class="text-muted"><?php echo t('Everything except %s comes from %s and is refreshed on each import.', '<em>' . t('Show in forms') . '</em>', h($providerLabel)); ?></p>

<form method="post" action="<?php echo h($view->action('save', $entity->getID())); ?>">
    <?php echo $token->output('save_list'); ?>
    <fieldset>
        <legend><?php echo t('Details'); ?></legend>
        <div class="row row-cols-1 row-cols-lg-2">
            <div class="col mb-3">
                <label class="form-label"><?php echo t('Name'); ?></label>
                <input type="text" class="form-control" value="<?php echo h($entity->getName()); ?>" readonly>
            </div>
            <div class="col mb-3">
                <label class="form-label"><?php echo t('List ID'); ?></label>
                <input type="text" class="form-control" value="<?php echo h($entity->getListId()); ?>" readonly>
            </div>
            <?php if ($entity instanceof \NewsletterSignupKit\Entity\Mailchimp\MailchimpList) { ?>
                <div class="col mb-3">
                    <label class="form-label"><?php echo t('List Web ID'); ?></label>
                    <input type="text" class="form-control" value="<?php echo h($entity->getListWebId()); ?>" readonly>
                </div>
                <div class="col mb-3">
                    <label class="form-label"><?php echo t('Visibility'); ?></label>
                    <input type="text" class="form-control" value="<?php echo h($entity->getVisibility()); ?>" readonly>
                </div>
            <?php } elseif ($entity instanceof \NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorList) { ?>
                <div class="col mb-3">
                    <label class="form-label"><?php echo t('Client'); ?></label>
                    <input type="text" class="form-control" value="<?php echo h($entity->getClient() ? $entity->getClient()->getName() : ''); ?>" readonly>
                </div>
            <?php } ?>
            <div class="col mb-3">
                <label class="form-label"><?php echo t('Languages'); ?></label>
                <input type="text" class="form-control" value="<?php echo h($entity->getLanguagesAsString()); ?>" readonly>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend><?php echo t('Settings'); ?></legend>
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="showInForms" name="showInForms" value="1" <?php echo $entity->getShowInForms() > 0 ? 'checked' : ''; ?>>
            <label class="form-check-label" for="showInForms"><?php echo t('Show in forms'); ?></label>
        </div>
    </fieldset>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button type="submit" class="btn btn-primary float-end"><?php echo t('Save'); ?></button>
        </div>
    </div>
</form>
