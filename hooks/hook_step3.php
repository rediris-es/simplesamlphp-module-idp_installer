<?php
/*
 *  IdPRef - IdP de Referencia para SIR 2 basado en SimpleSAMLPHP v1.13.1
 * =========================================================================== *
 *
 * Copyright (C) 2014 - 2015 by the Spanish Research and Academic Network.
 * This code was developed by Auditoria y Consultoría de Privacidad y Seguridad
 * (PRiSE http://www.prise.es) for the RedIRIS SIR service (SIR: 
 * http://www.rediris.es/sir)
 *
 * *****************************************************************************
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * ************************************************************************** */

/** 
 * Paso 3 del modulo instalador para SimpleSAMLphp v1.13.1
 * @package    IdPRef\modules\idpinstaller
 * @author     "PRiSE [Auditoria y Consultoria de privacidad y Seguridad, S.L.]"
 * @copyright  Copyright (C) 2014 - 2015 by the Spanish Research and Academic
 *             Network
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @version    0.3-Sprint3-R57
 */

/**
 * Hook a ejecutar antes del paso 3 de la instalación
 * 1) Define la contraseña de administrador (se debe preguntar al usuario)
 * 2) Define el grano de sal (automáticamente)
 * 3) Define información sobre el contacto técnico(se debe preguntar al usuario)
 * 4) Define el idioma por defecto de Castellano(automáticamente)
 * 5) Define la zona horaria por defectosegún el PHP.ini(automáticamente, pero se informa al usuario)
 *
 * @param array &$data  Los datos a utilizar por las plantillas de tipo stepn
 */
function idpinstaller_hook_step3(&$data) {
    if (array_key_exists('ssphp_password', $_REQUEST) && array_key_exists('ssphp_password2', $_REQUEST) && !empty($_REQUEST['ssphp_password'])) {
        $pass  = $_REQUEST['ssphp_password'];
        $pass2 = $_REQUEST['ssphp_password2'];
        if(isset($_REQUEST['ssphp_organization_name'])){ 
            $org_name = $_REQUEST['ssphp_organization_name'];
        }
        if(isset($_REQUEST['ssphp_organization_description'])){
            $org_info = $_REQUEST['ssphp_organization_description'];
        }
        if(isset($_REQUEST['ssphp_organization_info_url'])){
            $org_url_info = $_REQUEST['ssphp_organization_info_url'];
        } 
        $file_tmp_name = realpath(__DIR__ . '/../../../cert/').'/tmp_org_info.php';
        if(file_exists($file_tmp_name)){
            unlink($file_tmp_name);
        }
        if($file = fopen($file_tmp_name, "x")){
            fwrite($file, '<?php $org_info = array('
                            . "'name' => '$org_name',"
                            . "'info' => '$org_info',"
                            . "'url'  => '$org_url_info'); ");
            fclose($file);
        }
        $data['ssphpobj']->data['buffer_info_org']= array($org_name,$org_info,$org_url_info);
        if (strcmp($pass, $pass2) == 0) {
            if (array_key_exists('ssphp_technicalcontact_name', $_REQUEST) && array_key_exists('ssphp_technicalcontact_email', $_REQUEST) && !empty($_REQUEST['ssphp_technicalcontact_name']) && !empty($_REQUEST['ssphp_technicalcontact_email'])) {
                $filename                         = __DIR__ . '/../../../config/config.php';
                include($filename);
                
		//ALMACENAMIENTO ANTERIOR DE PASSWORD
		//$config['auth.adminpassword']     = $pass;
		//FIN ALMACENAMIENTO ANTERIOR DE PASSWORD
		
		/*
                NUEVA FORMA DE ALMACENAR PASSWORD
                Adrian Gomez en Julio de 2018 #2
                Pôsibles algoritmos de cifrado: 
                md2          md4          md5          sha1         sha224       sha256
                sha384       sha512       ripemd128    ripemd160    ripemd256    ripemd320
                whirlpool    tiger128,3   tiger160,3   tiger192,3   tiger128,4   tiger160,4
                tiger192,4   snefru       snefru256    gost         adler32      crc32
                crc32b       salsa10      salsa20      haval128,3   haval160,3   haval192,3
                haval224,3   haval256,3   haval128,4   haval160,4   haval192,4   haval224,4
                haval256,4   haval128,5   haval160,5   haval192,5   haval224,5   haval256,5
                */
                $algoritmo = 'sha256';
                $salt = 'S';//Si no queremos usar salt, simplemente poner ''
                $config['auth.adminpassword'] = SimpleSAML\Utils\Crypto::pwHash($pass, strtoupper($salt.$algoritmo));
		/* Fin Adrian Gomez Julio 2018 #2*/

                //$config['secretsalt']             = bin2hex(openssl_random_pseudo_bytes(16));
                

		$config['secretsalt']             = shell_exec("tr -cd '[:alnum:][:blank:]' < /dev/urandom | head -c48; echo");
                $config['technicalcontact_name']  = $_REQUEST['ssphp_technicalcontact_name'];
                $config['technicalcontact_email'] = $_REQUEST['ssphp_technicalcontact_email'];
                $config['language.default']       = "es";
                $config['timezone']               = date_default_timezone_get();
                $config['timezone']               = date_default_timezone_get();
                $config['enable.saml20-idp']      = true;
                $config['enable.shib13-idp']      = false;
                $config['enable.adfs-idp']        = false;
                $config['enable.wsfed-sp']        = false;
		/* Aniadido por Adrian Gomez en Julio de 2018
		 Modificacion de directivas de configuración para dar mayor seguridad. #3
		*/
		$config['admin.protectindexpage'] = true;
		$config['showerrors']		  = false;
		$config['session.cookie.secure']  = true;
		$config['trusted.url.domains']	  = array();
		/*Fin de aniadido por Adrian Gomez*/

                $res                              = @file_put_contents($filename, '<?php  $config = ' . var_export($config, 1) . "; ?>");
                if (!$res) {
                    $data['errors'][] = $data['ssphpobj']->t('{idpinstaller:idpinstaller:step2_contact_save_error}');
                    $data['errors'][] = $data['ssphpobj']->t('{idpinstaller:idpinstaller:step2_contact_save_error2}') . " <i>" . realpath($filename) . "</i>";
                } else {
                    $data['warning'][] = $data['ssphpobj']->t('{idpinstaller:idpinstaller:step2_timezone_info}') . " <i>" . $config['timezone'] . "</i>. " . $data['ssphpobj']->t('{idpinstaller:idpinstaller:step2_timezone_info2}');
                }
            } else {
                $data['errors'][] = $data['ssphpobj']->t('{idpinstaller:idpinstaller:step2_contact_info_error}');
            }
        } else {
            $data['errors'][] = $data['ssphpobj']->t('{idpinstaller:idpinstaller:step2_passwords_error}');
        }
    } else {
        $data['errors'][] = $data['ssphpobj']->t('{idpinstaller:idpinstaller:step2_password_ko_error}');
    }
    return true;
}
