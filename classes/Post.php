<?php

abstract class Post
{
    protected $_isValid = TRUE;
    protected $_data = array();
    protected $_fields = array();

    public function __construct()
    {
        foreach ($this->_fields as $name => $definition)
        {
            if (isset($_POST[$name]) && $_POST[$name])
            {
                $this->data[$name] = $_POST[$name];
            }
            else if ($definition['required'])
            {
                $this->_isValid = FALSE;
            }
        }
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return isset($this->_data[$name]) ? $this->_data[$name] : NULL;
    }

    /**
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->_isValid;
    }
}
