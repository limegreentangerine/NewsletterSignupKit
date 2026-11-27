<?php defined('C5_EXECUTE') or die('Access Denied.');
$editor = \Core::make('editor');
$editor->setAllowFileManager(false);
?>

<fieldset>
    <legend><?php echo t('Mailchimp List'); ?></legend>

    <div class="form-group">
        <?php
            echo $form->label('subscriptionListId', t('Choose Mailing List'));
echo $form->select('subscriptionListId', $lists, $subscriptionListId ?? null);
?>
    </div>
</fieldset>

<fieldset>
    <legend><?php echo t('Content'); ?></legend>

    <div class="form-group">
        <?php
    echo $form->label('title', t('Title'));
echo $form->text('title', $title ?? null);
?>
    </div>

    <div class="form-group">
        <?php
    echo $form->label('content', t('Content'));
echo $editor->outputStandardEditor('content', $content ?? null);
?>
    </div>

    <div class="form-group" id="ns-success-message-group">
        <?php
    echo $form->label('successMessage', t('Success Message'));
echo $editor->outputStandardEditor('successMessage', $successMessage ?? null);
?>
    </div>

    <div class="form-group">
        <div class="form-check">
            <?php
        echo $form->checkbox('ajaxSubmission', 1, $ajaxSubmission ?? 0);
echo $form->label('ajaxSubmission', t('AJAX Submission?'));
?>
        </div>
    </div>

    <div class="form-group" id="ns-thank-you-page-group">
        <?php
    echo $form->label('thankYouPageID', t('Thank You Page'));
echo \Core::make('helper/form/page_selector')->selectPage('thankYouPageID', $thankYouPageID ?? null);
?>
        <div class="form-text"><?php echo t('Visitors are sent here after subscribing. Required unless AJAX submission is enabled.'); ?></div>
    </div>
</fieldset>

<fieldset>
    <legend><?php echo t('Form Information'); ?></legend>

    <div class="form-group">
        <?php
echo $form->label('placeholder', t('Placeholder Text'));
echo $form->text('placeholder', $placeholder ?? null);
?>
    </div>

    <div class="form-group">
        <?php
    echo $form->label('buttonText', t('Button Text'));
echo $form->text('buttonText', $buttonText ?? null);
?>
    </div>

    <div class="form-group">
        <?php
    echo $form->label('helpText', t('Help Text'));
echo $editor->outputStandardEditor('helpText', $helpText ?? null);
?>
    </div>
</fieldset>

<script>
(function() {
    // AJAX shows the success message in place; otherwise visitors go to the thank you page
    const ajax = document.getElementById('ajaxSubmission');
    const sync = function() {
        document.getElementById('ns-thank-you-page-group').classList.toggle('d-none', ajax.checked);
        document.getElementById('ns-success-message-group').classList.toggle('d-none', !ajax.checked);
    };
    ajax.addEventListener('change', sync);
    sync();
})();
</script>
