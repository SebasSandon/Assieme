<?php
/*
 * Función para eliminar un directorio y su contenido
 * From: http://www.cristianperez.com/borrar-un-directorio-no-vacio-con-php/
 */
function removeDirectory($path)
{
    $path = rtrim( strval( $path ), '/' ) ;
    
    $d = dir( $path );
    
    if( ! $d )
        return false;
    
    while ( false !== ($current = $d->read()) )
    {
        if( $current === '.' || $current === '..')
            continue;
        
        $file = $d->path . '/' . $current;
        
        if( is_dir($file) )
            removeDirectory($file);
        
        if( is_file($file) )
            unlink($file);
    }
    
    rmdir( $d->path );
    $d->close();
    return true;
}
