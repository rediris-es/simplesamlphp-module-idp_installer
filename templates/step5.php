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
 * Plantilla para el paso 5 del modulo instalador para SimpleSAMLphp v1.13.1
 * @package    IdPRef\modules\idpinstaller
 * @author     "PRiSE [Auditoria y Consultoria de privacidad y Seguridad, S.L.]"
 * @copyright  Copyright (C) 2014 - 2015 by the Spanish Research and Academic
 *             Network
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @version    0.3-Sprint3-R57
 */
?>

<?php

    function generateRandom($length)
    {
        $number = "";

        for($i=0; $i<$length; $i++){
            $number .= mt_rand(0,9);
        }

        return $number;
        
    }

    function generateUsers()
    {
        $numUsers = 2;
        $users = array();
        while(count($users)<$numUsers){
            $number = generateRandom(5);

            if(!in_array($number, $users)){
                $users []= "u".$number;
            }
        }

        return $users;

    }   

    function generatePass()
    {
        $pass = array();
        $pass []= generateSecurePass();
        $pass []= generateSecurePass();

        return $pass;
    }

    $users = generateUsers();
    $pass = generatePass();
    $rolUsers = array("staff", "faculty");

?>

<script type="text/javascript">
    function showDatasource() {
        var elem = document.getElementById('data_source_type');
        if (elem.value == "ldap") {
            SimpleSAML_show('ldap_form');
            SimpleSAML_hide('pdo_form');
            SimpleSAML_hide('config_form');
        } else if(elem.value == "pdo") {
            SimpleSAML_show('pdo_form');
            SimpleSAML_hide('ldap_form');
            SimpleSAML_hide('config_form');
        } else if(elem.value == "config"){
            SimpleSAML_hide('ldap_form');
            SimpleSAML_hide('pdo_form');
            SimpleSAML_show('config_form');
        }
    }
</script>

<div id="js-error" style="display: none; border-left: 1px solid #e8e8e8; border-bottom: 1px solid #e8e8e8; background: #f5f5f5;">  
  <img style="margin-right: 10px;margin-left: 5px;" class="float-l erroricon" src="/resources/icons/experience/gtk-dialog-error.48x48.png">  
  <div style="padding-left: 64px;">
      <p id="ldap_hostname" class="errors-msg" style="margin: 0; padding-top: 5px "><?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_hostname_error}'); ?></p>
      <p id="ldap_port" class="errors-msg" style="margin: 0; padding-top: 5px "><?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_port_error}'); ?></p>
      <p id="ldap_binddn" class="errors-msg" style="margin: 0; padding-top: 5px "><?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_binddn_error}'); ?></p>
      <p id="ldap_bindpassword" class="errors-msg" style="margin: 0; padding-top: 5px "><?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_bindpassword_error}'); ?></p>
      <p id="pdo_dsn" class="errors-msg" style="margin: 0; padding-top: 5px "><?php echo $this->t('{idpinstaller:idpinstaller:step5_pdo_dsn_error}'); ?></p>
      <p id="pdo_username" class="errors-msg" style="margin: 0; padding-top: 5px "><?php echo $this->t('{idpinstaller:idpinstaller:step5_pdo_username_error}'); ?></p>
      <p id="pdo_password" class="errors-msg" style="margin: 0; padding-top: 5px "><?php echo $this->t('{idpinstaller:idpinstaller:step5_pdo_password_error}'); ?></p>
  </div>
  <div style="clear:both"></div>
</div>

<form action="" method="post" style="margin-top:20px;" onsubmit="return validateForm()">    
    <?php
//PASO 5 del instalador.
    $step      = 5;
    $next_step = 6;
    $options   = array(
        'ldap'   => "LDAP",
        'pdo'    => "PDO",
        'config' => 'CONFIG'
    );
    
    if (strcmp($this->data['sir']['datasources'], "all") == 0) {
        $ldap = true;
        $pdo  = true;
        $conf = true;
    } else if (strcmp($this->data['sir']['datasources'], "ldap") == 0) {
        $ldap = true;
        $pdo = false;
        $conf = true;
        unset($options['pdo']);
    } else if (strcmp($this->data['sir']['datasources'], "pdo") == 0) {
        $pdo  = true;
        $ldap = false;
        $conf = true;
        unset($options['ldap']);
    } else if(strcmp($this->data['sir']['datasources'], "config") == 0){
        $pdo  = false;
        $ldap = false;
        $conf = true;
        unset($options['ldap']);
        unset($options['pdo']);
    }
    if (count($this->data['sir']['errors']) > 0) {
        $button_msg = $this->t('{idpinstaller:idpinstaller:try_again_button}');
        $next_step  = 5;
    } else {
        $button_msg = $this->t('{idpinstaller:idpinstaller:next_step}');
    }
    ?>
    <p><?php echo $this->t('{idpinstaller:idpinstaller:step5_datasource_select}'); ?>
        <select id="data_source_type" name="data_source_type" onchange="showDatasource();">
            <?php
            $selected = isset($this->data['sir']['datasource_selected'])?$this->data['sir']['datasource_selected']:'ldap';
            foreach ($options as $k => $v) {
                echo "<option value='$k'";
                if($selected==$k){
                    echo' selected=true';
                }
                echo ">$v</option>";
            }
            ?>
        </select>
    </p>
    <div onload = "showDatasource();">
        <div id = "ldap_form" <?php if ((!$ldap && ($pdo || $conf))|| $selected=="pdo" || $selected=="config") { ?>style="display:none" <?php } ?>>
            <h3><?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_title}'); ?></h3>
            <p>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_hostname}'); ?><br/>
                <input type="text" name="ldap_hostname" value="" style="width: 300px;"/><br/>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_example}'); ?><br/>
            </p>
            <p>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_port}'); ?><br/>
                <input type="text" name="ldap_port" value="" style="width: 300px;"/><br/>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_port_example}'); ?><br/>
            </p>
            <p>   
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_anonymous_bind}'); ?><br/>
                <select name="ldap_anonymous_bind">
                    <option value="1"><?php echo $this->t('{idpinstaller:idpinstaller:step5_anonbind_yes}'); ?></option>
                    <option value="0"><?php echo $this->t('{idpinstaller:idpinstaller:step5_anonbind_no}'); ?></option>
                </select><br/>
            </p>            
            <p>En el caso de realizar bind anónimo especifique el DN y password:</p>
            <p>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_binddn}'); ?><br/>
                <input type="text" name="ldap_binddn" value="" style="width: 300px;"/><br/>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_binddn_example}'); ?><br/>
            </p>    
            <p>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_bindpassword}'); ?><br/>
                <input type="text" name="ldap_bindpassword" value="" style="width: 300px;"/><br/>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_bindpassword_example}'); ?><br/>
            </p>              
            <p>   
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_enable}'); ?><br/>
                <select name="ldap_enable_tls">
                    <option value="0"><?php echo $this->t('{idpinstaller:idpinstaller:step5_yes}'); ?></option>
                    <option value="1"><?php echo $this->t('{idpinstaller:idpinstaller:step5_no}'); ?></option>
                </select><br/>
            </p>
            <p>    
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_referral}'); ?><br/>
                <select name="ldap_referral">
                    <option value="0"><?php echo $this->t('{idpinstaller:idpinstaller:step5_yes}'); ?></option>
                    <option value="1"><?php echo $this->t('{idpinstaller:idpinstaller:step5_no}'); ?></option>
                </select><br/>
            </p>   
            <div class="caution" style="min-height:45px;">
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_info}'); ?>
            </div>
        </div>
        <div id="pdo_form" <?php if ((!$pdo && ($ldap || $conf)) || ($ldap && $pdo && $conf && ($selected=="ldap" || $selected=="conf"))) { ?>style="display:none" <?php } ?>>
            <h3><?php echo $this->t('{idpinstaller:idpinstaller:step5_pdo_title}'); ?></h3>
            <p>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_pdo_dsn}'); ?><br/>
                <input type="text" name="pdo_dsn" value="" style="width: 600px;"/><br/>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_pdo_example}'); ?><br/>
            </p>
            <p>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_pdo_username}'); ?><br/>
                <input type="text" name="pdo_username" value="" style="width: 300px;"/><br/>
            </p>
            <p>
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_pdo_password}'); ?><br/>
                <input type="password" name="pdo_password" value="" style="width: 300px;"/><br/>
            </p>
            <div class="caution" style="min-height:45px;">
                <?php echo $this->t('{idpinstaller:idpinstaller:step5_ldap_info}'); ?>
            </div>
        </div>

        <div id="config_form" <?php if ((!$conf && ($ldap || $pdo)) || ($ldap && $pdo && $conf && ($selected=="ldap" || $selected=="pdo"))) { ?>style="display:none" <?php } ?>>
            <h3><?php echo $this->t('{idpinstaller:idpinstaller:step5_config_title}'); ?></h3>

            <?php foreach ($users as $key => $user): ?>
                <h4>Usuario <?php echo ($key+1); ?></h4>
                <p>
                    <?php echo $this->t('{idpinstaller:idpinstaller:step5_config_user}'); ?><br/>
                    <input type="text" readonly="readonly" name="config_user[]" value="<?php echo $user; ?>" style="width: 300px;"/><br/>
                </p>
                <p>
                    <?php echo $this->t('{idpinstaller:idpinstaller:step5_config_pass}'); ?><br/>
                    <input type="text" readonly="readonly" name="config_pass[]" value="<?php echo $pass[$key]; ?>" style="width: 300px;"/><br/>
                </p>
                <p>
                    <?php echo $this->t('{idpinstaller:idpinstaller:step5_config_rol}'); ?><br/>
                    <input type="text" readonly="readonly" name="config_rol[]" value="<?php echo $rolUsers[$key]; ?>" style="width: 300px;"/><br/>
                </p>

            <?php endforeach; ?>

        </div>        

        <input type="hidden" name="step" value="<?php echo $next_step; ?>"/>
        <input type="submit" value="<?php echo $button_msg; ?>"/>
    </div>
</form>
<script>

function validateForm(){

    var selectDataSourceType = document.getElementsByName('data_source_type')[0];

    var inputLdapHostname = document.getElementsByName('ldap_hostname')[0];
    var inputLdapPort = document.getElementsByName('ldap_port')[0];
    var inputLdapBinddn = document.getElementsByName('ldap_binddn')[0];
    var inputLdapBindpassword = document.getElementsByName('ldap_bindpassword')[0];
    var inputLdapAnonymousBind = document.getElementsByName('ldap_anonymous_bind')[0];
    var inputPdoDsn = document.getElementsByName('pdo_dsn')[0];
    var inputPdoUsername = document.getElementsByName('pdo_username')[0];
    var inputPdoPassword = document.getElementsByName('pdo_password')[0];
    
    var error = document.getElementById('js-error');
    var errors = [];

    var allErrorsElements = document.getElementsByClassName("errors-msg");

    for (var i = 0; i < allErrorsElements.length; i++) {
        allErrorsElements[i].style.display = "none";
    } 

    if(selectDataSourceType.value=="ldap"){

        if(inputLdapHostname.value.trim().length == 0){
          errors.push("ldap_hostname");
        }

        if(inputLdapPort.value.trim().length == 0){
          errors.push("ldap_port");
        }

        if(inputLdapAnonymousBind.value=="1"){

            if(inputLdapBinddn.value.trim().length == 0){
              errors.push("ldap_binddn");
            }

            if(inputLdapBindpassword.value.trim().length == 0){
              errors.push("ldap_bindpassword");
            }

        }

    }else if(selectDataSourceType.value=="pdo"){
         
        if(inputPdoDsn.value.trim().length == 0){
          errors.push("pdo_dsn");
        }

        if(inputPdoUsername.value.trim().length == 0){
          errors.push("pdo_username");
        }

        if(inputPdoPassword.value.trim().length == 0){
          errors.push("pdo_password");
        }

    }

    if (errors.length > 0) {

      error.style.display = "block";

      for(var i=0;i<errors.length;i++){
        document.getElementById(errors[i]).style.display = "block";
      }

      window.scrollTo(0,0);
      return false;

    } else {

      error.style.display = "none";
      this.submit();
      return true;

    }

}

</script>