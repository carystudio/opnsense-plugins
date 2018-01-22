<?php

abstract class Csbackend
{
    private static $ERRORCODE;
    
    public static function getErrors(){
        return static::$ERRORCODE;
    }
}