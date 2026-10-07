<?php

namespace NewsletterSignupKit\Command;

use Core;
use NewsletterSignupKit\Config\Env;
use Doctrine\ORM\EntityManagerInterface;
use NewsletterSignupKit\Entity\AbstractList;
use Concrete\Core\Command\Task\Output\{OutputAwareInterface, OutputAwareTrait};

/**
 * Imports the mailing lists of one newsletter provider into the nsLists table.
 *
 * Providers supply the config, the API fetch and any provider-specific fields;
 * this class does the add/update/remove sync, output and logging.
 *
 * @template TList of AbstractList
 */
abstract class AbstractGetListsCommandHandler implements OutputAwareInterface
{
    use OutputAwareTrait;

    protected EntityManagerInterface $entityManager;

    /**
     * @return class-string<TList>
     */
    abstract protected function getListClass(): string;

    /**
     * @return class-string<\ClassKit\Log\Logger>
     */
    abstract protected function getLoggerClass(): string;

    abstract protected function getConfig(): Env;

    abstract protected function getMissingConfigMessage(): string;

    /**
     * Fetch every list from the provider.
     *
     * Must throw if the provider can't be fully read, so a failed or partial
     * fetch never causes existing lists to be removed.
     *
     * @return iterable<array{id: string, name: string}> Extra keys are passed to hydrateList()
     */
    abstract protected function fetchLists(): iterable;

    /**
     * Set provider-specific fields on a list from the fetched data.
     *
     * @param TList $list
     */
    protected function hydrateList(AbstractList $list, array $data): void {}

    public function importLists(): bool
    {
        $count = 0;
        $deleted = 0;

        $logger = Core::make($this->getLoggerClass())->getLogger();

        if (!$this->getConfig()->isConfigured()) {
            $this->output->write($this->getMissingConfigMessage());

            return false;
        }

        $this->entityManager = Core::make(EntityManagerInterface::class);
        $listClass = $this->getListClass();

        $this->output->write(t('Creating list of existing mailing lists...'));
        $existing = [];
        foreach ($this->entityManager->getRepository($listClass)->findAll() as $list) {
            $existing[$list->getListId()] = $list;
        }

        try {
            $found = 0;
            foreach ($this->fetchLists() as $data) {
                ++$found;
                $list = $existing[$data['id']] ?? null;

                if ($list === null) {
                    $list = new $listClass();
                    $list->setShowInForms(0);
                    ++$count;
                }

                $list->setName($data['name']);
                $list->setListId($data['id']);
                $this->hydrateList($list, $data);

                $this->entityManager->persist($list);
                $this->output->write(t('Mailing list added or updated (ID: %s)', $data['id']));

                unset($existing[$data['id']]);
            }

            if ($found === 0) {
                $this->output->write(t('No mailing lists found.'));
            }
        } catch (\Throwable $e) {
            $logger->addError($e->getMessage());
            $this->output->write(t('Importing mailing lists failed: %s', $e->getMessage()));

            return false;
        }

        if (count($existing) > 0) {
            $this->output->write(t('Removing old mailing lists...'));
            foreach ($existing as $list) {
                $this->entityManager->remove($list);
                ++$deleted;
            }
        }
        $this->entityManager->flush();

        $logger->addInfo(t('%s mailing lists imported. %s lists deleted.', $count, $deleted));
        $this->output->write(t('%s mailing lists imported. %s lists deleted.', $count, $deleted));

        return true;
    }
}
