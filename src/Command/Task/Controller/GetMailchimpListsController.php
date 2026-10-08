<?php

namespace NewsletterSignupKit\Command\Task\Controller;

use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Command\Task\Input\InputInterface;
use Concrete\Core\Command\Task\Runner\ProcessTaskRunner;
use NewsletterSignupKit\Command\GetMailchimpListsCommand;
use Concrete\Core\Command\Task\Runner\TaskRunnerInterface;
use Concrete\Core\Command\Task\Controller\AbstractController;

class GetMailchimpListsController extends AbstractController
{
    public function getName(): string
    {
        return t('Get Mailchimp Lists');
    }

    public function getDescription(): string
    {
        return t('Get all mailing lists from Mailchimp.');
    }

    public function getTaskRunner(TaskInterface $task, InputInterface $input): TaskRunnerInterface
    {
        return new ProcessTaskRunner(
            $task,
            new GetMailchimpListsCommand(),
            $input,
            t('Getting mailing lists from Mailchimp...'),
        );
    }
}
