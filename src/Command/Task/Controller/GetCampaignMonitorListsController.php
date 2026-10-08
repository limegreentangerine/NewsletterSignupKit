<?php

namespace NewsletterSignupKit\Command\Task\Controller;

use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Command\Task\Input\InputInterface;
use Concrete\Core\Command\Task\Runner\ProcessTaskRunner;
use Concrete\Core\Command\Task\Runner\TaskRunnerInterface;
use Concrete\Core\Command\Task\Controller\AbstractController;
use NewsletterSignupKit\Command\GetCampaignMonitorListsCommand;

class GetCampaignMonitorListsController extends AbstractController
{
    public function getName(): string
    {
        return t('Get Campaign Monitor Lists');
    }

    public function getDescription(): string
    {
        return t('Get all mailing lists from Campaign Monitor.');
    }

    public function getTaskRunner(TaskInterface $task, InputInterface $input): TaskRunnerInterface
    {
        return new ProcessTaskRunner(
            $task,
            new GetCampaignMonitorListsCommand(),
            $input,
            t('Getting mailing lists from Campaign Monitor...'),
        );
    }
}
