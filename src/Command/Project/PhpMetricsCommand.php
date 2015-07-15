<?php

/*
 * This file is part of the Jarvis package
 *
 * Copyright (c) 2015 Tony Dubreil
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Tony Dubreil <tonydubreil@gmail.com>
 */

namespace Jarvis\Command\Project;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jarvis\Project\ProjectConfiguration;

class PhpMetricsCommand extends BaseBuildCommand
{
    /**
     * @var string
     */
    private $remoteBuildDir;

    /**
     * @var string
     */
    private $localBuildDir;

    /**
     * @{inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Gives metrics about PHP project and classes for to one or all projects');

        $this->addOption('self-update', null, InputOption::VALUE_NONE, 'Self update phpmetrics');

        parent::configure();
    }

    /**
     * @{inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('self-update')) {
            $this->getSshExec()->exec('wget https://github.com/Halleck45/PhpMetrics/raw/master/build/phpmetrics.phar && chmod +x phpmetrics.phar && mv phpmetrics.phar /usr/local/bin/phpmetrics');

            return;
        }

        // Check already installed
        $this->getSshExec()->exec(
            'test -f /usr/local/bin/phpmetrics || (wget https://github.com/Halleck45/PhpMetrics/raw/master/build/phpmetrics.phar && chmod +x phpmetrics.phar && mv phpmetrics.phar /usr/local/bin/phpmetrics)'
        );

        $this->remoteBuildDir = sprintf('%s/metrics', $this->getRemoteBuildDir());
        $this->localBuildDir = sprintf('%s/metrics', $this->getLocalBuildDir());
    }

    /**
     * @{inheritdoc}
     */
    protected function executeCommandByProject($projectName, ProjectConfiguration $projectConfig, OutputInterface $output)
    {
        $remoteReportFilePath = strtr('%build_dir%/phpmetrics/%project_name%.html', [
            '%project_name%' => $projectConfig->getProjectName(),
            '%build_dir%' => $this->remoteBuildDir
        ]);

        // Analyse source project code
        $this->getSshExec()->exec(
            strtr(
                'mkdir -p %build_dir% && /usr/local/bin/phpmetrics --level=0 --report-html=%report_file% %project_dir%/src'.($output->isDebug() ? ' --verbose' : ''),
                [
                    '%report_file%' => $remoteReportFilePath,
                    '%build_dir%' => $this->remoteBuildDir,
                    '%project_dir%' => $projectConfig->getRemoteWebappDir(),
                ]
            )
        );

        $localReportFilePath = str_replace(
            $this->remoteBuildDir,
            $this->localBuildDir,
            $remoteReportFilePath
        );
        $this->getRemoteFilesystem()->copyRemoteFileToLocal(
            $remoteReportFilePath,
            $localReportFilePath
        );
        if ($this->getLocalFilesystem()->exists($localReportFilePath)) {
            $this->openFile($localReportFilePath);
        }

        return $this->getSshExec()->getLastReturnStatus();
    }
}
