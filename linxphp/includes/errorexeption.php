<?php
/*
 * Linx PHP Framework
 * Author: Javier Arias. *
 * Licensed under MIT License.
 */
 
class WarningException extends ErrorException {}
class FatalErrorException extends ErrorException {}

function exceptionsHandler($code, $string, $file, $line) {
	switch ($code){
		case E_DEPRECATED: // ignores new DEPRECATED error to allow developers to use third party libraries
   			return;
		case E_WARNING:
			throw new WarningException($string, $code, $code,$file,$line);
		default:
			throw new ErrorException($string, $code, $code,$file,$line);
	}
}
set_error_handler('exceptionsHandler', E_ALL);
?>