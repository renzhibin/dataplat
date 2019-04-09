<?php

class Manager
{
	public function __set($name,$value){ 
        $this->$name = $value; 
    } 

    public function __get($name){
        if(isset($this -> $name))
            return $this -> $name;
        else
        {
            return Creator::getInstance()->spawn($name);
        }
    } 
}