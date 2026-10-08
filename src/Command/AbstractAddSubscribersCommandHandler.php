<?php

namespace NewsletterSignupKit\Command;

use Core;
use NewsletterSignupKit\Config\Env;
use Doctrine\ORM\EntityManagerInterface;
use NewsletterSignupKit\Entity\AbstractSubscriber;
use Concrete\Core\Command\Task\Output\{OutputAwareInterface, OutputAwareTrait};

/**
 * Sends the unprocessed subscribers (processed = 0) of one newsletter provider
 * to that provider.
 *
 * Providers supply the config and the API call; this class does the query,
 * marks each subscriber processed, and handles output and logging.
 *
 * @template TSubscriber of AbstractSubscriber
 */
abstract class AbstractAddSubscribersCommandHandler implements OutputAwareInterface
{
    use OutputAwareTrait;

    /**
     * @return class-string<TSubscriber>
     */
    abstract protected function getSubscriberClass(): string;

    /**
     * @return class-string<\ClassKit\Log\Logger>
     */
    abstract protected function getLoggerClass(): string;

    abstract protected function getConfig(): Env;

    abstract protected function getMissingConfigMessage(): string;

    /**
     * Add one subscriber to the provider.
     *
     * Must throw if the provider didn't accept the subscriber, so it stays
     * unprocessed and is retried on the next run.
     *
     * @param TSubscriber $subscriber
     */
    abstract protected function addSubscriber(AbstractSubscriber $subscriber): void;

    public function importSubscribers(): bool
    {
        $added = 0;
        $failed = 0;

        $logger = Core::make($this->getLoggerClass())->getLogger();

        if (!$this->getConfig()->isConfigured()) {
            $this->output->write($this->getMissingConfigMessage());

            return false;
        }

        $entityManager = Core::make(EntityManagerInterface::class);

        $this->output->write(t('Finding unprocessed subscribers...'));
        $subscribers = $entityManager->getRepository($this->getSubscriberClass())->findBy(['processed' => 0]);

        if (count($subscribers) === 0) {
            $this->output->write(t('No unprocessed subscribers found.'));
        }

        foreach ($subscribers as $subscriber) {
            try {
                $this->addSubscriber($subscriber);
            } catch (\Throwable $e) {
                ++$failed;
                $logger->addError(t('Could not add %s to list %s: %s', $subscriber->getEmail(), $subscriber->getListId(), $e->getMessage()));
                $this->output->write(t('Could not add %s to list %s: %s', $subscriber->getEmail(), $subscriber->getListId(), $e->getMessage()));

                continue;
            }

            // Flush per subscriber so a later failure can't cause this one to be sent again
            $subscriber->setProcessed(1);
            $entityManager->flush();

            ++$added;
            $this->output->write(t('Subscriber added (%s to list %s)', $subscriber->getEmail(), $subscriber->getListId()));
        }

        $logger->addInfo(t('%s subscribers added. %s failed.', $added, $failed));
        $this->output->write(t('%s subscribers added. %s failed.', $added, $failed));

        return $failed === 0;
    }
}
