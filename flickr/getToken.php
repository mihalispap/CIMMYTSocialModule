<?php
    /* Last updated with phpFlickr 1.4
     *
     * If you need your app to always login with the same user (to see your private
     * photos or photosets, for example), you can use this file to login and get a
     * token assigned so that you can hard code the token to be used.  To use this
     * use the phpFlickr::setToken() function whenever you create an instance of 
     * the class.
     *
     * @sookoll (2016-04-05): Point this file as auth callback, fixed redirect loop,
     *                        Copy token from var_dump output
     */

	 $frob="72157670661413351-d2a7b42791593c84-144022562";
	 
	 $api_key = "68e9a4df375a157a22a8d79e4e9a43bb";
    $api_secret = "3b4e45208b0d7aaf";
	
    session_start();

    require_once("phpFlickr.php");
    $f = new phpFlickr( $api_key, $api_secret);

    if(empty($_GET['frob'])) {
        //change this to the permissions you will need
        $f->auth("write");
    } else {
        $t = $f->auth_getToken($_GET['frob']);
        var_dump($t);
    }
