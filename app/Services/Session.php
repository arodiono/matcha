<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/19/18
 * Time: 3:36 AM
 */

namespace App\Services;


class Session
{
    // the id of the client session;
    private $id;

    // take a session Id and continue a session if empty create a new session
    public function __construct($PHPSESSID = ''){

        // if we have a valid session id containing letters and numbers then use it
        // @note add check for weird characters later
        if (strlen($PHPSESSID) > 5 && preg_match('/[A-Za-z]/', $PHPSESSID) && preg_match('/[0-9]/', $PHPSESSID)){
            session_id($PHPSESSID);
        } else{
            session_regenerate_id(); // so we get a new session id for the new user
        }

        @session_start();
        $this->id = session_id();
        unset($_SESSION); // so we cant attempt to use this directly
        session_write_close();
    }

    // return the value of a session variable or null
    public function get($var_name){
        session_id($this->id);
        @session_start();

        if(isset($_SESSION[$var_name])){
            $var =  $_SESSION[$var_name];
        } else{
            $var = NULL;
        }

        unset($_SESSION);
        session_write_close();

        return $var;
    }

    // set the value of a session variable
    public function set($var_name, $var_value){
        session_id($this->id);
        @session_start();

        $_SESSION[$var_name] = $var_value;

        unset($_SESSION);
        session_write_close();
    }
}