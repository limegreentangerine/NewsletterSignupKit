<?php defined('C5_EXECUTE') or die('Access Denied.');
/**
 * @var array<string, string>                                             $providers Configured providers, key => label
 * @var string|null                                                       $provider  Selected provider key
 * @var array                                                             $items
 * @var \Concrete\Core\Search\Result\Result|null                          $result
 * @var string                                                            $pagination
 * @var string                                                            $name
 * @var int|null                                                          $num_results
 * @var array                                                             $allowed_num_results
 * @var \Concrete\Core\Validation\CSRF\Token|null                         $token
 * @var string|null                                                       $bulkToken
 * @var \Concrete\Core\Application\UserInterface\ContextMenu\DropdownMenu $resultsBulkMenu
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
        <div id="ccm-search-results-table" style="position:relative;">
            <table class="ccm-search-results-table" data-search-results="mailing-lists">
                <thead>
                    <tr>
                        <th class="ccm-search-results-bulk-selector" colspan="1">
                            <div class="btn-group dropdown">
                                <span class="btn btn-secondary" data-search-checkbox-button="select-all">
                                    <input type="checkbox" data-search-checkbox="select-all" aria-label="<?php echo t('Select all'); ?>">
                                </span>
                                <button type="button" disabled="disabled" data-search-checkbox-button="dropdown" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" data-reference="parent">
                                    <span class="sr-only"><?php echo t('Toggle Dropdown'); ?></span>
                                </button>
                                <?php echo $resultsBulkMenu->getMenuElement(); ?>
                            </div>
                        </th>
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
                            <td class="ccm-search-results-checkbox" colspan="1">
                                <input data-search-checkbox="individual" type="checkbox" data-item-id="<?php echo h($item->getID()); ?>" aria-label="<?php echo t('Select'); ?>">
                            </td>
                            <?php foreach ($item->getColumns() as $column) { ?>
                                <td><?php echo h($column->getColumnValue()); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination) { ?>
            <div class="ccm-search-results-pagination"><?php echo $pagination; ?></div>
        <?php } ?>

        <script>
            (function ($) {
                $(function () {
                    var table = new ConcreteSearchResultsTable($('#ccm-search-results-table'), {bulkParameterName: 'ids'});
                    table.setupBulkActions();
                    // Post the ticked list IDs, then reload so the results (and the flash message) reflect the change.
                    table.handleSelectedBulkAction = function (action, type, $link, ids) {
                        $.concreteAjax({
                            url: $link.attr('data-bulk-action-url'),
                            method: 'POST',
                            data: {ccm_token: <?php echo json_encode($bulkToken); ?>, ids: ids},
                            success: function () {
                                window.location.reload();
                            }
                        });
                    };
                });
            })(jQuery);
        </script>
    <?php } ?>
<?php } ?>
