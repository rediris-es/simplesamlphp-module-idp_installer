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
 * Paso 9 del modulo instalador para SimpleSAMLphp v1.13.1
 * @package    IdPRef\modules\sir_install
 * @author     "PRiSE [Auditoria y Consultoria de privacidad y Seguridad, S.L.]"
 * @copyright  Copyright (C) 2014 - 2015 by the Spanish Research and Academic
 *             Network
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @version    IdPRef-Sprint2
 */

/**
 * Hook a ejecutar antes del paso 9 de la instalación
 *
 * @param array &$data  Los datos a utilizar por las plantillas de tipo stepn
 */
function sir_install_hook_step9(&$data) {

    $conf_path = realpath(__DIR__ . '/../../../config/config.php'); //440
    $idph_path = realpath(__DIR__ . '/../../../metadata/saml20-idp-hosted.php'); //444
    $spr_path  = realpath(__DIR__ . '/../../../metadata/saml20-sp-remote.php');
    $idpr_path = realpath(__DIR__ . '/../../../metadata/saml20-idp-remote.php');
    $mods_path = realpath(__DIR__ . '/../../../modules'); //555
    $cert_path = realpath(__DIR__ . '/../../../cert'); //555 a la pkey ->440
    $cert = $cert_path."/".$_SERVER['HTTP_HOST'].'.crt';
    
    $data['info'][] = $data['ssphpobj']->t('{sir_install:sir_install:step9_finished}');    
    if (function_exists('posix_getgrnam')) {
        $aux = $data['ssphpobj']->t('{sir_install:sir_install:step9_change_perms}');
        
        $recursive1 = is_dir($conf_path) ? "-R" : "";
        $aux.= "<pre>&gt; chmod $recursive1 440 " . $conf_path . "</pre>";
        $recursive2 = is_dir($idph_path) ? "-R" : "";
        $aux.= "<pre>&gt; chmod $recursive2 444 " . $idph_path . "</pre>";
        $recursive3 = is_dir($spr_path) ? "-R" : "";
        $aux.= "<pre>&gt; chmod $recursive3 444 " . $spr_path . "</pre>";
        $recursive4 = is_dir($idpr_path) ? "-R" : "";
        $aux.= "<pre>&gt; chmod $recursive4 444 " . $idpr_path . "</pre>";
        $recursive5 = is_dir($mods_path) ? "-R" : "";
        $aux.= "<pre>&gt; chmod $recursive5 555 " . $mods_path . "</pre>";
        $recursive6 = is_dir($cert_path) ? "-R" : "";
        $aux.= "<pre>&gt; chmod $recursive6 440 " . $cert_path . "</pre>";
        $data['info'][] = $aux;
    }
    $data['info'][] = $data['ssphpobj']->t('{sir_install:sir_install:step9_remember}');
    $data['info'][] = $data['ssphpobj']->t('{sir_install:sir_install:step9_remember_cert}').'<i>'.$cert_path.'</i>';
    $data['info'][] = $data['ssphpobj']->t('{sir_install:sir_install:step9_pub_key}').'</br><p style="font: bold 115% monospace;">'.str_replace("\n","</br>",file_get_contents($cert)).'</p>';
    return true;
}
