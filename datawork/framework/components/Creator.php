<?php

class Creator
{
    // single instance
    private static $_instance;

    private $_instances;

    public static function getInstance()
    {
        if(!(self::$_instance instanceof self))
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function __clone(){
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    public function spawn($name, $create = false)
    {
        $class = ucfirst($name);

        if(isset($_instances[$name]))
        {
            return $_instances[$name];
        }
        else
        {
            // extension
            if(class_exists($class, false))
            {
                $_instances[$name] = new $class; 
            }
            // manager
            elseif(class_exists($class .= 'Manager', true))
            {
                $_instances[$name] = new $class;
            }
            else
            {
                throw new CException(Yii::t('yii', $class.' not exist , could not spawn a instance'));
            }

            return $_instances[$name];
        }
    }
}