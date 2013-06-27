<?php

/*
* This file is part of the Flow Tools utility.
*
* (c) Vincent Touzet <vincent.touzet@gmail.com>
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace FlowTools\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use FlowTools\Util\Compiler;

/**
 * Compile command
 *
 * @author Vincent Touzet <vincent.touzet@gmail.com>
 */
class CompileCommand extends Command
{
    protected function configure()
    {
        $this->setName('compile')
            ->setDescription('Compile application as a phar file')
            ->setDefinition(
                array(
                    new InputOption('out', 'o', InputOption::VALUE_REQUIRED, 'output file', 'flow-tools.phar'),
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $compiler = new Compiler();
        $compiler->compile($input->getOption('out'));
    }
}
