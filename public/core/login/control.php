<?php 
/**
 *  File: control.php
 *  Author: Crece Consultores
 * 
 * 
 *      1. Valida al usuario
 *      2. Establece la sesion
 * 
 *  Nombre de la sesion: %sistema%_%modulo%
 *  Variables de sesion:
 *      $_SESSION["%sistema%_%usuario%_id"] : id unico de la cuenta.
 *      $_SESSION["%sistema%_%usuario%_username"] : nombre de usuario.
 *	$_SESSION["%sistema%_%usuario%_name"] : nombre del usuario.
 *      $_SESSION["%sistema%_%usuario%_level"] : nivel de la cuenta.
 *      $_SESSION["%sistema%_%usuario%_email"] : email de la cuenta.
 *      $_SESSION["%sistema%_%usuario%_session_id"] : identificador de la sesion.
 *      $_SESSION["%sistema%_%usuario%_session_time"] : timestamp de la creacion de la sesion.
 * 
 *  Entrada:
 *      $_POST["username"]
 *      $_POST["password"]
 * 
 * Salida:
 *      		
 *      
 */
header('Content-Type: application/json');

require_once '../sys/init.php';

/**
 * response
 */
$response = new responseClass();

/**
 * input
 */
$loginUsername = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$loginPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

if(empty($loginUsername) || empty($loginPassword)){
    $respose->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

/**
 * connection
 */
try {
    $loginPDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
    
    $loginQuery = $loginPDO->prepare('SELECT * FROM administrator WHERE username = :username LIMIT 1');
    $loginQuery->execute(array(
        'username' => $loginUsername
    ));

    $loginResult = $loginQuery->fetch(PDO::FETCH_OBJ);
            
    if(!empty($loginResult)){
        if(hash_equals(crypt($loginPassword,'$2y$10$'.$loginResult->salt.'$'),$loginResult->password)){
            $_SESSION[SYSSESSION.'_'.SYSMODULE.'_id'] = $loginResult->id;
            $_SESSION[SYSSESSION.'_'.SYSMODULE.'_username'] = $loginResult->username;
            $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] = $loginResult->level;
            $_SESSION[SYSSESSION.'_'.SYSMODULE.'_name'] = $loginResult->name;
            $_SESSION[SYSSESSION.'_'.SYSMODULE.'_email'] = $loginResult->email;
            $_SESSION[SYSSESSION.'_'.SYSMODULE.'_session_id'] = session_id();
            $_SESSION[SYSSESSION.'_'.SYSMODULE.'_session_time'] = time();
           
            $response->setResponse(true,'LOGIN_SUCCESS');
            echo json_encode($response);
            die;
        }else{
            $response->setResponse(false,'LOGIN_PASSWORD_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            echo json_encode($response);
            die;
        }
    }else{
        $response->setResponse(false,'LOGIN_USERNAME_ERROR');
        $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        echo json_encode($response);
        die;
    }     
} catch (PDOException $e) {
    $response->setResponse(false,'DATABASE_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    $response->setException($e);
    echo json_encode($response);
    die;
}