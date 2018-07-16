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
 * 
 * @package    IdPRef\modules\idpinstaller\lib
 * @author     "PRiSE [Auditoria y Consultoria de privacidad y Seguridad, S.L.]"
 * @copyright  Copyright (C) 2014 - 2015 by the Spanish Research and Academic
 *             Network
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @version    0.3-Sprint3-R57
 */

function isExtensionActive($ext, $loaded_modules) {
    return array_search($ext, $loaded_modules) !== false;
}

function isLDAPextensionActive($loaded_modules) {
    return isExtensionActive("ldap", $loaded_modules);
}

function isPDOextensionActive($loaded_modules) {
    return isExtensionActive("PDO", $loaded_modules);
}

function isPDOModulesActive($loaded_modules) {
    $pdos           = array("pdo_oci", "pdo_mysql", "pdo_sqlite", "pdo_pgsql", "pdo_mssql");
    $active_modules = array();
    for ($index = 0; $index < count($pdos); $index++) {
        if (array_search($pdos[$index], $loaded_modules) !== false) {
            $active_modules[] = $pdos[$index];
        }
    }
    return count($active_modules) > 0;
}

function getPDOModulesActive($loaded_modules) {
    $pdos           = array("pdo_oci", "pdo_mysql", "pdo_sqlite", "pdo_pgsql", "pdo_mssql");
    $active_modules = array();
    for ($index = 0; $index < count($pdos); $index++) {
        if (array_search($pdos[$index], $loaded_modules) !== false) {
            $active_modules[] = $pdos[$index];
        }
    }
    return $active_modules;
}

function drawButton($next_step, $msg, $style = '', $inputs = '') {
    ?>
    <div <?php echo $style; ?>>
        <form action="" method="post">    
            <input type="hidden" name="step" value="<?php echo $next_step; ?>"/>
            <?php echo $inputs; ?>
            <input type="submit" value="<?php echo $msg; ?>"/>
        </form>
    </div>
    <?php
}

function getDataSources() {
    $loaded_extensions = get_loaded_extensions();
    $ldap              = isLDAPextensionActive($loaded_extensions);
    $pdo               = isPDOextensionActive($loaded_extensions);
    $modulos           = $pdo ? isPDOextensionActive($loaded_extensions) : false;
    if ($ldap && $pdo && $modulos) {
        $res = "all";
    } else if (!$ldap && $pdo && $modulos) {
        $res = "pdo";
    } else {
        $res = "ldap";
    }
    return $res;
}

function getFileUsername($filename) {
    $file_owner = posix_getpwuid(fileowner($filename));
    return $file_owner['name'];
}

function getApacheGroup() {
    $group = posix_getgrgid(posix_getgid());
    return $group['name'];
}
function transaleXMLToSsPHP($xmldata){
    
    if (!empty($xmldata)) {

        SimpleSAML_Utilities::validateXMLDocument($xmldata, 'saml-meta');
        $entities = SimpleSAML_Metadata_SAMLParser::parseDescriptorsString($xmldata);

        /* Get all metadata for the entities. */
        foreach ($entities as &$entity) {
            $entity = array(
                'saml20-sp-remote' => $entity->getMetadata20SP(),
                'saml20-idp-remote' => $entity->getMetadata20IdP(),
            );
        }

        /* Transpose from $entities[entityid][type] to $output[type][entityid]. */
        $output = SimpleSAML_Utilities::transposeArray($entities);

        /* Merge all metadata of each type to a single string which should be
         * added to the corresponding file.
         */
        foreach ($output as $type => &$entities) {

            $text = '';

            foreach ($entities as $entityId => $entityMetadata) {

                if ($entityMetadata === NULL) {
                    continue;
                }

                /* Remove the entityDescriptor element because it is unused, and only
                 * makes the output harder to read.
                 */
                unset($entityMetadata['entityDescriptor']);

                $text .= '$metadata[' . var_export($entityId, TRUE) . '] = ' .
                        var_export($entityMetadata, TRUE) . ";\n";
            }

            $entities = $text;
        }
    } else {
        $output  = array();
    }
    return $output;
}


function arrayToFileString($array, $nivel=0){
    $fileContent = " array(\n";
    $tabNivel = "";
    for ($i=0; $i < $nivel; $i++) { 
        $tabNivel .= "\t";
    }

    foreach ($array as $key => $value) {

        if(array_values($array) === $array && !is_array($value)){
            $fileContent .= $tabNivel."\t\t".varToString($value, '', $nivel);
        }else{
           
            $fileContent .= $tabNivel."\t\t".varToString($value, $key, $nivel);
        }
    }
    $fileContent .= $tabNivel."\t)";
    return $fileContent;
}


function varToString($var, $key, $nivel=0){

    $stringVar = "";
    $stringKey = "";
    if($key!==''){
        if(is_numeric($key)){
            $stringKey = "{$key} => ";
        }else{
            $stringKey = "'{$key}' => ";
        }
        
    }
    if (is_array($var) === true)
    {
        $stringVar = $stringKey.(arrayToFileString($var, $nivel+1)).", \n";
    } 
    //una vez que hemos obtenido los datos vamos a procesarlos de manera correcta
    //en el caso de que sea un string añadiremos comillas simples al rededor del fichero
    else if ( gettype($var) == 'string' )
    {
        $stringVar = $stringKey." '{$var}', \n";;
    }
    //en el caso de que tengamos un dato boleano, el propio php mostrará un 0 si el valor es falso
    //y cualquier otro número en el caso de que el valor sea verdadero
    else if ( gettype($var) == 'boolean' )
    {
        if ($var == 0)
        {
            $stringVar = $stringKey." FALSE, \n";;
        }
        else
        {
            $stringVar = $stringKey." TRUE, \n";;
        }
    }
    //finalmente si el tipo de dato es NULL no mostrará nada. Por lo que será necesario incluir tambien el valor null
    else if ( gettype($var) == 'NULL' )
    {
        $stringVar = $stringKey." NULL, \n";;
    }
    else
    {
        $stringVar = $stringKey." {$var}, \n";;
    }
    return $stringVar;
}



function overwriteAuthsources ($config, $filename)
{
        $file = __DIR__ . '/../../../config-templates/'.$filename;
        $fopen = fopen($file, 'r');
        $fread = fread($fopen,filesize($file));
        fclose($fopen);
        //a continuación dividiremos el fichero en líneas
        $remove = "\n";
        $split = explode($remove, $fread);
        //declaramos también otras variables útiles para el recorrido del contenido del fichero
        $fileContent = "";
        $isCommentLong = false;
        $isArrayLong = 0;
        //creamos la variable config aux para no altearar el array original
        $configAux = $config;
        $i = 0;
    
        //una vez dividido pasamos a recorrerlo
        foreach ($split as $string)
        {
            $matched = false;
        $i++;
            //Primero de todo. miramos si la linea es un comentario o no
            $isComment = false;
            //primero le quitamos los espacios en blanco
            $stringAux = str_replace(' ', '', $string);
            if (substr($stringAux,0,1) == '/')
            {
                //si empieza por /* entonces es un comentario de varias lineas
                if ( substr($stringAux,1,1) == '*')
                {
                    $isComment = true;
                    $isCommentLong = true;
                }
                //si empieza por //entonces es un comentario de una linea
                if (substr($stringAux,1,1) == '/')
                {
                    $isComment = true;
                }
            }
           //Por el contrario si contiene * / suponemos que se ha cerrado un comentario largo
           if (strpos($stringAux, '*/') !== false) 
           {
               $isComment = true;
               $isCommentLong = false;
            }
            //si no es un comentario, entonces procedemos a comparar
            if ($isComment == false && $isCommentLong == false)
            {
                //ahora vamos a recorrer cada uno de los elementos que contiene el array config
                foreach ($configAux as $clave => $valor)
                {
                    /** COMPROBACION EXACTA, NO SI EXISTE EN KA STRING. HAY QUE OBTENER  **/
                     //por cada elemento del config vamos a ver si coincide o si contiene la cadena que estamos buscando
                     if (strpos($string, $clave) !== false) 
                     {
                        //de ser así indicamos a ciertas variables y ponemos una marca por pantalla para que se vea que la hemos encontrado
                        $matched = true;
                       
                        //si encontramos que vamos a sobrescribir un array entonces lo vamos a tratar de manera diferente
                       
                        if (strpos($string,"array(") !== false)
                        {
                            //dividimos el string en dos lo que viene antes del array y lo que viene después
                            $splitedString = explode( 'array(', $string );
                            //lo que hay antes lo dejamos intacto por ejemplo en el caso
                            //'Nombre del atributo' => array('array','con muchas','cosas');
                            //quedaría así 'Nombre del atributo' => array(
                            $fileContent .= $splitedString[0];
                            //a continuación comprobamos si es un array multilinea o si acaba en la misma linea
                            if ( strpos ( $string, ")," ) !== false )
                            {
                                //el array acaba en la misma linea
                            } 
                            else
                            {
                                $isArrayLong = 1;
                            }
                            //ahora veremos el contenido del nuevo array que tenemos en el config
                            //en el caso de que no sea un array o que tenga longitud Cero entonces dejamos el nuevo array vacío
                            if ( is_array($valor) && sizeof($valor) > 0 )
                            {
                                if ( strcmp(array_keys($valor)[0],"Array") !== 0 )
                                {
                                    $fileContent .= arrayToFileString($valor);
                                    //$stringExport = var_export($valor,1);
                                    ////$fileContent .= "'". implode("','",$valor) . "'";
                                    //$fileContent .= "" . $stringExport . "";
                                }
                            }else{
                                $fileContent .= " array()";
                            }

                            $fileContent .= ",\n";
                        } 
                        //una vez que hemos obtenido los datos vamos a procesarlos de manera correcta
                        //en el caso de que sea un string añadiremos comillas simples al rededor del fichero
                        else if ( gettype($valor) == 'string' )
                        {
                            $fileContent .= "\t'{$clave}' => '{$valor}', \n";
                        }
                        //en el caso de que tengamos un dato boleano, el propio php mostrará un 0 si el valor es falso
                        //y cualquier otro número en el caso de que el valor sea verdadero
                        else if ( gettype($valor) == 'boolean' )
                        {
                            if ($valor == 0)
                            {
                                $fileContent .= "\t'{$clave}' => FALSE,\n";
                            }
                            else
                            {
                                $fileContent .= "\t'{$clave}' => TRUE,\n";
                            }
                        }
                        //finalmente si el tipo de dato es NULL no mostrará nada. Por lo que será necesario incluir tambien el valor null
                        else if ( gettype($valor) == 'NULL' )
                        {
                                $fileContent .= "\t'{$clave}' => NULL,\n";
                        }
                        else
                        {
                            $fileContent .= "\t'{$clave}' => {$valor},\n";
                        }
                        //además también eliminaremos este elemento del array para que no se vuelva a repetir
                        unset($configAux[$clave]);
                     }
                }
            }
            //aquí vamos a comprobar si se cierra el array o si hay algún array anidado
            //comprobamos que matched sea falso por que de lo contrario la primera vez lo sumará dos veces
            if ($isArrayLong > 0 && $matched == false)
            {
                if ($isComment == false && $isCommentLong == false)
                {
                    if (strpos($string,"array(") !== false)
                    {
                        $isArrayLong++;
                    }
                    if (strpos($string,"),") !== false)
                    {
                        $isArrayLong--;
                    }
                }
                else
                {
                    $fileContent .= $string . "\n";
                }
            }
            //si no se ha encontrado ninguna coincidencia entonces se copia el contenido del fichero tal cual
            else if ($matched == false)
            {
                if ($i == count($split) - 1 )
                {
                    //recorremos el array por si acaso se nos han quedado datos sin sobrescribir
                    foreach ($configAux as $clave => $valor)
                    {
                        //en este caso todos los elementos que encontramos son un array de arrays así que para una mayor eficiencia
                        //utilizaremos la función var_export
                        //$fileContent .= "\t\t'{$clave}' => " . var_export($valor,1) . ", \n
                        $fileContent .= "\t".varToString($valor, $clave, $nivel=0);
                    }
                }
                    $fileContent .= $string . "\n";
            }
               
        }
        //Creamos el fichero php correspondiente
        return $fileContent;
}
