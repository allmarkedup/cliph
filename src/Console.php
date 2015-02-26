<?php namespace Amu\Clip;

use Amu\Clip\Exception;
use Amu\Clip\Commands\Command;
use Amu\Clip\Commands\ListCommand;

class Console
{
    protected $commands = [];

    protected $defaultCommand = 'list';

    public function __construct()
    {
        $this->addCommand(new ListCommand());
    }

    public function addCommand(Command $command)
    {
        $command->setConsole($this);
        $this->commands[$command->getName()] = $command;
    }

    public function getCommand($name)
    {
        if (isset($this->commands[$name])) {
            return $this->commands[$name];
        }
        throw new Exception('Command not found');
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function run(array $argv = null)
    {
        try {
            $output = new Output();

            if ($argv === null) {
                $argv = isset($_SERVER['argv']) ? array_slice($_SERVER['argv'], 1) : array();
            }

            if (count($argv) === 0 || strpos($argv[0], '-') === 0) {
                $command = $this->getCommand($this->defaultCommand);
            } else {
                $command = $this->getCommand($argv[0]);
                array_shift($argv);
            }

            $input = new Input($argv);

            if ($this->validateInput($input, $command->getOpts())) {
                $command->execute($input, $output);
            }

        } catch (Exception $e) {
            $output->error($e->getMessage());
        }
    }

    public function validateInput(Input $input, $expected)
    {
                echo '<pre>';
                print_r($expected);
                echo '</pre>';
        // throw new Exception("Input error");
    }  
}