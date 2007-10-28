<?php 

/* 
    This plugin allows users to select a logo graphic which will be available 
    for use in the template.  Use the line:
    
    <img id=Logo src='/graphics/<?php  echo($ewiki_config['page_logo']);   ?>' alt='<?php  echo($ewiki_config['page_logo_alt']);   ?>'>    
    
    to insert the graphic.
*/
/* 
* @author jeremy mikola <jmikola@arsjerm.net>
* @author andy fundinger <afundinger@burgiss.com> (Minor contributions and maintenance)
*/


$ewiki_plugins['edit_save'][]        = 'ewiki_edit_save_piclogocntrl';
$ewiki_plugins['edit_form_append'][] = 'ewiki_edit_form_append_piclogocntrl';
$ewiki_plugins["handler"][]        = 'ewiki_set_pickedlogo';

// array lists all available logo files and titles
$piclogocntrlLogos = array(
    'logo1_01.gif' => 'Logo 1',
    'logo2_01.gif' => 'Logo 2',
    'logo3_01.gif' => 'Logo 3',
    'logo4_01.gif' => 'Logo 4');

define('DEFAULT_LOGO','BurgissGroup_01.gif');

function ewiki_set_pickedlogo($id, $data, $action){
    global $ewiki_config,$piclogocntrlLogos;

    if(isset($ewiki_config['page_logo'])){
        return;
    }

    if(!($ewiki_config['page_logo']=$data['meta']['logo'])){
        $ewiki_config['page_logo']=DEFAULT_LOGO;
    }
    
    $ewiki_config['page_logo_alt']=$piclogocntrlLogos[$ewiki_config['page_logo']];
}

/**
 * Save selected logo value by setting it in the meta field of save data array
 * passed by reference.
 * 
 * @param array save associative array of ewiki form data
 */
function ewiki_edit_save_piclogocntrl(&$save, &$old_data)
{
    global $piclogocntrlLogos;
    
    if (isset($_REQUEST['piclogocntrlSelectLogo']) && array_key_exists($_REQUEST['piclogocntrlSelectLogo'], $piclogocntrlLogos)) {
        $save['meta']['logo'] = $_REQUEST['piclogocntrlSelectLogo'];
    }
}

/**
 * generates html form output for the logo selection field.
 *
 * @param mixed id
 * @param mixed data
 * @param string action
 * @return string html output for logo selection fields
 */
function ewiki_edit_form_append_piclogocntrl ($id, $data, $action)
{
    global $piclogocntrlLogos;
    
    /*
     * problem: $data['meta'] is still serialized at this point. it should be
     * unserialized and accessible as an array in order to fetch the currently
     * selected logo value.
     */        
    
    $o = '
        <br /><label for="piclogocntrlSelectLogo">Please choose:</label>
        <select id="piclogocntrlSelectLogo" name="piclogocntrlSelectLogo">';
        
    foreach ($piclogocntrlLogos as $filename => $title) {
        $o .= '<option value="'.htmlentities($filename).'"'.
            (isset($data['meta']['logo']) && $filename == $data['meta']['logo'] ? ' selected="selected"' : '').
            '>'.htmlentities($title).'</option>';
    }

    $o .= '</select><br /><br />';
    return($o);
}



?>