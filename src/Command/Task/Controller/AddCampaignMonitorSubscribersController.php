<?php

namespace NewsletterSignupKit\Command\Task\Controller;

use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Command\Task\Input\InputInterface;
use Concrete\Core\Command\Task\Runner\ProcessTaskRunner;
use Concrete\Core\Command\Task\Runner\TaskRunnerInterface;
use Concrete\Core\Command\Task\Controller\AbstractController;
use NewsletterSignupKit\Command\AddCampaignMonitorSubscribersCommand;

class AddCampaignMonitorSubscribersController extends AbstractController
{
    public function getName(): string
    {
        return t('Add Campaign Monitor Subscribers');
    }

    public function getDescription(): string
    {
        return t('Add unprocessed subscribers to Campaign Monitor.');
    }

    public function getTaskRunner(TaskInterface $task, InputInterface $input): TaskRunnerInterface
    {
        return new ProcessTaskRunner(
            $task,
            new AddCampaignMonitorSubscribersCommand(),
            $input,
            t('Adding subscribers to Campaign Monitor...'),
        );
    }
}
