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

namespace Jarvis\Command\Vagrant;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestartCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('vagrant:restart');
        $this->setDescription('Restarts vagrant machine, loads new Vagrantfile configuration');

        $this->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Vagrant provider', 'virtualbox');
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Vagrant virtual machine name', 'default');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->executeCommand('vagrant:stop', [], $output);

        $this->getApplication()->executeCommand('vagrant:start', [
            '--provider' => $input->getOption('provider'),
        ], $output);
    }
}
