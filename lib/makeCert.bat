@echo off 
setlocal enabledelayedexpansion
:: Shell Script (Bash) para la generación de certificados x509 a partir de los
:: parámetros de entrada:
::   $0 => Nombre del comando
::   $1 => Fichero de salida para cert 
::   $2 => Fichero de salida para privKey
::   $3 => Organization
::   $4 => CommonName


call :split "%4%" "." FULL_DC

::Nombre de la organización 
set "O=/O=%3"

::Organizational Unit
set "OU=/OU=Certificado SPT"

::Common Name
set "CN=/CN=%4"

::Tiempo de vida del certificado en días por defecto 10 años.
set TTL=3652

::Tamaño de la clave por defecto 1024 bytes
set KEY_SIZE=1024

::Composición del SubjectDN del certificado.
set "SUBJECT=%FULL_DC%%O%%OU%%CN%"

openssl req -newkey rsa:%KEY_SIZE% -new -x509 -days %TTL% -nodes -out %1 -keyout %2 -multivalue-rdn -subj "%SUBJECT%"

exit


:: Función split personalizada
:split <string_to_split> <split_delimiter> <array_to_populate>
	set "_data=%~1"
	set _data="!_data:%~2=" "!"
	set "_data=%_data:""=%"
	set "%~3="

	for %%I in (%_data%) do (
		set "%~3=!FULL_DC!/DC=%%~I
	)
goto :EOF


