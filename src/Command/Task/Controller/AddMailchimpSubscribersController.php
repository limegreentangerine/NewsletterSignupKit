<?php

namespace NewsletterSignupKit\Command\Task\Controller;

use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Command\Task\Input\InputInterface;
use Concrete\Core\Command\Task\Runner\ProcessTaskRunner;
use Concrete\Core\Command\Task\Runner\TaskRunnerInterface;
use Concrete\Core\Command\Task\Controller\AbstractController;
use NewsletterSignupKit\Command\AddMailchimpSubscribersCommand;

class AddMailchimpSubscribersController extends AbstractController
{
    public function getName(): string
    {
        return t('Add Mailchimp Subscribers');
    }

    public function getDescription(): string
    {
        return t('Add unprocessed subscribers to Mailchimp.');
    }

    public function getTaskRunner(TaskInterface $task, InputInterface $input): TaskRunnerInterface
    {
        return new ProcessTaskRunner(
            $task,
            new AddMailchimpSubscribersCommand(),
            $input,
            t('Adding subscribers to Mailchimp...'),
        );
    }
}
