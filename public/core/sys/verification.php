<?php
/**
 * Verificacion
 */
if ( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_session_id'] !== session_id() ){ 
    $notification = new notificationClass('danger','No hay sesión');
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    header('Location: index.php');
}