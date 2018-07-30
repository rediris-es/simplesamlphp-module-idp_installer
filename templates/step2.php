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
 * Plantilla para el paso 2 del modulo instalador para SimpleSAMLphp v1.13.1
 * @package    IdPRef\modules\idpinstaller
 * @author     "PRiSE [Auditoria y Consultoria de privacidad y Seguridad, S.L.]"
 * @copyright  Copyright (C) 2014 - 2015 by the Spanish Research and Academic
 *             Network
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @version    0.3-Sprint3-R57
 */
$step      = 2;
$next_step = 3;
if (count($this->data['sir']['errors']) > 0) {
    $button_msg = $this->t('{idpinstaller:idpinstaller:try_again_button}');
} else {
    $button_msg = $this->t('{idpinstaller:idpinstaller:next_step}');
}
?>

<style>
/*Creado por Adrian Gomez en Julio de 2018, para gestionar el texto indicativo de la fuerza del password*/
span#passScore{
	fount-weight: bold;
}

span.pass-txt{
	padding: 10px;
}
/*Fin codigo creado por Adrian Gomez en Julio de 2018*/
</style>

<div id="domain-error" style="display: none; border-left: 1px solid #e8e8e8; border-bottom: 1px solid #e8e8e8; background: #f5f5f5;">  
  <img style="margin-right: 10px;margin-left: 5px;" class="float-l erroricon" src="/resources/icons/experience/gtk-dialog-error.48x48.png">  
  <p style="padding-top: 5px "><?php echo $this->t('{idpinstaller:idpinstaller:step2_domain_error}'); ?></p>
  <div style="clear:both"></div>
</div>

<form action="" method="post" onsubmit="return validateForm()">    
    <h4><?php echo $this->t('{idpinstaller:idpinstaller:step2_access_title}'); ?></h4>
    <input type="hidden" name="step" value="<?php echo $next_step; ?>">
    <?php echo $this->t('{idpinstaller:idpinstaller:step2_access_password}'); ?><br/>
    <input autocomplete="off" type="password" onkeyup="checkPass(this.value)"  value="" id="ssphp_password" name="ssphp_password" style="width:200px;"><span class="pass-txt"><?php echo $this->t('{idpinstaller:idpinstaller:step2_pass_text}');?>: <span id="passScore"></span></span><br/>
    <?php echo $this->t('{idpinstaller:idpinstaller:step2_access_password2}'); ?><br/>
    <input autocomplete="off" type="password" value="" id="ssphp_password2" name="ssphp_password2" style="width:200px;"><br/>
    <br/>
    <button type="button" onclick="createSecurePassword()"> <?php echo $this->t('{idpinstaller:idpinstaller:step2_access_generate}'); ?>  </button>
    <br/>
     <h4><?php echo $this->t('{idpinstaller:idpinstaller:step2_contact_title}'); ?></h4>
    <?php echo $this->t('{idpinstaller:idpinstaller:step2_contact_name}'); ?>:<br/>
    <input type="text" value="" name="ssphp_technicalcontact_name" style="width:300px;"><br/>
    <?php echo $this->t('{idpinstaller:idpinstaller:step2_contact_email}'); ?>:<br/>
    <input type="text" value="" name="ssphp_technicalcontact_email" style="width:300px;"><br/>
    <p><?php echo $this->t('{idpinstaller:idpinstaller:step2_contact_info}'); ?></p>
    
    <h4><?php echo $this->t('{idpinstaller:idpinstaller:step2_organization_title}'); ?></h4>
    <?php echo $this->t('{idpinstaller:idpinstaller:step2_organization_name}'); ?>:<br/>
    <input type="text" value="" name="ssphp_organization_name" style="width:300px;"><br/>
    <?php echo $this->t('{idpinstaller:idpinstaller:step2_organization_description}'); ?>:<br/>
    <input type="text" value="" name="ssphp_organization_description" style="width:300px;"><br/>
    <?php echo $this->t('{idpinstaller:idpinstaller:step2_organization_domain}'); ?>:<br/>
    <input type="text" value="" name="ssphp_organization_domain" style="width:300px;"><br/>
    <?php echo $this->t('{idpinstaller:idpinstaller:step2_organization_info_url}'); ?>:<br/>
    <input type="text" value="" name="ssphp_organization_info_url" style="width:300px;"><br/>

    <br/><input type="submit" value="<?php echo $button_msg; ?>"></input>
</form>

<script>

/*
 CREADO POR Adrian Gomez en Julio del 2018

Gestion del password: 
- Creacion de un boton para crear un password automaticamente
- Validacion del password para mostrar la fuerza de la misma en terminos de seguridad
*/

/*Metodo que genera un password aleatorio. El password contendra al menos una mayuscula, una minuscula, un numero y un caracter especial, y su longitud sera de 9 caracteres al menos... de esta forma estaremos generando siempre un pasword que al menos tenga una calificacion de "fuerte"*/
function createSecurePassword() 
{
   //declaramos las strings que contendrán todos los posibles carácteres que se escojerán de manera aleatoria
   var specials = '!@#$%^&*-+?';
   var lowercase = 'abcdefghijklmnopqrstuvwxyz';
   var uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   var numbers = '0123456789';

   //se incluirá en la variable final lowercase 2 veces para que sea más probable que la contraseña sea una palabra
   //con minúsculas pero que al mismo tiempo contenga un poco de todo
   var all = specials + lowercase + lowercase + uppercase + numbers;
   
   //esta función te escojerá aleatoriamente un carácter de todo lo ubicado en all
   String.prototype.pick = function(min, max) {
    var n, chars = '';

    if (typeof max === 'undefined') 
    {
        n = min;
    } 
    else 
    {
        n = min + Math.floor(Math.random() * (max - min));
    }

    for (var i = 0; i < n; i++) 
    {
        chars += this.charAt(Math.floor(Math.random() * this.length));
    }

      return chars;
   };

   //esta función reordenará de manera aleatoria los carácteres del string
   String.prototype.shuffle = function() 
   {
      var array = this.split('');
      var tmp, current, top = array.length;

      if (top) while (--top) 
      {
        current = Math.floor(Math.random() * (top + 1));
        tmp = array[current];
        array[current] = array[top];
        array[top] = tmp;
      }

      return array.join('');
   };

   //se crea la contraseña que contenga al menos un caracter especial, minusculas, mayusculas y numeros
   var password = (numbers.pick(1) + specials.pick(1) + lowercase.pick(1) + uppercase.pick(1) + all.pick(5, 10)).shuffle();

   
   //una vez que ya hemos generado la contraseña la mostraremos en el formulario
   //de tal manera que sea visible para el usuario
   document.getElementById("ssphp_password").value = password;
   document.getElementById("ssphp_password2").value = password;
   document.getElementById("ssphp_password").type = "text";
   document.getElementById("ssphp_password2").type = "text"; 
   checkPass(password);
}

//Metodo que calcula la fuerza de la contraseña dependiendo de los caracteres que contenga
function puntuacionPass(pass) {
    var score = 0;
    if (!pass)
        return score;

    //Puntua cada caracter en sus primeras 5 repeticiones
    var letters = new Object();
    for (var i=0; i<pass.length; i++) {
        letters[pass[i]] = (letters[pass[i]] || 0) + 1;
        score += 5.0 / letters[pass[i]];
    }

    // Checkeamos si la contraseña tiene digitos, minusculas, mayusculas y caracteres especiales
    var variations = {
        digits: /\d/.test(pass),
        lower: /[a-z]/.test(pass),
        upper: /[A-Z]/.test(pass),
        nonWords: /\W/.test(pass),
    }

    //Segun cumpla con los parametros anteriores vamos sumando puntuacion
    variationCount = 0;
    for (var check in variations) {
        variationCount += (variations[check] == true) ? 1 : 0;
    }
    score += (variationCount - 1) * 10;

    return parseInt(score);
}


//Metodo que gestiona el texto de la fuerza del password
function checkPass(pass) {
    var score = puntuacionPass(pass); 
    var passText = "";
    if (score >= 80)
        passText = "Muy fuerte";
    else if (score < 80 && score >= 60)
        passText = "Fuerte";
    else if (score < 60 && score >= 30)
        passText = "Debil";
    else
	passText = "Muy debil";
    document.getElementById("passScore").innerHTML = passText;
}


/*
Fin de codigo nuevo creado por Adrian Gomez
*/


function validateForm(){

    var inputDomain = document.getElementsByName('ssphp_organization_domain')[0];
    var errorDomain = document.getElementById('domain-error');
    if(/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/.test(inputDomain.value)){
        errorDomain.style.display = "none";
        this.submit();
        return true;
    }
    errorDomain.style.display = "block";
    window.scrollTo(0,0);
    return false;

 }

</script>

