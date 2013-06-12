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
use FlowTools\Console\Command\ConvertCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ConvertCommandTest extends \PHPUnit_Framework_TestCase
{
    protected $filenameIn = 'foobar.csv';
    protected $filenameOut = 'foobar.json';
    protected $filename = 'file.exist';
    protected $command = null;
    protected $commandTester = null;

    public function setUp()
    {
        $c = 'sku,name,price
POV157,"Déboucheur",80.90
SAV42,"Mélangeur à tout faire",19.90';
        file_put_contents($this->filenameIn, $c);
        touch($this->filename);
        $application = new Application();
        $application->add(new ConvertCommand());

        $this->command = $application->find('convert');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
                'input' => $this->filenameIn,
                'output' => $this->filenameOut,
            )
        );

        $this->assertRegExp('/2 entries exported/', $this->commandTester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInTypeAmbigous()
    {
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
                'input' => $this->filenameIn.'.xml',
                'output' => $this->filenameOut,
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOutTypeAmbigous()
    {
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
                'input' => $this->filenameIn,
                'output' => $this->filenameOut.'.xml',
            )
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInFileDoesNotExist()
    {
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
                'input' => 'file_that_doesnt_exists',
                'output' => $this->filenameOut,
            )
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testOutFileDoesNotExist()
    {
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
                'input' => $this->filenameIn,
                'output' => $this->filename,
            )
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testOutFileUnwritable()
    {
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
                'input' => $this->filenameIn,
                'output' => '/tmp/flow-tools-unexisting-folder/unwritable-file',
            )
        );
    }
    /*
    public function testExcelColumnsTypeOptionAll()
    {
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
                'input' => $this->filenameIn,
                'output' => $this->filenameOut,
                '--out' => 'excel',
                '--excel-column-types' => 'Number',
            )
        );

        $fileContents = file_get_contents($this->filenameOut);
        preg_match_all('/Type="Number"/', $fileContents, $matches);
        $this->assertEquals(6, count($matches[0]));
    }

    public function testExcelColumnsTypeOptionSpecific()
    {
        $this->commandTester->execute(
            array(
                'command' => $this->command->getName(),
                'input' => $this->filenameIn,
                'output' => $this->filenameOut,
                '--out' => 'excel',
                '--excel-column-types' => 'price:Number',
            )
        );

        $fileContents = file_get_contents($this->filenameOut);
        preg_match_all('/Type="Number"/', $fileContents, $matches);
        $this->assertEquals(2, count($matches[0]));
    }*/

    public function tearDown()
    {
        unlink($this->filenameIn);
        unlink($this->filename);
        if (is_file($this->filenameOut)) {
            unlink($this->filenameOut);
        }
    }
}
