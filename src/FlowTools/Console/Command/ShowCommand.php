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
use FlowTools\Console\Helper\FullScreenTableHelper;

/**
 * Show command
 *
 * @author Vincent Touzet <vincent.touzet@gmail.com>
 */
class ShowCommand extends Command
{
    protected $screenSize = array();

    protected function configure()
    {
        $this->setName('show')
            ->setDescription('Show a flow')
            ->setDefinition(
                array(
                    new InputArgument('input', InputArgument::REQUIRED, 'The input file'),
                    new InputOption('in', 'i', InputOption::VALUE_REQUIRED, 'type of the input file'),
                    ////////////////////
                    // Source options //
                    ////////////////////
                    new InputOption('headers', '', InputOption::VALUE_REQUIRED, 'Indicates if input file has headers', true),
                    // CSV
                    new InputOption('delimiter', '', InputOption::VALUE_REQUIRED, 'csv input file delimiter', ','),
                    new InputOption('enclosure', '', InputOption::VALUE_REQUIRED, 'csv input file enclosure', '"'),
                    new InputOption('escape', '', InputOption::VALUE_REQUIRED, 'csv input file escape', '\\'),
                )
            )->setHelp(
<<<EOF
The <info>%command.name%</info> command show a flow in a table.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        // create SourceIterator
        $inType = $input->getOption('in');
        if (is_null($inType)) {
            $inType = $this->getFileExtension($input->getArgument('input'));
            if ($inType == 'xml') {
                // ambiguous
                throw new \InvalidArgumentException(sprintf('You must define the input type via the --in option for xml files'));
            }
        }
        // check files
        if (!is_file($input->getArgument('input'))) {
            throw new \RuntimeException(sprintf('The file "%s" does not exist.', $input->getArgument('input')));
        }
        $source = Factory::create(
            array(
                'direction' => 'in',
                'type' => $inType,
                'filename' => $input->getArgument('input'),
                'headers' => $input->getOption('headers'),
                'delimiter' => $input->getOption('delimiter'),
                'enclosure' => $input->getOption('enclosure'),
                'escape' => $input->getOption('escape'),
            )
        );
        $position=0;
        $tableHelper = $this->getHelperSet()->get('table');
        $fullScreenTableHelper = new FullScreenTableHelper($tableHelper);
        $nbRowsToLoad = $fullScreenTableHelper->getNbRowsToShow();
        $nbRowsToLoad = 5;
        // load datas
        $datas = array();
        $headers = array();
        foreach ($source as $data) {
            if ($position===$nbRowsToLoad) {
                break;
            }
            if ($position===0) {
                $headers = array_keys($data);
            }
            $datas[]=$data;
            $position++;
        }

        $fullScreenTableHelper->setHeaders($headers);
        $fullScreenTableHelper->setMaxColWidth(floor($fullScreenTableHelper->getScreenWidth()/4));

        $continue = true;
        $render = true;
        while ($continue) {
            $fullScreenTableHelper->setRows($datas);
            if ($render) {
                $fullScreenTableHelper->render($output);
                $render = false;
            }

            $ret = $this->getKeyChar();
            switch (ord($ret)) {
                case 113:
                    $continue = false;
                    break;
                case 65:
                    $nb = $fullScreenTableHelper->getRowOffset()+1;
                    if ($nb>=0) {
                        $fullScreenTableHelper->setRowOffset($nb);
                        $render = true;
                    }
                    break;
                case 66:
                    // load next row
                    if ($source->valid()) {
                        $datas[] = $source->current();
                        $render = true;
                    }
                    $source->next();
                    if ($fullScreenTableHelper->getNbRowsToShow() <= count($datas)) {
                        $nb = $fullScreenTableHelper->getRowOffset()+1;
                        if ($nb < count($datas)) {
                            $fullScreenTableHelper->setRowOffset($nb);
                            $render = true;
                        }
                    }
                    break;
                case 67:
                    $nb = $fullScreenTableHelper->getColOffset()+1;
                    if ($nb < count($headers)) {
                        $fullScreenTableHelper->setColOffset($nb);
                        $render = true;
                    }
                    break;
                case 68:
                    $nb = $fullScreenTableHelper->getColOffset()-1;
                    if ($nb>=0) {
                        $fullScreenTableHelper->setColOffset($nb);
                        $render = true;
                    }
                    break;
            }
        }
    }

    private function getKeyChar()
    {
        shell_exec("stty -icanon");
        $sttyMode = shell_exec('stty -g');

        shell_exec('stty -echo');
        $value = fread(STDIN, 1);
        shell_exec(sprintf('stty %s', $sttyMode));
        return $value;
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
