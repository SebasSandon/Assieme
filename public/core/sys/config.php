<?php
/*
 * Archivo de configuracion
 */

/*
 * Base de datos
 */
define('DBHOST','localhost');
define('DBNAME','crececon_ceres_v1');
define('DBUSER','crececon_ceresus');
define('DBPASS','mj5aHXFmuQSz');

/*
 * Sistema
 */
define('SYSNAME','ceres');
define('SYSVER','v1.0');
define('SYSMODULE', 'manager');
define('SYSTITLE','ceres.manager.v1');
define('SYSICON','');
define('SYSSESSION', 'ceresv1');

/**
 * Clave SHA256
 */
define('SYSSECRET', 'bc483cdfb27714828a99ba159e2e8fbe6a6d140623b2b3ac54aba8cb33566e65');

/*
 * Medios
 */
define('MEDIADOMAIN','ceresflujo.crececonsultores.cl');
define('MEDIASUBDOMAIN','uploads');

/*
 * Paginacion
 */
define('PAGINATION',20);

/**
 * Mailer
 */
define('MAILERFROM','');
define('MAILERREPLY','');
define('MAILERSUBJECT','[]');

/**
 *  Variable configuración
 */
$sysConfiguration = array(
    'sysname' => SYSNAME,
    'sysver' => SYSVER,
    'sysmodule' => SYSMODULE,
    'systitle' => SYSTITLE,
    'sysicon' => SYSICON,
    'syssession' => SYSSESSION,
    'mediadomain' => MEDIADOMAIN,
    'mediasubdomain' => MEDIASUBDOMAIN
);