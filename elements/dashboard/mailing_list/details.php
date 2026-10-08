<?php defined('C5_EXECUTE') or die('Access Denied.');
/**
 * @var \NewsletterSignupKit\Entity\AbstractList $entity
 * @var string                                   $providerLabel
 * @var \Concrete\Core\Validation\CSRF\Token     $token
 */
$form = \Core::make('helper/form');
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
                <?php
                    echo $form->label('name', t('Name'));
echo $form->text('name', h($entity->getName()), ['readonly' => 'readonly']);
?>
            </div>
            <div class="col mb-3">
                <?php
    echo $form->label('list_id', t('List ID'));
echo $form->text('list_id', h($entity->getListId()), ['readonly' => 'readonly']);
?>
            </div>
            <?php if ($entity instanceof \NewsletterSignupKit\Entity\Mailchimp\MailchimpList) { ?>
                <div class="col mb-3">
                    <?php
        echo $form->label('list_web_id', t('List Web ID'));
                echo $form->text('list_web_id', h($entity->getListWebId()), ['readonly' => 'readonly']);
                ?>
                </div>
                <div class="col mb-3">
                    <?php
                    echo $form->label('visibility', t('Visibility'));
                echo $form->text('visibility', h($entity->getVisibility()), ['readonly' => 'readonly']);
                ?>
                </div>
            <?php } elseif ($entity instanceof \NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorList) { ?>
                <div class="col mb-3">
                    <?php
                    echo $form->label('client', t('Client'));
                echo $form->text('client', h($entity->getClient() ? $entity->getClient()->getName() : ''), ['readonly' => 'readonly']);
                ?>
                </div>
            <?php } ?>
        </div>
    </fieldset>
    <fieldset>
        <legend><?php echo t('Settings'); ?></legend>
        <div class="form-check mb-3">
            <?php
                echo $form->checkbox('showInForms', 1, $entity->getShowInForms() > 0);
echo $form->label('showInForms', t('Show in forms'), ['class' => 'form-check-label']);
?>
        </div>
    </fieldset>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button type="submit" class="btn btn-primary float-end"><?php echo t('Save'); ?></button>
        </div>
    </div>
</form>
