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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FlowTools\Exporter\Factory;
use FlowTools\Exporter\Handler;

class ConvertCommand extends Command
{
    protected function configure()
    {
        $this->setName('convert')
            ->setDescription('Convert a flow to another')
            ->setDefinition(
                array(
                    new InputArgument('input', InputArgument::REQUIRED, 'The input file'),
                    new InputArgument('output', InputArgument::REQUIRED, 'The output file'),
                    new InputOption('in', 'i', InputOption::VALUE_REQUIRED, 'type of the input file'),
                    new InputOption('out', 'o', InputOption::VALUE_REQUIRED, 'type of the output file'),
                    ////////////////////
                    // Source options //
                    ////////////////////
                    new InputOption('in-headers', '', InputOption::VALUE_REQUIRED, 'Indicates if input file has headers', true),
                    // CSV
                    new InputOption('in-delimiter', '', InputOption::VALUE_REQUIRED, 'csv input file delimiter', ','),
                    new InputOption('in-enclosure', '', InputOption::VALUE_REQUIRED, 'csv input file enclosure', '"'),
                    new InputOption('in-escape', '', InputOption::VALUE_REQUIRED, 'csv input file escape', '\\'),
                    ////////////////////
                    // Writer options //
                    ////////////////////
                    new InputOption('out-headers', '', InputOption::VALUE_REQUIRED, 'Indicates if output file must have headers', true),
                    // CSV
                    new InputOption('out-delimiter', '', InputOption::VALUE_REQUIRED, 'csv output file delimiter', ','),
                    new InputOption('out-enclosure', '', InputOption::VALUE_REQUIRED, 'csv output file enclosure', '"'),
                    new InputOption('out-escape', '', InputOption::VALUE_REQUIRED, 'csv output file escape', '\\'),
                    // Xml
                    new InputOption('xml-element-main', '', InputOption::VALUE_REQUIRED, 'Main element', 'datas'),
                    new InputOption('xml-element-child', '', InputOption::VALUE_REQUIRED, 'Child element', 'data'),
                )
            )->setHelp(
<<<EOF
The <info>%command.name%</info> command convert a flow to another.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inType = $input->getOption('in');
        if (is_null($inType)) {
            $inType = $this->getFileExtension($input->getArgument('input'));
            if ($inType == 'xml') {
                // ambiguous
                throw new \InvalidArgumentException(sprintf('You must define the input type via the --in option for xml files'));
            }
        }
        $outType = $input->getOption('out');
        if (is_null($outType)) {
            $outType = $this->getFileExtension($input->getArgument('output'));
            if ($outType == 'xml') {
                // ambiguous
                throw new \InvalidArgumentException(sprintf('You must define the output type via the --out option for xml files'));
            }
        }
        // check files
        if (!is_file($input->getArgument('input'))) {
            throw new \RuntimeException(sprintf('The file "%s" does not exist.', $input->getArgument('input')));
        }
        if (is_file($input->getArgument('output'))) {
            throw new \RuntimeException(sprintf('The file "%s" already exist.', $input->getArgument('output')));
        }
        // check writable output
        if (!touch($input->getArgument('output'))) {
            throw new \RuntimeException(sprintf('The file "%s" is not writable.', $input->getArgument('output')));
        } else {
            unlink($input->getArgument('output'));
        }
        $source = Factory::create(
            array(
                'direction' => 'in',
                'type' => $inType,
                'filename' => $input->getArgument('input'),
                'headers' => $input->getOption('in-headers'),
                'delimiter' => $input->getOption('in-delimiter'),
                'enclosure' => $input->getOption('in-enclosure'),
                'escape' => $input->getOption('in-escape'),
                // xml
                'main_element' => $input->getOption('xml-element-main'),
                'child_element' => $input->getOption('xml-element-child'),
                'columns_type' => null,
            )
        );
        $writer = Factory::create(
            array(
                'direction' => 'out',
                'type' => $outType,
                'filename' => $input->getArgument('output'),
                'headers' => $input->getOption('out-headers'),
                'delimiter' => $input->getOption('out-delimiter'),
                'enclosure' => $input->getOption('out-enclosure'),
                'escape' => $input->getOption('out-escape'),
                // xml
                'main_element' => $input->getOption('xml-element-main'),
                'child_element' => $input->getOption('xml-element-child'),
                'columns_type' => null,
            )
        );
        Handler::create($source, $writer)->export();
    }

    /**
     * Gets the file extension
     *
     * @param string $fileName
     *
     * @return string
     */
    protected function getFileExtension($fileName)
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }
}
