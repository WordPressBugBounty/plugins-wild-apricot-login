<?php

class WA_ClassLoader
{
    private $classesPath;
    private $classNamePrefix;
    private $classNameSeparator;
    private $pathSeparator;
    private $phpFileExtension;

    public function __construct($classesPath, $classNamePrefix, $classNameSeparator, $pathSeparator, $phpFileExtension)
    {
        $this->classesPath = $classesPath;
        $this->classNamePrefix = $classNamePrefix;
        $this->classNameSeparator = $classNameSeparator;
        $this->pathSeparator = $pathSeparator;
        $this->phpFileExtension = $phpFileExtension;

        $this->registerClassLoader();
    }

    private function registerClassLoader()
    {
        if (!spl_autoload_register(array($this, 'loadClass'), false))
        {
            throw new Exception('Unable to register class loader.');
        }
    }

    public function loadClass($className)
    {
        $classPathParts = explode($this->classNameSeparator, $className);

        if (count($classPathParts) == 1 || $classPathParts[0] != $this->classNamePrefix) { return; }

        array_shift($classPathParts);

        $classPath = $this->classesPath . implode($this->pathSeparator, $classPathParts);
        $classFile = $classPath . $this->phpFileExtension;

        if (!file_exists($classFile))
        {
            throw new Exception('Cannot find file for class "' . $className . '".');
        }

        require_once($classFile);

        if (!class_exists($className) && !interface_exists($className))
        {
            throw new Exception('Cannot find declaration for "' . $className . '" in file.');
        }
    }
} 