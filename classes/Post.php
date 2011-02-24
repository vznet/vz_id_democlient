<?php

abstract class Post
{
    protected $isValid = true;
    protected $data = array();
    protected $fields = array();

    public function __construct()
    {
        foreach ($this->fields as $name => $definition)
        {
/*
            $validatorFunction = 'is' . ucfirst($definition['type']);
            var_dump($validatorFunction);
            $validatorFunction($_POST[$name], $definition);
*/
            if (isset($_POST[$name]) && $_POST[$name])
            {
                $this->data[$name] = $_POST[$name];
            }
            else if ($definition['required'])
            {
                $this->isValid = false;
            }
        }
    }

    protected function isString($string, $definition)
    {
    }

    public function isValid()
    {
        return $this->isValid;
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
}
