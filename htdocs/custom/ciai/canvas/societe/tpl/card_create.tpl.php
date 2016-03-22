<?php
/* Copyright (C) 2016 Claudio Aschieri <c.aschieri@19.coop>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       /ciai/canvas/societe/tpl/card_create.tpl.php
 *  \ingroup    ciai
 *  \brief      Third party card page
 */

dol_include_once("./ciai/class/societePlus.class.php");

global $hookmanager;

$object = new SocietePlus($db);
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label($object->table_element);

$form = new Form($db);
$formfile = new FormFile($db);
$formadmin = new FormAdmin($db);
$formcompany = new FormCompany($db);


// -----------------------------------------
// When used in standard mode
// -----------------------------------------
if ($action == 'create')
{
	/*
	*  Creation
	*/
	$private=GETPOST("private","int");
	if (! empty($conf->global->MAIN_THIRDPARTY_CREATION_INDIVIDUAL) && ! isset($_GET['private']) && ! isset($_POST['private'])) $private=1;
	if (empty($private)) $private=0;

        // Load object modCodeTiers
        $module=(! empty($conf->global->SOCIETE_CODECLIENT_ADDON)?$conf->global->SOCIETE_CODECLIENT_ADDON:'mod_codeclient_leopard');
        if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
        {
            $module = substr($module, 0, dol_strlen($module)-4);
        }
        $dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
        foreach ($dirsociete as $dirroot)
        {
            $res=dol_include_once($dirroot.$module.'.php');
            if ($res) break;
        }
        $modCodeClient = new $module;
        // Load object modCodeFournisseur
        $module=(! empty($conf->global->SOCIETE_CODECLIENT_ADDON)?$conf->global->SOCIETE_CODECLIENT_ADDON:'mod_codeclient_leopard');
        if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
        {
            $module = substr($module, 0, dol_strlen($module)-4);
        }
        $dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
        foreach ($dirsociete as $dirroot)
        {
            $res=dol_include_once($dirroot.$module.'.php');
            if ($res) break;
        }
        $modCodeFournisseur = new $module;

        // Define if customer/prospect or supplier status is set or not
        if (GETPOST("type")!='f' && empty($conf->global->THIRDPARTY_NOTCUSTOMERPROSPECT_BY_DEFAULT))  { $object->client=3; }
        if (GETPOST("type")=='c')  { $object->client=1; }
        if (GETPOST("type")=='p')  { $object->client=2; }
        if (! empty($conf->fournisseur->enabled) && (GETPOST("type")=='f' || (GETPOST("type")=='' && empty($conf->global->THIRDPARTY_NOTSUPPLIER_BY_DEFAULT))))  { $object->fournisseur=1; }

        $object->name				= GETPOST('name', 'alpha');
        $object->firstname			= GETPOST('firstname', 'alpha');
        $object->particulier		= $private;
        $object->prefix_comm		= GETPOST('prefix_comm');
        $object->client				= GETPOST('client')?GETPOST('client'):$object->client;
        $object->code_client		= GETPOST('code_client', 'alpha');
        $object->fournisseur		= GETPOST('fournisseur')?GETPOST('fournisseur'):$object->fournisseur;
        $object->code_fournisseur	= GETPOST('code_fournisseur', 'alpha');
        $object->address			= GETPOST('address', 'alpha');
        $object->zip				= GETPOST('zipcode', 'alpha');
        $object->town				= GETPOST('town', 'alpha');
        $object->state_id			= GETPOST('state_id', 'int');
        $object->skype				= GETPOST('skype', 'alpha');
        $object->phone				= GETPOST('phone', 'alpha');
        $object->fax				= GETPOST('fax', 'alpha');
        $object->email				= GETPOST('email', 'custom', 0, FILTER_SANITIZE_EMAIL);
        $object->url				= GETPOST('url', 'custom', 0, FILTER_SANITIZE_URL);
        $object->capital			= GETPOST('capital', 'alpha');
        $object->barcode			= GETPOST('barcode', 'alpha');
        $object->idprof1			= GETPOST('idprof1', 'alpha');
        $object->idprof2			= GETPOST('idprof2', 'alpha');
        $object->idprof3			= GETPOST('idprof3', 'alpha');
        $object->idprof4			= GETPOST('idprof4', 'alpha');
        $object->idprof5			= GETPOST('idprof5', 'alpha');
        $object->idprof6			= GETPOST('idprof6', 'alpha');
        $object->typent_id			= GETPOST('typent_id', 'int');
        $object->effectif_id		= GETPOST('effectif_id', 'int');
        $object->civility_id		= GETPOST('civility_id', 'int');

        $object->tva_assuj			= GETPOST('assujtva_value', 'int');
        $object->status				= GETPOST('status', 'int');

        //Local Taxes
        $object->localtax1_assuj	= GETPOST('localtax1assuj_value', 'int');
        $object->localtax2_assuj	= GETPOST('localtax2assuj_value', 'int');

        $object->localtax1_value	=GETPOST('lt1', 'int');
        $object->localtax2_value	=GETPOST('lt2', 'int');

        $object->tva_intra			= GETPOST('tva_intra', 'alpha');

        $object->commercial_id		= GETPOST('commercial_id', 'int');
        $object->default_lang		= GETPOST('default_lang');

        $object->logo = (isset($_FILES['photo'])?dol_sanitizeFileName($_FILES['photo']['name']):'');

        // Gestion du logo de la société
        $dir     = $conf->societe->multidir_output[$conf->entity]."/".$object->id."/logos";
        $file_OK = (isset($_FILES['photo'])?is_uploaded_file($_FILES['photo']['tmp_name']):false);
        if ($file_OK)
        {
            if (image_format_supported($_FILES['photo']['name']))
            {
                dol_mkdir($dir);

                if (@is_dir($dir))
                {
                    $newfile=$dir.'/'.dol_sanitizeFileName($_FILES['photo']['name']);
                    $result = dol_move_uploaded_file($_FILES['photo']['tmp_name'], $newfile, 1);

                    if (! $result > 0)
                    {
                        $errors[] = "ErrorFailedToSaveFile";
                    }
                    else
                    {
                        // Create small thumbs for company (Ratio is near 16/9)
                        // Used on logon for example
                        $imgThumbSmall = vignette($newfile, $maxwidthsmall, $maxheightsmall, '_small', $quality);

                        // Create mini thumbs for company (Ratio is near 16/9)
                        // Used on menu or for setup page for example
                        $imgThumbMini = vignette($newfile, $maxwidthmini, $maxheightmini, '_mini', $quality);
                    }
                }
            }
        }

        // We set country_id, country_code and country for the selected country
        $object->country_id=GETPOST('country_id')?GETPOST('country_id'):$mysoc->country_id;
        if ($object->country_id)
        {
            $tmparray=getCountry($object->country_id,'all');
            $object->country_code=$tmparray['code'];
            $object->country=$tmparray['label'];
        }
        $object->forme_juridique_code=GETPOST('forme_juridique_code');
        /* Show create form */

        $linkback="";
        print load_fiche_titre($langs->trans("NewThirdParty"),$linkback,'title_companies.png');

        if (! empty($conf->use_javascript_ajax))
        {
            print "\n".'<script type="text/javascript">';
            print '$(document).ready(function () {
						id_te_private=8;
                        id_ef15=1;
                        is_private='.$private.';
						if (is_private) {
							$(".individualline").show();
              $(".companyline").hide();
						} else {
							$(".individualline").hide();
              $(".companyline").show();
						}
              $("#radiocompany").click(function() {
                        	$(".individualline").hide();
                        	$(".companyline").show();	
                        	$("#typent_id").val(0);
							$("#name_alias").show();
                        	$("#effectif_id").val(0);
                        	$("#TypeName").html(document.formsoc.ThirdPartyName.value);
                        	document.formsoc.private.value=0;
                        });
                        $("#radioprivate").click(function() {
                        	$(".individualline").show();
                        	$(".companyline").hide();
                        	$("#typent_id").val(id_te_private);
							$("#name_alias").hide();
                        	$("#effectif_id").val(id_ef15);
                        	$("#TypeName").html(document.formsoc.LastName.value);
                        	document.formsoc.private.value=1;
                        });
                        $("#selectcountry_id").change(function() {
                        	document.formsoc.action.value="create";
                        	document.formsoc.submit();
                        });
                     });';
            print '</script>'."\n";

            print '<div id="selectthirdpartytype">';
            print '<div class="hideonsmartphone float">';
            print $langs->trans("ThirdPartyType").': &nbsp; &nbsp; ';
            print '</div>';
	        print '<label for="radiocompany">';
            print '<input type="radio" id="radiocompany" class="flat" name="private"  value="0"'.($private?'':' checked').'>';
	        print '&nbsp;';
            print $langs->trans("Company/Fundation");
	        print '</label>';
            print ' &nbsp; &nbsp; ';
	        print '<label for="radioprivate">';
            $text ='<input type="radio" id="radioprivate" class="flat" name="private" value="1"'.($private?' checked':'').'>';
	        $text.='&nbsp;';
	        $text.= $langs->trans("Individual");
	        $htmltext=$langs->trans("ToCreateContactWithSameName");
	        print $form->textwithpicto($text, $htmltext, 1, 'help', '', 0, 3);
            print '</label>';
            print '</div>';
            print "<br>\n";
        }

        dol_htmloutput_mesg(is_numeric($error)?'':$error, $errors, 'error');

        print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'?canvas=societe@ciai" method="post" name="formsoc">';

        print '<input type="hidden" name="action" value="add">';
        print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        print '<input type="hidden" name="private" value='.$object->particulier.'>';
        print '<input type="hidden" name="type" value='.GETPOST("type").'>';
        print '<input type="hidden" name="LastName" value="'.$langs->trans('LastName').'">';
        print '<input type="hidden" name="ThirdPartyName" value="'.$langs->trans('ThirdPartyName').'">';
        if ($modCodeClient->code_auto || $modCodeFournisseur->code_auto) print '<input type="hidden" name="code_auto" value="1">';

        dol_fiche_head(null, 'card', '', 0, '');

        print '<table class="border" width="100%">';

        // Name, firstname
	    print '<tr><td>';
        if ($object->particulier || $private)
        {
	        print '<span id="TypeName" class="fieldrequired">'.$langs->trans('LastName','name').'</span>';
        }
        else
        {	
        	print '<span span id="TypeName" class="fieldrequired">'.fieldLabel('ThirdPartyName','name').'</span>';
        }
        
	    print '</td><td'.(empty($conf->global->SOCIETE_USEPREFIX)?' colspan="3"':'').'>';
	    print '<input type="text" size="60" maxlength="128" name="name" id="name" value="'.$object->name.'" autofocus="autofocus"></td>';
	    
	    if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
	    {
		    print '<td>'.$langs->trans('Prefix').'</td><td><input type="text" size="5" maxlength="5" name="prefix_comm" value="'.$object->prefix_comm.'"></td>';
	    }
	    print '</tr>';

        // If javascript on, we show option individual
        if ($conf->use_javascript_ajax)
        {
            print '<tr class="individualline"><td class="fieldrequired">'.fieldLabel('FirstName','firstname').'</td>';
	        print '<td><input type="text"  size="60" name="firstname" id="firstname" value="'.$object->firstname.'"></td>';
            print '<td colspan=2>&nbsp;</td></tr>';
           
            /*print '<tr class="individualline"><td>'.fieldLabel('UserTitle','civility_id').'</td><td>';
            print $formcompany->select_civility($object->civility_id).'</td>';
            print '<td colspan=2>&nbsp;</td></tr>';*/
        }
        
        // Prof ids
        $i=1; $j=0;
        while ($i <= 6)
        {
        	if($i == 4) // mostro solo il codice fiscale
        	{
        		$idprof=$langs->transcountry('ProfId'.$i,$object->country_code);
        		if ($idprof!='-')
        		{
        			$key='idprof'.$i;
        
        			if (($j % 2) == 0) print '<tr class="individualline">';
        
        			$idprof_mandatory ='SOCIETE_IDPROF'.($i).'_MANDATORY';
        			if(empty($conf->global->$idprof_mandatory))
        				print '<td class="fieldrequired">'.fieldLabel($idprof,$key).'</td><td>';
        			else
        				print '<td class="fieldrequired">'.fieldLabel($idprof,$key,1).'</td><td>';
        
        					print $formcompany->get_input_id_prof($i,$key,$object->$key,$object->country_code);
        					print '</td>';
        					if (($j % 2) == 1) print '</tr>';
        					$j++;
        		}
        	}
        	$i++;
        }
        
        if ($j % 2 == 1) print '<td colspan="2"></td></tr>';

        // Alias names (commercial, trademark or alias names)
        //print '<tr id="name_alias"><td><label for="name_alias_input">'.$langs->trans('AliasNames').'</label></td>';
	      // print '<td colspan="3"><input type="text" size="60" name="name_alias" id="name_alias_input" value="'.$object->name_alias.'" size="32"></td></tr>';

        // Prospect/Customer
      /*  print '<tr><td width="25%">'.fieldLabel('ProspectCustomer','customerprospect',1).'</td>';
        print '<td width="25%" class="maxwidthonsmartphone"><select class="flat" name="client" id="customerprospect">';
        $selected=isset($_POST['client'])?GETPOST('client'):$object->client;
        if (GETPOST("type") == '') print '<option value="-1"></option>';
        if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS)) print '<option value="2"'.($selected==2?' selected':'').'>'.$langs->trans('Prospect').'</option>';
        if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print '<option value="3"'.($selected==3?' selected':'').'>'.$langs->trans('ProspectCustomer').'</option>';
        if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print '<option value="1"'.($selected==1?' selected':'').'>'.$langs->trans('Customer').'</option>';
        print '<option value="0"'.((string) $selected == '0'?' selected':'').'>'.$langs->trans('NorProspectNorCustomer').'</option>';
        print '</select></td>';

        print '<td width="25%">'.fieldLabel('CustomerCode','customer_code').'</td><td width="25%">';
        print '<table class="nobordernopadding"><tr><td>';
        $tmpcode=$object->code_client;
        if (empty($tmpcode) && ! empty($modCodeClient->code_auto)) $tmpcode=$modCodeClient->getNextValue($object,0);
        print '<input type="text" name="code_client" id="customer_code" size="16" value="'.dol_escape_htmltag($tmpcode).'" maxlength="15">';
        print '</td><td>';
        $s=$modCodeClient->getToolTip($langs,$object,0);
        print $form->textwithpicto('',$s,1);
        print '</td></tr></table>';
        print '</td></tr>';

        if (! empty($conf->fournisseur->enabled) && ! empty($user->rights->fournisseur->lire))
        {
            // Supplier
            print '<tr>';
            print '<td>'.fieldLabel('Supplier','fournisseur',1).'</td><td>';
            print $form->selectyesno("fournisseur", (isset($_POST['fournisseur'])?GETPOST('fournisseur'):(GETPOST("type") == '' ? -1 : $object->fournisseur)), 1, 0, (GETPOST("type") == '' ? 1 : 0));
            print '</td>';
            print '<td>'.fieldLabel('SupplierCode','supplier_code').'</td><td>';
            print '<table class="nobordernopadding"><tr><td>';
            $tmpcode=$object->code_fournisseur;
            if (empty($tmpcode) && ! empty($modCodeFournisseur->code_auto)) $tmpcode=$modCodeFournisseur->getNextValue($object,1);
            print '<input type="text" name="code_fournisseur" id="supplier_code" size="16" value="'.dol_escape_htmltag($tmpcode).'" maxlength="15">';
            print '</td><td>';
            $s=$modCodeFournisseur->getToolTip($langs,$object,1);
            print $form->textwithpicto('',$s,1);
            print '</td></tr></table>';
            print '</td></tr>';
        }

        // Status
        print '<tr><td>'.fieldLabel('Status','status').'</td><td colspan="3">';
        print $form->selectarray('status', array('0'=>$langs->trans('ActivityCeased'),'1'=>$langs->trans('InActivity')),1);
        print '</td></tr>';

        // Barcode
        if (! empty($conf->barcode->enabled))
        {
            print '<tr><td>'.fieldLabel('Gencod','barcode').'</td>';
	        print '<td colspan="3"><input type="text" name="barcode" id="barcode" value="'.$object->barcode.'">';
            print '</td></tr>';
        }
*/
        // Address
        print '<tr class="companyline"><td class="tdtop">'.fieldLabel('Address','address').'</td>';
        print '<td colspan="3"><textarea name="address" id="address" cols="80" rows="'._ROWS_2.'" wrap="soft">';
        print $object->address;
        print '</textarea></td></tr>';

        // Zip / Town
        print '<tr class="companyline"><td>'.fieldLabel('Zip','zipcode').'</td><td>';
        print $formcompany->select_ziptown($object->zip,'zipcode',array('town','selectcountry_id','state_id'),6);
        print '</td><td>'.fieldLabel('Town','town').'</td><td>';
        print $formcompany->select_ziptown($object->town,'town',array('zipcode','selectcountry_id','state_id'));
        print '</td></tr>';

        // Country
        print '<tr class="companyline"><td width="25%">'.fieldLabel('Country','selectcountry_id').'</td><td colspan="3" class="maxwidthonsmartphone">';
        print $form->select_country((GETPOST('country_id')!=''?GETPOST('country_id'):$object->country_id));
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
        print '</td></tr>';

        // State
        if (empty($conf->global->SOCIETE_DISABLE_STATE))
        {
            print '<tr class="companyline"><td>'.fieldLabel('State','state_id').'</td><td colspan="3" class="maxwidthonsmartphone">';
            if ($object->country_id) print $formcompany->select_state($object->state_id,$object->country_code);
            else print $countrynotdefined;
            print '</td></tr>';
        }
        
        // sesso
        
        
        // data di nascita
        
        
        // luogo di nascita

        // Email web
        print '<tr><td>'.fieldLabel('EMail','email').(! empty($conf->global->SOCIETE_MAIL_REQUIRED)?'*':'').'</td>';
        print '<td colspan="3"><input type="text" name="email" id="email" size="32" value="'.$object->email.'"></td></tr>';
/*
        print '<tr><td>'.fieldLabel('Web','url').'</td>';
        print '<td colspan="3"><input type="text" name="url" id="url" size="32" value="'.$object->url.'"></td></tr>';
*/
        // Skype
        /*if (! empty($conf->skype->enabled))
        {
            print '<tr><td>'.fieldLabel('Skype','skype').'</td>';
	        print '<td colspan="3"><input type="text" name="skype" id="skype" size="32" value="'.$object->skype.'"></td></tr>';
        }*/

        // Phone / Fax
        print '<tr><td>'.fieldLabel('Phone','phone').'</td>';
        print '<td><input type="text" name="phone" id="phone" value="'.$object->phone.'"></td><td></td></td></tr>';
        /*print '<td>'.fieldLabel('Fax','fax').'</td>';
        print '<td><input type="text" name="fax" id="fax" value="'.$object->fax.'"></td></tr>';
*/

        // Assujeti TVA
        print '<tr class="companyline"><td>'.fieldLabel('VATIsUsed','assujtva_value').'</td>';
        print '<td>';
        print $form->selectyesno('assujtva_value',1,1);     // Assujeti par defaut en creation
        print '</td>';
        print '<td class="nowrap">'.fieldLabel('VATIntra','intra_vat').'</td>';
        print '<td class="nowrap">';
        $s = '<input type="text" class="flat" name="tva_intra" id="intra_vat" size="12" maxlength="20" value="'.$object->tva_intra.'">';

        if (empty($conf->global->MAIN_DISABLEVATCHECK))
        {
            $s.=' ';

            if (! empty($conf->use_javascript_ajax))
            {
                print "\n";
                print '<script language="JavaScript" type="text/javascript">';
                print "function CheckVAT(a) {\n";
                print "newpopup('".DOL_URL_ROOT."/societe/checkvat/checkVatPopup.php?vatNumber='+a,'".dol_escape_js($langs->trans("VATIntraCheckableOnEUSite"))."',500,300);\n";
                print "}\n";
                print '</script>';
                print "\n";
                $s.='<a href="#" class="hideonsmartphone" onclick="javascript: CheckVAT(document.formsoc.tva_intra.value);">'.$langs->trans("VATIntraCheck").'</a>';
                $s = $form->textwithpicto($s,$langs->trans("VATIntraCheckDesc",$langs->trans("VATIntraCheck")),1);
            }
            else
            {
                $s.='<a href="'.$langs->transcountry("VATIntraCheckURL",$object->country_id).'" target="_blank">'.img_picto($langs->trans("VATIntraCheckableOnEUSite"),'help').'</a>';
            }
        }
        print $s;
        print '</td>';
        print '</tr>';

        // Type - Size
/*        print '<tr><td>'.fieldLabel('ThirdPartyType','typent_id').'</td><td>'."\n";
        $sortparam=(empty($conf->global->SOCIETE_SORT_ON_TYPEENT)?'ASC':$conf->global->SOCIETE_SORT_ON_TYPEENT); // NONE means we keep sort of original array, so we sort on position. ASC, means next function will sort on label.
        print $form->selectarray("typent_id", $formcompany->typent_array(0), $object->typent_id, 0, 0, 0, '', 0, 0, 0, $sortparam);
        if ($user->admin) print ' '.info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
        print '</td>';
        print '<td>'.fieldLabel('Staff','effectif_id').'</td><td>';
        print $form->selectarray("effectif_id", $formcompany->effectif_array(0), $object->effectif_id);
        if ($user->admin) print ' '.info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
        print '</td></tr>';

        // Legal Form
        print '<tr><td>'.fieldLabel('JuridicalStatus','forme_juridique_code').'</td>';
        print '<td colspan="3" class="maxwidthonsmartphone">';
        if ($object->country_id)
        {
            print $formcompany->select_juridicalstatus($object->forme_juridique_code, $object->country_code, '', 'forme_juridique_code');
        }
        else
        {
            print $countrynotdefined;
        }
        print '</td></tr>';

        // Capital
        print '<tr><td>'.fieldLabel('Capital','capital').'</td>';
	    print '<td colspan="3"><input type="text" name="capital" id="capital" size="10" value="'.$object->capital.'"> ';
        print '<span class="hideonsmartphone">'.$langs->trans("Currency".$conf->currency).'</span></td></tr>';

        // Local Taxes
        //TODO: Place into a function to control showing by country or study better option
        if($mysoc->localtax1_assuj=="1" && $mysoc->localtax2_assuj=="1")
        {
            print '<tr><td>'.$langs->transcountry("LocalTax1IsUsed",$mysoc->country_code).'</td><td>';
            print $form->selectyesno('localtax1assuj_value',0,1);
            print '</td><td>'.$langs->transcountry("LocalTax2IsUsed",$mysoc->country_code).'</td><td>';
            print $form->selectyesno('localtax2assuj_value',0,1);
            print '</td></tr>';

        }
        elseif($mysoc->localtax1_assuj=="1")
        {
            print '<tr><td>'.$langs->transcountry("LocalTax1IsUsed",$mysoc->country_code).'</td><td colspan="3">';
            print $form->selectyesno('localtax1assuj_value',0,1);
            print '</td><tr>';
        }
        elseif($mysoc->localtax2_assuj=="1")
        {
            print '<tr><td>'.$langs->transcountry("LocalTax2IsUsed",$mysoc->country_code).'</td><td colspan="3">';
            print $form->selectyesno('localtax2assuj_value',0,1);
            print '</td><tr>';
        }
*/
        if (! empty($conf->global->MAIN_MULTILANGS))
        {
            print '<tr><td>'.fieldLabel('DefaultLang','default_lang').'</td><td colspan="3" class="maxwidthonsmartphone">'."\n";
            print $formadmin->select_language(($object->default_lang?$object->default_lang:$conf->global->MAIN_LANG_DEFAULT),'default_lang',0,0,1);
            print '</td>';
            print '</tr>';
        }

/*        if ($user->rights->societe->client->voir)
        {
            // Assign a Name
            print '<tr>';
            print '<td>'.fieldLabel('AllocateCommercial','commercial_id').'</td>';
            print '<td colspan="3" class="maxwidthonsmartphone">';
            $form->select_dolusers((! empty($object->commercial_id)?$object->commercial_id:$user->id),'commercial_id',1); // Add current user by default
            print '</td></tr>';
        }
*/
		// Incoterms
		if (!empty($conf->incoterm->enabled))
		{
			print '<tr>';
			print '<td>'.fieldLabel('IncotermLabel','incoterm_id').'</td>';
	        print '<td colspan="3" class="maxwidthonsmartphone">';
	        print $form->select_incoterms((!empty($object->fk_incoterms) ? $object->fk_incoterms : ''), (!empty($object->location_incoterms)?$object->location_incoterms:''));
			print '</td></tr>';
		}

		// Categories
		if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire))
		{
			$langs->load('categories');

			// Customer
			if ($object->prospect || $object->client) {
				print '<tr><td class="toptd">' . fieldLabel('CustomersCategoriesShort', 'custcats') . '</td><td colspan="3">';
				$cate_arbo = $form->select_all_categories(Categorie::TYPE_CUSTOMER, null, 'parent', null, null, 1);
				print $form->multiselectarray('custcats', $cate_arbo, GETPOST('custcats', 'array'), null, null, null,
					null, "90%");
				print "</td></tr>";
			}

			// Supplier
/*			if ($object->fournisseur) {
				print '<tr><td class="toptd">' . fieldLabel('SuppliersCategoriesShort', 'suppcats') . '</td><td colspan="3">';
				$cate_arbo = $form->select_all_categories(Categorie::TYPE_SUPPLIER, null, 'parent', null, null, 1);
				print $form->multiselectarray('suppcats', $cate_arbo, GETPOST('suppcats', 'array'), null, null, null,
					null, "90%");
				print "</td></tr>";
			} 
*/
		}

		// Multicurrency
		if (! empty($conf->multicurrency->enabled))
		{
			print '<tr>';
			print '<td>'.fieldLabel('Currency','multicurrency_code').'</td>';
	        print '<td colspan="3" class="maxwidthonsmartphone">';
	        print $form->selectMultiCurrency(($object->multicurrency_code ? $object->multicurrency_code : $conf->currency), 'multicurrency_code', 1);
			print '</td></tr>';
		}

        // Other attributes
        $parameters=array('colspan' => ' colspan="3"', 'colspanvalue' => '3');
        $reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
        print $hookmanager->resPrint;
        if (empty($reshook) && ! empty($extrafields->attribute_label))
        {
        	print $object->showOptionals($extrafields,'edit');
        }

        // Ajout du logo
/*        print '<tr class="hideonsmartphone">';
        print '<td>'.fieldLabel('Logo','photoinput').'</td>';
        print '<td colspan="3">';
        print '<input class="flat" type="file" name="photo" id="photoinput" />';
        print '</td>';
        print '</tr>';
*/
        print '</table>'."\n";

        dol_fiche_end();

        print '<div class="center">';
        print '<input type="submit" class="button" name="create" value="'.$langs->trans('AddThirdParty').'">';
        if ($backtopage)
        {
            print ' &nbsp; ';
            print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
        }
        print '</div>'."\n";

        print '</form>'."\n";
    }
?>