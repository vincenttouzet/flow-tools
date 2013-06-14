<?php

/*
* This file is part of the Flow Tools utility.
*
* (c) Vincent Touzet <vincent.touzet@gmail.com>
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

namespace FlowTools\Tests\Console\Command;

use FlowTools\Console\Application;
use FlowTools\Console\Command\CompileCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CompileCommandTest extends \PHPUnit_Framework_TestCase
{
    protected $filename = 'flow-tools.phar';
    protected $command = null;
    protected $commandTester = null;

    public function setUp()
    {
        $application = new Application();
        $application->add(new CompileCommand());

        $this->command = $application->find('compile');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
            )
        );

        $this->assertTrue(file_exists($this->filename));
    }

    public function testOutOption()
    {
        $this->filename = 'ft.phar';
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
                '--out' => $this->filename,
            )
        );

        $this->assertTrue(file_exists($this->filename));
    }

    public function tearDown()
    {
        unlink($this->filename);
    }
}
