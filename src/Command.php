<?php namespace Amu\Clip;

use Amu\Clip\Annotations\ClassParser;

abstract class Command
{
    private $_console;

    private $_annotationParser;

    abstract public function execute(Input $input, Output $output);

    public function getConsole()
    {
        return $this->_console;
    }

    public function setConsole(Console $console)
    {
        $this->_console = $console;
    }

    public function getName()
    {
        return $this->getClassAnnotation('command') ?: $this->getNameFromClassName();
    }

    public function getDescription()
    {
        return $this->getClassAnnotation('description') ?: '';
    }

    public function getOpts()
    {
        $expected = [];
        $expected['arguments'] = $this->getArguments();
        $expected['options'] = $this->getOptions();
        return $expected;
    }

    protected function getArguments()
    {
        return $this->filterProperties('type', 'argument');
    }

    protected function getOptions()
    {
        return $this->filterProperties('type', 'option');
    }

    protected function filterProperties($key, $value)
    {
        return array_filter($this->getAnnotatedProperties(), function($property) use ($key, $value) {
            return isset($property['annotations'][$key]) && $property['annotations'][$key] === $value;
        });
    }

    protected function getAnnotatedProperties()
    {
        return array_map(function($property){
            if ( isset($property['validate'] ) ) {
                $property['validate'] = $this->parseValidationRules($property['validate']);
            }
            return $property;
        }, $this->_annotationParser->getAnnotatedProperties());
    }

    protected function parseValidationRules($validationString)
    {
        return explode('|', $validationString);
    }

    protected function getNameFromClassName()
    {
        $className = implode('', array_slice(explode('\\', get_class($this)), -1));
        $className = str_replace('Command', '', $className);
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $className, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('-', $ret);
    }

    private function getClassAnnotation($name)
    {
        if (! $this->_annotationParser) {
            $this->_annotationParser = new ClassParser($this);
        }

        return $this->_annotationParser->getClassAnnotation($name);
    }
}