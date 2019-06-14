<?php
/**
 * Inicio
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
require_once 'classes/responseClass.php';
require_once 'classes/notificationClass.php';
require_once 'classes/csrfTokenClass.php';
require_once 'classes/mailerClass.php';
require_once 'config.php';

/**
 * Funciones
 */
require_once 'functions/slugify.php';
require_once 'functions/queryBuilder.php';
require_once 'functions/removeDirectory.php';
require_once 'functions/nest.php';

/** 
 * Locale 
 */
setlocale (LC_ALL, 'es_ES');

/**
 * Sesion
 */
session_start();
session_name(SYSSESSION.'_'.SYSMODULE.'');

if(!isset($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'])){
    $_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'] = array();
}

if(!isset($_SESSION[SYSSESSION.'_'.SYSMODULE.'_tokens'])){
    $_SESSION[SYSSESSION.'_'.SYSMODULE.'_tokens'] = array();
}

/**
 * Notificaciones
 */
$sessionNotifications = array();
if(!empty($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'])){
    foreach ($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'] as $key => $notification){
        array_push($sessionNotifications, $notification);
        unset($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'][$key]);
    }
}

/**
 * Tokens
 */
if(!$_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf']){
    $_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf'] = new csrfTokenClass(SYSSECRET);
}

/**
 * Usuario
 */
$sessionUser = array(
    'id' => $_SESSION[SYSSESSION.'_'.SYSMODULE.'_id'],
    'username' => $_SESSION[SYSSESSION.'_'.SYSMODULE.'_username'],
    'level' => $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'],
    'name' => $_SESSION[SYSSESSION.'_'.SYSMODULE.'_name'],
    'email' => $_SESSION[SYSSESSION.'_'.SYSMODULE.'_email']
);

/**
 * Twig
 */
$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates');
$twig = new \Twig_Environment($loader, array(
    'debug' => true,
));
$twig->addExtension(new Twig_Extension_Debug());
$twig->addExtension(new Twig_Extensions_Extension_Intl());
$twig->addGlobal('sysConfiguration', $sysConfiguration);
$twig->addGlobal('sessionNotifications', $sessionNotifications);
$twig->addGlobal('sessionUser', $sessionUser);

$twig->addFunction(
    new \Twig_SimpleFunction(
        'csrf_token',
        function($resource) {
            if(!$_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf']){
                $_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf'] = new csrfTokenClass(SYSSECRET);
            }
            return $_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf']->addToken($resource);
        }
    )
);
    
/**
 * mailer
 */
$mailer = new mailerClass(MAILERFROM, MAILERREPLY);