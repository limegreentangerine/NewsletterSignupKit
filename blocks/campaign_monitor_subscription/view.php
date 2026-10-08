<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>
<?php
$form = \Core::make('helper/form');
?>

<section id="cm_section_<?php echo $bID; ?>" class="block__newsletter-signup-form">
    <div class="container">
        <div class="row g-5 g-lg-4 align-items-start justify-content-between">
            <div class="col-12 col-lg-5">
                <?php if (strlen($title) > 0 || strlen($content) > 0) { ?>
                    <?php if (strlen($title) > 0) { ?>
                        <h3><?php echo $title; ?></h3>
                    <?php } ?>
                    <?php if (strlen($content) > 0) {
                        echo $content;
                    } ?>
                <?php } ?>
            </div>
            <div class="col-12 col-lg-6">
                <div class="cm-feedback" role="status" aria-live="polite"></div>
                
                <form id="cm_form_<?php echo $bID; ?>" action="<?php echo $this->action('subscribe'); ?>#cm_<?php echo $bID; ?>" method="post">
                    <fieldset>
                        <?php
                            echo $form->hidden('listId', $subscriptionListId);
echo $form->hidden('ajax', $ajaxSubmission);
?>
                    </fieldset>
                    <fieldset>
                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <?php
                echo $form->label('firstName', t('First name'));
echo $form->text('firstName', (isset($formData)) ? $formData['firstName'] : '', ['autocomplete' => 'given-name']);
?>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <?php
    echo $form->label('lastName', t('Last name'));
echo $form->text('lastName', (isset($formData)) ? $formData['lastName'] : '', ['autocomplete' => 'family-name']);
?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <?php
    echo $form->label('email', t('Email address'));
echo $form->email('email', (isset($formData)) ? $formData['email'] : '', ['autocomplete' => 'email']);
?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary"><?php echo $buttonText; ?></button>
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
    const form = document.querySelector("#cm_form_<?php echo $bID; ?>");
    const feedbackEl = document.querySelector("#cm_section_<?php echo $bID; ?> .cm-feedback");
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener("submit", async function(e) {
        e.preventDefault();
        
        // Clear previous feedback
        feedbackEl.innerHTML = '';
        feedbackEl.className = 'cm-feedback';
        
        // Disable button and show loading state
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '<?php echo t('Submitting...'); ?>';
        
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
                    feedbackEl.innerHTML = '<div class="alert alert-success">' + 
                        ('<?php echo $successMessage; ?>') + 
                        '</div>';
                    form.reset();
                } else {
                    feedbackEl.innerHTML = '<div class="alert alert-danger">' + 
                        data.message + 
                        '</div>';
                }
            } else {
                // Fallback for HTML response (traditional ConcreteCMS pattern)
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const alert = doc.querySelector('.alert');
                
                if (alert) {
                    feedbackEl.innerHTML = alert.outerHTML;
                    if (alert.classList.contains('alert-success')) {
                        form.reset();
                    }
                } else {
                    feedbackEl.innerHTML = '<div class="alert alert-danger"><?php echo t('An unexpected error occurred.'); ?></div>';
                }
            }
            
        } catch (error) {
            console.error('Subscription error:', error);
            feedbackEl.innerHTML = '<div class="alert alert-danger"><?php echo t('Network error. Please check your connection and try again.'); ?></div>';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
})();
</script>
<?php } ?>