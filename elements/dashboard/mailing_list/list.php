<?php defined('C5_EXECUTE') or die('Access Denied.');
/**
 * @var array<string, string>                     $providers Configured providers, key => label
 * @var string|null                               $provider  Selected provider key
 * @var array                                     $items
 * @var \Concrete\Core\Search\Result\Result|null  $result
 * @var string                                    $pagination
 * @var string                                    $name
 * @var int|null                                  $num_results
 * @var array                                     $allowed_num_results
 * @var \Concrete\Core\Validation\CSRF\Token|null $token
 */
?>

<?php if ($providers === []) { ?>
    <div class="alert alert-warning">
        <?php echo t('No newsletter provider is configured. Add its API settings to the site\'s %s file; see %s.', '<code>.env</code>', '<a href="' . h($view->url('/dashboard/newsletter_signup/settings')) . '">' . t('Settings') . '</a>'); ?>
    </div>
<?php } else { ?>
    <?php if (count($providers) > 1) { ?>
        <ul class="nav nav-tabs mb-3">
            <?php foreach ($providers as $key => $label) { ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $key === $provider ? 'active' : ''; ?>" href="<?php echo h($view->url('/dashboard/newsletter_signup/lists') . '?provider=' . $key); ?>"><?php echo h($label); ?></a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <form method="post" action="<?php echo h($view->url('/dashboard/newsletter_signup/lists') . '?provider=' . $provider); ?>" class="row row-cols-auto g-3 mb-3 align-items-center">
        <?php echo $token->output('list-search'); ?>
        <div class="col">
            <input type="text" name="name" value="<?php echo h($name); ?>" class="form-control" placeholder="<?php echo t('Search by name'); ?>">
        </div>
        <div class="col">
            <select name="num_results" class="form-select">
                <?php foreach ($allowed_num_results as $size) { ?>
                    <option value="<?php echo (int) $size; ?>" <?php echo (int) $size === $num_results ? 'selected' : ''; ?>><?php echo t('%d per page', $size); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col">
            <button type="submit" class="btn btn-primary"><?php echo t('Search'); ?></button>
        </div>
    </form>

    <?php if (empty($items)) { ?>
        <div class="alert alert-warning"><?php echo t('No mailing lists found. Run the scheduled task to import them.'); ?></div>
    <?php } else { ?>
        <form method="post" action="<?php echo h($view->action('bulk')); ?>" id="nsk-bulk-form">
        <?php echo $bulkToken; ?>
        <input type="hidden" name="provider" value="<?php echo h($provider); ?>">
        <div class="row row-cols-auto g-2 mb-3 align-items-center">
            <div class="col">
                <select name="bulk_action" class="form-select" aria-label="<?php echo t('Bulk action'); ?>">
                    <option value=""><?php echo t('Bulk action...'); ?></option>
                    <option value="show"><?php echo t('Show in forms'); ?></option>
                    <option value="hide"><?php echo t('Hide from forms'); ?></option>
                </select>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-secondary" id="nsk-bulk-apply" disabled><?php echo t('Apply to selected'); ?></button>
            </div>
            <div class="col text-muted small" id="nsk-bulk-count"></div>
        </div>
        <div class="table-responsive">
            <table class="ccm-search-results-table">
                <thead>
                    <tr>
                        <th style="width: 1%;"><input type="checkbox" class="form-check-input" id="nsk-select-all" aria-label="<?php echo t('Select all'); ?>"></th>
                        <?php foreach ($result->getColumns() as $column) { ?>
                            <?php if ($column->isColumnSortable()) { ?>
                                <th class="<?php echo $column->getColumnStyleClass(); ?>">
                                    <a href="<?php echo $column->getColumnSortURL(); ?>"><?php echo h($column->getColumnTitle()); ?></a>
                                </th>
                            <?php } else { ?>
                                <th><span><?php echo h($column->getColumnTitle()); ?></span></th>
                            <?php } ?>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) { ?>
                        <tr data-details-url="<?php echo h($item->getViewUrl()); ?>">
                            <td><input type="checkbox" class="form-check-input nsk-select" name="ids[]" value="<?php echo h($item->getID()); ?>" aria-label="<?php echo t('Select'); ?>"></td>
                            <?php foreach ($item->getColumns() as $column) { ?>
                                <td><?php echo h($column->getColumnValue()); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        </form>

        <?php if ($pagination) { ?>
            <div class="ccm-search-results-pagination"><?php echo $pagination; ?></div>
        <?php } ?>

        <script>
            (function () {
                var form = document.getElementById('nsk-bulk-form');
                var all = document.getElementById('nsk-select-all');
                var apply = document.getElementById('nsk-bulk-apply');
                var count = document.getElementById('nsk-bulk-count');
                var boxes = form.querySelectorAll('.nsk-select');

                function sync() {
                    var n = form.querySelectorAll('.nsk-select:checked').length;
                    apply.disabled = n === 0;
                    count.textContent = n ? n + ' <?php echo h(t('selected')); ?>' : '';
                    all.checked = n > 0 && n === boxes.length;
                    all.indeterminate = n > 0 && n < boxes.length;
                }

                all.addEventListener('change', function () {
                    boxes.forEach(function (b) { b.checked = all.checked; });
                    sync();
                });
                boxes.forEach(function (b) {
                    b.addEventListener('change', sync);
                });
                // Don't let ticking a box trigger the row's click-through to the details page.
                form.querySelectorAll('td:first-child, th:first-child').forEach(function (c) {
                    c.addEventListener('click', function (e) { e.stopPropagation(); });
                });
                sync();
            })();
        </script>
    <?php } ?>
<?php } ?>
