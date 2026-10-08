<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>
<?php
$form = \Core::make('helper/form');
$bID = (int) $bID;
$jsString = static fn($value): string => json_encode((string) $value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
?>

<section id="mc_section_<?php echo $bID; ?>" class="block__mailchimp-signup">
    <div class="container">
        <div class="row g-5 g-lg-4 align-items-start justify-content-between">
            <div class="col-12 col-lg-5">
                <?php if (strlen($title) > 0 || strlen($content) > 0) { ?>
                    <?php if (strlen($title) > 0) { ?>
                        <h3><?php echo h($title); ?></h3>
                    <?php } ?>
                    <?php if (strlen($content) > 0) {
                        echo $content;
                    } ?>
                <?php } ?>
            </div>
            <div class="col-12 col-lg-6">
                <div class="mc-feedback" role="status" aria-live="polite"></div>
                
                <form id="mc_form_<?php echo $bID; ?>" action="<?php echo h($this->action('subscribe')); ?>#mc_<?php echo $bID; ?>" method="post">
                    <fieldset>
                        <?php
                            echo $form->hidden('listId', $subscriptionListId);
echo $form->hidden('ajax', (int) $ajaxSubmission);
?>
                    </fieldset>
                    <fieldset>
                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <?php
                echo $form->label('firstName', t('First name'));
echo $form->text('firstName', $formData['firstName'] ?? '', ['autocomplete' => 'given-name']);
?>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <?php
    echo $form->label('lastName', t('Last name'));
echo $form->text('lastName', $formData['lastName'] ?? '', ['autocomplete' => 'family-name']);
?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <?php
    echo $form->label('email', t('Email address'));
echo $form->email('email', $formData['email'] ?? '', ['autocomplete' => 'email']);
?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary"><?php echo h($buttonText); ?></button>
                    </div>
                </form>
                <?php if (strlen($helpText) > 0) { ?>
                    <div class="text-small"><?php echo $helpText; ?></div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<?php if ($ajaxSubmission) { ?>
<script type="text/javascript">
(function() {
    const form = document.querySelector("#mc_form_<?php echo $bID; ?>");
    const feedbackEl = document.querySelector("#mc_section_<?php echo $bID; ?> .mc-feedback");
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Message is inserted as text unless explicitly flagged as trusted HTML
    function showAlert(type, message, isHtml) {
        const el = document.createElement('div');
        el.className = 'alert alert-' + type;
        if (isHtml) {
            el.innerHTML = message;
        } else {
            el.textContent = message;
        }
        feedbackEl.replaceChildren(el);
    }

    form.addEventListener("submit", async function(e) {
        e.preventDefault();
        
        // Clear previous feedback
        feedbackEl.innerHTML = '';
        feedbackEl.className = 'mc-feedback';
        
        // Disable button and show loading state
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = <?php echo $jsString(t('Submitting...')); ?>;
        
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                
                if (data.success) {
                    // successMessage is admin-authored rich text, escaped for the JS context
                    showAlert('success', <?php echo $jsString($successMessage); ?>, true);
                    form.reset();
                } else {
                    showAlert('danger', data.message);
                }
            } else {
                // Fallback for HTML response (traditional ConcreteCMS pattern)
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const alert = doc.querySelector('.alert');
                
                if (alert) {
                    showAlert(alert.classList.contains('alert-success') ? 'success' : 'danger', alert.textContent);
                    if (alert.classList.contains('alert-success')) {
                        form.reset();
                    }
                } else {
                    showAlert('danger', <?php echo $jsString(t('An unexpected error occurred.')); ?>);
                }
            }
            
        } catch (error) {
            console.error('Subscription error:', error);
            showAlert('danger', <?php echo $jsString(t('Network error. Please check your connection and try again.')); ?>);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
})();
</script>
<?php } ?>