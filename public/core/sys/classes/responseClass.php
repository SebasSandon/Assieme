<?php
/**
 * responseClass
 *
 * @author SadSacrifice
 */
class responseClass
{
    public $result;
    
    public $sysCode;
    public $sysMessage;
    
    public $exceptionMessage;
    public $exceptionCode;
	
    public $dbQuery;
    
    function __construct() {
       $this->result = false;
    }
    
    function setResponse($responseResult, $responseCode){
        $this->result = $responseResult;
        $this->sysCode = $responseCode;
        $this->sysMessage = $this->setMessage($this->sysCode);
    }
    
    function setException($responseException){
        $this->exceptionMessage = $responseException->getMessage();
        $this->exceptionCode = $responseException->getCode();
    }
    
    private function setMessage($sysCode){
	switch($sysCode){
            /* SESSION */
            case 'SESSION_ERROR':
		return 'No hay sessión';
            /* INPUT */
            case 'INPUT_ERROR':
		return 'Parámetros inválidos';
            /* INPUT */
            case 'REQUEST_ERROR':
		return 'Petición inválida';
            /* DATABASE */
            case 'DATABASE_CONNECTION_ERROR':
		return 'Error en conexión a base de datos';
            case 'DATABASE_QUERY_ERROR':
		return 'Error en consulta a base de datos';
            case 'DATABASE_NOT_FOUND_ERROR':
		return 'No se ha encontrado el registro';
            /* LOGIN */
            case 'LOGIN_SUCCESS':
                    return 'Ingreso correcto';
            case 'LOGIN_PASSWORD_ERROR':
                return 'Contraseña incorrecta';
            case 'LOGIN_USERNAME_ERROR':
                return 'Usuario no encontrado';
            case 'LOGIN_DENIED_ERROR':
		return 'No hay permisos suficientes';
            /* CSRF */
            case 'TOKEN_ERROR':
		return 'Petición incorrecta';
            /* SUCCESS */
            case 'SUCCESS_CREATE':
                return 'Registro agregado';
            case 'SUCCESS_UPDATE':
                return 'Registro actualizado';
            case 'SUCCESS_DELETE':
                return 'Registro eliminado';
            case 'SUCCESS_UPLOAD':
                return 'Carga exitosa';
            /* UPLOAD */
            case 'UPLOAD_ERROR':
                return 'Error al cargar archivo';
            case 'UPLOAD_FOLDER_ERROR':
                return 'Error al crear directorio';
            case 'UPLOAD_TYPE_ERROR':
                return 'Archivo no soportado';
            /* OPERATION */
            case 'OPERATION_ERROR':
                return 'Operación no completada';    
            default:
                return null;
        }
    }
}
