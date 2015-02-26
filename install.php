<?php


$DebugMode = false;
// -------------------------INITIALIZATION-----------------------------//
define('DIR_ROOT', str_replace("\\","/",realpath(dirname(__FILE__))).'/published/SC/html/scripts');
include(DIR_ROOT.'/includes/init.php');
include_once(DIR_CFG.'/connect.inc.wa.php');
include_once(DIR_FUNC.'/setting_functions.php' );
require_once(DIR_FUNC.'/product_functions.php');
require_once(DIR_FUNC.'/reg_fields_functions.php' );
require_once(DIR_FUNC.'/order_status_functions.php' );
require_once(DIR_FUNC.'/cart_functions.php');
require_once(DIR_FUNC.'/order_functions.php' );
if(!defined('WBS_DIR')){
	define('WBS_DIR',realpath(dirname(__FILE__)));
}


$DB_tree = new DataBase();
db_connect(SystemSettings::get('DB_HOST'),SystemSettings::get('DB_USER'),SystemSettings::get('DB_PASS')) or die (db_error());
db_select_db(SystemSettings::get('DB_NAME')) or die (db_error());
$DB_tree->connect(SystemSettings::get('DB_HOST'), SystemSettings::get('DB_USER'), SystemSettings::get('DB_PASS'));
$DB_tree->query("SET character_set_client='".MYSQL_CHARSET."'");
$DB_tree->query("SET character_set_connection='".MYSQL_CHARSET."'");
$DB_tree->query("SET character_set_results='".MYSQL_CHARSET."'");
$DB_tree->selectDB(SystemSettings::get('DB_NAME'));
define('VAR_DBHANDLER','DBHandler');
$Register = &Register::getInstance();
$Register->set(VAR_DBHANDLER, $DB_tree);
settingDefineConstants();
$admin_mode = false;
if(isset($_SESSION['__WBS_SC_DATA'])&&isset($_SESSION['__WBS_SC_DATA']["U_ID"])||isset($_SESSION['wbs_username'])){
	$admin_mode = true;
}
session_write_close();

if(!$admin_mode){
	header('location: /published/index.php');
}else{
	set_time_limit (0);
	
		function clean_Cache(){
			require_once('/published/wbsadmin/classes/class.diagnostictools.php');
			$tools = new DiagnosticTools(WBS_DIR);
			$res = true;
				$res = $res&$tools->cleanCache('temp',$errorStr,'/^\.cache\.|^\.settings\./');
				$SCFolders = scandir(WBS_DIR.'/data');
				$applications = array();
				foreach($SCFolders as $SCFolder){
					if(($SCFolder == '.')
						||($SCFolder == '..')
						||!is_dir(WBS_DIR.'/data/'.$SCFolder)
					){
						continue;		
					}
					if(realpath(WBS_DIR.'/data/'.$SCFolder.'/attachments/SC/temp/')){
						$applications[] = 'data/'.$SCFolder.'/attachments/SC/temp/';
					}
				}
				if(count($applications)){
					$res = $res&$tools->cleanCache($applications,$errorStr,'/^\.cache\.|^\.settings\./');
				}
			
			$res = $res&$tools->cleanCache('kernel/includes/smarty/compiled',$errorStr,'/\.php$/');
				$applications = array('wbsadmin/localization');
				$applicationFolders = scandir(WBS_DIR.'/published');
				foreach($applicationFolders as $applicationFolder){
					if(preg_match('/^\w{2}$/',$applicationFolder)){
						$applications[] = 'published/'.$applicationFolder.'/localization/';
						$applications[] = 'published/'.$applicationFolder.'/2.0/localization/';
					}
				}
				$applications[] = 'published/wbsadmin/localization/';
				$SCFolders = scandir(WBS_DIR.'/data');
				foreach($SCFolders as $SCFolder){
					if(($SCFolder == '.')
						||($SCFolder == '..')
						||!is_dir(WBS_DIR.'/data/'.$SCFolder)
					){
						continue;		
					}
					if(realpath(WBS_DIR.'/data/'.$SCFolder.'/attachments/SC/temp/loc_cache/')){
						$applications[] = 'data/'.$SCFolder.'/attachments/SC/temp/loc_cache/';
					}
				}
				$res = $res&$tools->cleanCache($applications,$errorStr,'/(^\.cache\.)|(^serlang.+\.cch$)/',null,false);
		}
		
		function cleanDirectory($dirname){
			$dirname = realpath($dirname);
			if(file_exists($dirname)&&is_dir($dirname)&&($dir = opendir($dirname))){
				while ($name = readdir($dir)){
					if($name == '.'|| $name == '..')continue;

					$path = $dirname.'/'.$name;
					if(is_dir($path)){
						cleanDirectory($path);
					}elseif (file_exists($path)){
						@unlink($path);
					}
				}
				closedir($dir);
			}
		}
		
		
		function sql(){	
			$languages = &LanguagesManager::getLanguages();
			$sqls = array();
			
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_ENABLE'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_FIELDS_STANDART'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_FIELDS_FAST'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_TYPES_ORDERING'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_SHOW_DEFAULT'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_YANDEX_ADRESS_ENABLE'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_YANDEX_ADRESS_FIELDS'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_QIWI_PHONE_FIELD'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_COMPANY_NAME_FIELD'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_INN_NAME_FIELD'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_INFORMER_TYPE'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
		

			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_ENABLE', '0', 'conf_onesteporder_enable_title', 'conf_onesteporder_enable_desc', 'setting_CHECK_BOX(', 11);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_TYPES_ORDERING', 'all', 'conf_onesteporder_types_ordering_title', 'conf_onesteporder_types_ordering_desc', 'setting_RADIOGROUP(translate(\'conf_onesteporder_only_fast\').\':fast,\'.translate(\'conf_onesteporder_only_standart\').\':standart,\'.translate(\'conf_onesteporder_fast_and_standart\').\':all\',', 12);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_SHOW_DEFAULT', 'no', 'conf_onesteporder_show_default_title', 'conf_onesteporder_show_default_desc', 'setting_RADIOGROUP(translate(\'conf_onesteporder_open_nothing\').\':no,\'.translate(\'conf_onesteporder_open_fast\').\':fast,\'.translate(\'conf_onesteporder_open_standart\').\':standart\',', 13);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
					
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_FIELDS_STANDART', '', 'conf_onesteporder_fields_standart_title', 'conf_onesteporder_fields_standart_desc', 'settingCONF_ONESTEPORDER_FIELDS_STANDART()', 14);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_FIELDS_FAST', '', 'conf_onesteporder_fields_fast_title', 'conf_onesteporder_fields_fast_desc', 'settingCONF_ONESTEPORDER_FIELDS_FAST()', 15);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_YANDEX_ADRESS_ENABLE', '0', 'conf_onesteporder_yandex_adress_enable_title', 'conf_onesteporder_yandex_adress_enable_desc', 'setting_CHECK_BOX(', 16);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_YANDEX_ADRESS_FIELDS', '', 'conf_onesteporder_yandex_adress_fields_title', 'conf_onesteporder_yandex_adress_fields_desc', 'settingCONF_ONESTEPORDER_YANDEX_ADRESS_FIELDS()', 17);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_QIWI_PHONE_FIELD', '0', 'conf_onesteporder_qiwi_phone_field_title', 'conf_onesteporder_qiwi_phone_field_desc', 'settingCONF_ONESTEPORDER_QIWI_PHONE_FIELD()', 18);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');	

			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_COMPANY_NAME_FIELD', '0', 'conf_onesteporder_company_name_field_title', 'conf_onesteporder_company_name_field_desc', 'settingCONF_ONESTEPORDER_COMPANY_NAME_FIELD()', 19);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');

			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_INN_NAME_FIELD', '0', 'conf_onesteporder_inn_name_field_title', 'conf_onesteporder_inn_name_field_desc', 'settingCONF_ONESTEPORDER_INN_NAME_FIELD()', 20);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_INFORMER_TYPE', '0', 'conf_onesteporder_informer_type_title', 'conf_onesteporder_informer_type_desc', 'setting_RADIOGROUP(translate(\'conf_onesteporder_informer_type_cart\').\':cart,\'.translate(\'conf_onesteporder_informer_type_inform\').\':inform\',', 21);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			
			
		//local			
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_enable_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_enable_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_types_ordering_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_types_ordering_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_only_fast'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_only_standart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fast_and_standart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_show_default_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_show_default_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_open_nothing'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_open_fast'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_open_standart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fields_standart_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fields_standart_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fields_fast_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fields_fast_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_yandex_adress_enable_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_yandex_adress_enable_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_yandex_adress_fields_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_yandex_adress_fields_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_user_adresses'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_remove_all_elements'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_selected_elements'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_discount'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_total_cart_element'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_ordering_type'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_fast_type'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_standart_type'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_auth_type'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_auth'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_contact'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_yandex_adress_url'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_show_your_adress'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_your_adress'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_select_your_adress'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_shipping'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_billing'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_total_cart_fast'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_total_cart_standart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_order'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_street'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_building'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_suite'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_flat'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_entrance'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_floor'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_intercom'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_zip'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_metro'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_cargolift'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_fathersname'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_phone'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_phone-extra'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_empty_email'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_qiwi_phone_field_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_qiwi_phone_field_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_billing_as_shipping'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_company_name_field_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_company_name_field_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_inn_name_field_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_inn_name_field_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_cart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_inform'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			
			foreach($languages as $language){			
				if($language->iso2 == 'ru'){
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_enable_title', ".$language->id.", 'Активизация модуля \"Ajax оформление заказа в 1 шаг + Яндекс.Быстрый заказ\"', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_enable_desc', ".$language->id.", 'Включите данную опцию для активизации модуля. Если опция отключена, то будет отображаться стандартный модуль.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_types_ordering_title', ".$language->id.", 'Возможные способы оформления заказа', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_types_ordering_desc', ".$language->id.", 'Вы можете выбрать, какие способы оформления будут отображаться пользователю', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_only_fast', ".$language->id.", 'Только Быстрый', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_only_standart', ".$language->id.", 'Только Полный', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fast_and_standart', ".$language->id.", 'Быстрый и Полный', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_show_default_title', ".$language->id.", 'Способ оформления по умолчанию', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_show_default_desc', ".$language->id.", 'Данная опция отвечает за отображение способа оформления по умолчанию. При открытии корзины данный способ будет раскрыт автоматически.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_open_nothing', ".$language->id.", 'Все скрыто', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_open_fast', ".$language->id.", 'Быстрый способ оформления', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_open_standart', ".$language->id.", 'Полный способ оформления', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fields_standart_title', ".$language->id.", 'Поля для ПОЛНОГО оформления', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fields_standart_desc', ".$language->id.", 'Настройте требуемые поля для оформления. Те поля которые являются обязательными при подтверждения заказа будут возвращать NULL', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fields_fast_title', ".$language->id.", 'Поля для БЫСТРОГО оформления', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fields_fast_desc', ".$language->id.", 'Настройте требуемые поля для оформления. Те пол которые являются обязательными при подтверждения заказа будут возвращать NULL', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_yandex_adress_enable_title', ".$language->id.", 'Включить модуль Яндекс.Быстрый заказ', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_yandex_adress_enable_desc', ".$language->id.", 'Данный модуль позволит пользователям заполнять поля с помощью своего аккаунта в Яндекс. Данный модуль работает, только если включен \"Полный\" или \"Быстрый и Полный\" способ оформления', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_yandex_adress_fields_title', ".$language->id.", 'Настройка полей для Яндекс.Быстрый заказ', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_yandex_adress_fields_desc', ".$language->id.", 'Выставите поля Яндекса в соответствии с вашими дополнительными полями. Остальные поля (Имя, Фамилия, Индекс, Страна, Город, Адрес и т.д.) уже выставлены в модуле.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_user_adresses', ".$language->id.", 'Адреса пользователя', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_remove_all_elements', ".$language->id.", 'Очистить корзину', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_selected_elements', ".$language->id.", 'Выбранные продукты', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_discount', ".$language->id.", 'Скидка', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_total_cart_element', ".$language->id.", 'Итого за товары', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_ordering_type', ".$language->id.", 'Способ оформления', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_fast_type', ".$language->id.", 'Быстрое оформление', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_standart_type', ".$language->id.", 'Полное оформление', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_auth_type', ".$language->id.", 'Войти с паролем', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_auth', ".$language->id.", 'Авторизоваться', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_contact', ".$language->id.", 'Ваши данные', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_yandex_adress_url', ".$language->id.", 'Заполнить поля с помощью Яндекс.Быстрый заказ', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_show_your_adress', ".$language->id.", 'Показать все ваши адреса', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_your_adress', ".$language->id.", 'Ваши адреса', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_select_your_adress', ".$language->id.", 'Выбрать данный адрес', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_shipping', ".$language->id.", 'Способы доставки', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_billing', ".$language->id.", 'Способы оплаты', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_total_cart_fast', ".$language->id.", 'Итого без учета стоимости доставки', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_total_cart_standart', ".$language->id.", 'Итого с учетом стоимости доставки', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_order', ".$language->id.", 'Оформить заказ', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_street', ".$language->id.", 'Улица', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_building', ".$language->id.", 'Номер дома', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_suite', ".$language->id.", 'Корпус', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_flat', ".$language->id.", 'Квартира', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_entrance', ".$language->id.", 'Подъезд', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_floor', ".$language->id.", 'Этаж', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_intercom', ".$language->id.", 'Домофон', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_zip', ".$language->id.", 'Индекс', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_metro', ".$language->id.", 'Станция метро', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_cargolift', ".$language->id.", 'Наличие грузового лифта', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_fathersname', ".$language->id.", 'Отчество', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_phone', ".$language->id.", 'Телефон', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_phone-extra', ".$language->id.", 'Дополнительный телефон', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_empty_email', ".$language->id.", 'Некорректный номер телефона', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					
					
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_qiwi_phone_field_title', ".$language->id.", 'Поле телефона', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_qiwi_phone_field_desc', ".$language->id.", 'Для корректной работы некоторых модулей оплаты и доставки', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_company_name_field_title', ".$language->id.", 'Поле название компании', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_company_name_field_desc', ".$language->id.", 'Для корректной работы некоторых модулей оплаты и доставки', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_inn_name_field_title', ".$language->id.", 'Поле ИНН', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_inn_name_field_desc', ".$language->id.", 'Для корректной работы некоторых модулей оплаты и доставки', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_title', ".$language->id.", 'Тип отображения информации о добавленом продукте', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_desc', ".$language->id.", 'После того как продукт добавлен в корзину загружается диалоговое окно. Выберите какое окно отображать.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_cart', ".$language->id.", 'Мини корзина', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_inform', ".$language->id.", 'Информер', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_billing_as_shipping', ".$language->id.", 'Плательщик совпадает с получателем', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

	

				}else{		
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_enable_title', ".$language->id.", 'Activating the module \"Ajax ordering in step 1 + Yandex.Address\"', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_enable_desc', ".$language->id.", 'Enable this option to activate the modules. If disabled, it will display a standard module.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_types_ordering_title', ".$language->id.", 'Possible ways of ordering', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_types_ordering_desc', ".$language->id.", 'You can choose which methods of registration will be displayed to the user', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_only_fast', ".$language->id.", 'Only Fast', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_only_standart', ".$language->id.", 'Only Full', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fast_and_standart', ".$language->id.", 'Quick and Full', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_show_default_title', ".$language->id.", 'Way back to the default', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_show_default_desc', ".$language->id.", 'This feature is responsible for displaying the current default skin. When you open the basket, this method will be revealed automatically.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_open_nothing', ".$language->id.", 'Everything is hidden', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_open_fast', ".$language->id.", 'A quick way to design', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_open_standart', ".$language->id.", 'Complete way to design', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fields_standart_title', ".$language->id.", 'The fields for the total clearance', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fields_standart_desc', ".$language->id.", 'Configure the required fields for registration. Those fields that are required in order confirmation will return NULL', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fields_fast_title', ".$language->id.", 'Fields for fast registration', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_fields_fast_desc', ".$language->id.", 'Configure the required fields for registration. Those floors that are required in order confirmation will return NULL', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_yandex_adress_enable_title', ".$language->id.", 'Order to enable the module Yandex.Address', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_yandex_adress_enable_desc', ".$language->id.", 'This module allows users to fill out the fields with your account to Yandex. This module works only if you enabled the \"Full\" or \"rapid and complete\" way to design', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_yandex_adress_fields_title', ".$language->id.", 'Setting up custom fields for Yandex.Address', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_yandex_adress_fields_desc', ".$language->id.", 'Yandex expose fields to meet your additional fields. The other fields (Name, Last Name, Zip, Country, City, Address, etc.) are already on display in the module.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_user_adresses', ".$language->id.", 'Address of the user', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_remove_all_elements', ".$language->id.", 'Empty Trash', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_selected_elements', ".$language->id.", 'Selected products', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_discount', ".$language->id.", 'Discount', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_total_cart_element', ".$language->id.", 'Total for products', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_ordering_type', ".$language->id.", 'Way to design', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_fast_type', ".$language->id.", 'Rapid clearance', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_standart_type', ".$language->id.", 'Full registration', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_auth_type', ".$language->id.", 'Log in', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_auth', ".$language->id.", 'Log in', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_contact', ".$language->id.", 'Your data', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_yandex_adress_url', ".$language->id.", 'Fill in the fields with Yandex.Address', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_show_your_adress', ".$language->id.", 'Show all your addresses', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_your_adress', ".$language->id.", 'Your address', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_select_your_adress', ".$language->id.", 'Select the address', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_shipping', ".$language->id.", 'Delivery Methods', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_billing', ".$language->id.", 'Methods of payment', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_total_cart_fast', ".$language->id.", 'Total excluding shipping costs', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_total_cart_standart', ".$language->id.", 'Total with shipping', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_order', ".$language->id.", 'Checkout', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_street', ".$language->id.", 'Street', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_building', ".$language->id.", 'House number', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_suite', ".$language->id.", 'Housing', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_flat', ".$language->id.", 'Apartment', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_entrance', ".$language->id.", 'Porch', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_floor', ".$language->id.", 'Floor', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_intercom', ".$language->id.", 'Intercom', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_zip', ".$language->id.", 'zip', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_metro', ".$language->id.", 'Subway station', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_cargolift', ".$language->id.", 'The presence of the freight elevator', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_fathersname', ".$language->id.", 'Patronymic', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_phone', ".$language->id.", 'Phone', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_phone-extra', ".$language->id.", 'Additional phone', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_empty_email', ".$language->id.", 'Incorrect phone number', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					
					
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_qiwi_phone_field_title', ".$language->id.", 'The field of mobile phone', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_qiwi_phone_field_desc', ".$language->id.", 'To work correctly, some of the modules of payment and delivery', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_company_name_field_title', ".$language->id.", 'The field name of the company', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_company_name_field_desc', ".$language->id.", 'To work correctly, some of the modules of payment and delivery', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_inn_name_field_title', ".$language->id.", 'The field name of the TIN', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_inn_name_field_desc', ".$language->id.", 'To work correctly, some of the modules of payment and delivery', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_title', ".$language->id.", 'Type of display information about adding products', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_desc', ".$language->id.", 'After the product is added to the cart loaded dialog. Choose which screen display.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_cart', ".$language->id.", 'Mini cart', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_inform', ".$language->id.", 'Informer', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_billing_as_shipping', ".$language->id.", 'Payer coincides with the recipient', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
				}
			} 
			
			
			$i = 1;	
			$returnValues = array();	
			foreach($sqls as $key => $sql){
				$percent = intval($i/count($sqls) * 100)."%";	
				db_phquery($sql['sql']);	
				
				echo '<script language="javascript">
				document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';\">'.$percent.'</div>";
				</script>';
				echo '<script language="javascript">
					document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file\"><div class=\"check_file_success\">Успешно</div>'.$sqls[$key]['success_msg'].'</div>";
					</script>';
					
				echo str_repeat(' ',1024*64);
				flush();
				usleep(100000);
				$i++;
			}
			return true;
		}
	
		function delete_sql(){
			
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_ENABLE'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_FIELDS_STANDART'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_FIELDS_FAST'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_TYPES_ORDERING'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_SHOW_DEFAULT'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_YANDEX_ADRESS_ENABLE'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_YANDEX_ADRESS_FIELDS'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_QIWI_PHONE_FIELD'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_COMPANY_NAME_FIELD'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_INN_NAME_FIELD'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_INFORMER_TYPE'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			
		//local			
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_enable_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_enable_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_types_ordering_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_types_ordering_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_only_fast'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_only_standart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fast_and_standart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_show_default_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_show_default_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_open_nothing'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_open_fast'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_open_standart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fields_standart_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fields_standart_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fields_fast_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_fields_fast_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_yandex_adress_enable_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_yandex_adress_enable_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_yandex_adress_fields_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_yandex_adress_fields_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_user_adresses'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_remove_all_elements'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_selected_elements'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_discount'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_total_cart_element'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_ordering_type'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_fast_type'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_standart_type'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_auth_type'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_auth'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_contact'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_yandex_adress_url'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_show_your_adress'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_your_adress'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_select_your_adress'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_shipping'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_billing'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_total_cart_fast'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_total_cart_standart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_order'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_street'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_building'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_suite'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_flat'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_entrance'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_floor'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_intercom'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_zip'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_metro'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_cargolift'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_fathersname'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_phone'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_phone-extra'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_empty_email'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_qiwi_phone_field_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_qiwi_phone_field_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_billing_as_shipping'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_company_name_field_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_company_name_field_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_inn_name_field_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_inn_name_field_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_cart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_inform'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			
			$i = 1;	
			$returnValues = array();	
			foreach($sqls as $key => $sql){
				$percent = intval($i/count($sqls) * 100)."%";	
				db_phquery($sql['sql']);	
				
				echo '<script language="javascript">
				document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';\">'.$percent.'</div>";
				</script>';
				echo '<script language="javascript">
					document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file\"><div class=\"check_file_success\">Успешно</div>'.$sqls[$key]['success_msg'].'</div>";
					</script>';
					
				echo str_repeat(' ',1024*64);
				flush();
				usleep(100000);
				$i++;
			}
			return true;
		}
	
		function update_sql(){

			$languages = &LanguagesManager::getLanguages();
			$sqls = array();
			
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_QIWI_PHONE_FIELD'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_COMPANY_NAME_FIELD'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_INN_NAME_FIELD'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."settings` WHERE `settings_constant_name` = 'CONF_ONESTEPORDER_INFORMER_TYPE'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."settings");
			
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_empty_email'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_qiwi_phone_field_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_qiwi_phone_field_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='onesteporder_billing_as_shipping'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_company_name_field_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_company_name_field_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_inn_name_field_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_inn_name_field_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_title'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_desc'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_cart'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
			$sqls[] = array('sql'=>"DELETE FROM `".DBTABLE_PREFIX."local` WHERE id='conf_onesteporder_informer_type_inform'",'success_msg'=>"Удаляем записи в ".DBTABLE_PREFIX."local");
				
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_QIWI_PHONE_FIELD', '0', 'conf_onesteporder_qiwi_phone_field_title', 'conf_onesteporder_qiwi_phone_field_desc', 'settingCONF_ONESTEPORDER_QIWI_PHONE_FIELD()', 18);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_COMPANY_NAME_FIELD', '0', 'conf_onesteporder_company_name_field_title', 'conf_onesteporder_company_name_field_desc', 'settingCONF_ONESTEPORDER_COMPANY_NAME_FIELD()', 19);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');

			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_INN_NAME_FIELD', '0', 'conf_onesteporder_inn_name_field_title', 'conf_onesteporder_inn_name_field_desc', 'settingCONF_ONESTEPORDER_INN_NAME_FIELD()', 20);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
			
			$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."settings` (`settings_groupID`, `settings_constant_name`, `settings_value`, `settings_title`, `settings_description`, `settings_html_function`, `sort_order`) VALUES(6, 'CONF_ONESTEPORDER_INFORMER_TYPE', '0', 'conf_onesteporder_informer_type_title', 'conf_onesteporder_informer_type_desc', 'setting_RADIOGROUP(translate(\'conf_onesteporder_informer_type_cart\').\':cart,\'.translate(\'conf_onesteporder_informer_type_inform\').\':inform\',', 21);",'success_msg'=>"Запись в ".DBTABLE_PREFIX."settings успешно добавлена",'inputValue'=>'settings_groupID');
		
		
			foreach($languages as $language){			
				if($language->iso2 == 'ru'){
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_empty_email', ".$language->id.", 'Некорректный номер телефона', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_qiwi_phone_field_title', ".$language->id.", 'Поле телефона', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_qiwi_phone_field_desc', ".$language->id.", 'Для корректной работы некоторых модулей оплаты и доставки', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_company_name_field_title', ".$language->id.", 'Поле название компании', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_company_name_field_desc', ".$language->id.", 'Для корректной работы некоторых модулей оплаты и доставки', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_inn_name_field_title', ".$language->id.", 'Поле ИНН', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_inn_name_field_desc', ".$language->id.", 'Для корректной работы некоторых модулей оплаты и доставки', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_title', ".$language->id.", 'Тип отображения информации о добавленом продукте', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_desc', ".$language->id.", 'После того как продукт добавлен в корзину загружается диалоговое окно. Выберите какое окно отображать.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_cart', ".$language->id.", 'Мини корзина', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_inform', ".$language->id.", 'Информер', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_billing_as_shipping', ".$language->id.", 'Плательщик совпадает с получателем', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

				}else{		
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_empty_email', ".$language->id.", 'Incorrect phone number', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_qiwi_phone_field_title', ".$language->id.", 'The field of mobile phone', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_qiwi_phone_field_desc', ".$language->id.", 'To work correctly, some of the modules of payment and delivery', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_company_name_field_title', ".$language->id.", 'The field name of the company', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_company_name_field_desc', ".$language->id.", 'To work correctly, some of the modules of payment and delivery', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_inn_name_field_title', ".$language->id.", 'The field name of the TIN', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_inn_name_field_desc', ".$language->id.", 'To work correctly, some of the modules of payment and delivery', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  

					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_title', ".$language->id.", 'Type of display information about adding products', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_desc', ".$language->id.", 'After the product is added to the cart loaded dialog. Choose which screen display.', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_cart', ".$language->id.", 'Mini cart', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('conf_onesteporder_informer_type_inform', ".$language->id.", 'Informer', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
					$sqls[] = array('sql'=>"INSERT INTO `".DBTABLE_PREFIX."local` VALUES ('onesteporder_billing_as_shipping', ".$language->id.", 'Payer coincides with the recipient', 'general', 'gen')",'success_msg'=>"Запись в ".DBTABLE_PREFIX."local успешно добавлена");  
				}
			} 
			
			
			$i = 1;	
			$returnValues = array();	
			foreach($sqls as $key => $sql){
				$percent = intval($i/count($sqls) * 100)."%";	
				db_phquery($sql['sql']);	
				
				echo '<script language="javascript">
				document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';\">'.$percent.'</div>";
				</script>';
				echo '<script language="javascript">
					document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file\"><div class=\"check_file_success\">Успешно</div>'.$sqls[$key]['success_msg'].'</div>";
					</script>';
					
				echo str_repeat(' ',1024*64);
				flush();
				usleep(100000);
				$i++;
			}
			return true;
		}
	
	

	
		
	
		function update_files(){
		
			$furl_remove = array();
			
			//index.php
			$index_input = '	require_once(DIR_FUNC.\'/tax_function.php\' );//*'; 					
			$index_output = '	require_once(DIR_FUNC.\'/tax_function.php\' );//*	
	require_once(DIR_FUNC.\'/onesteporder_functions.php\');'; 
	
			$UpdateFiles[0]['file'] = WBS_DIR."published/SC/html/scripts/index.php";
			$UpdateFiles[0]['data'] = file_get_contents($UpdateFiles[0]['file']); 
			$UpdateFiles[0]['data'] = str_replace($index_output,$index_input,$UpdateFiles[0]['data']);
			$UpdateFiles[0]['data'] = str_replace($index_input,$index_output,$UpdateFiles[0]['data']);
			$UpdateFiles[0]['msg'] = "/published/SC/html/scripts/index.php";	
			$UpdateFiles[0]['check'][0] = '	require_once(DIR_FUNC.\'/onesteporder_functions.php\');';
			
			
			
			//cart_functions.php
			$cart_functions_input1 = '			"product_code" => $cart_item["product_code"],'; 					
			$cart_functions_output1 = '			"product_code" => $cart_item["product_code"],	
			"in_stock" => (CONF_CHECKSTOCK)?$cart_item["in_stock"]:100000,
			"extra" =>  GetExtraParametrs($cart_item["productID"]),
			"configurations" =>  OneStepOrder_GetOptionsIDs(GetConfigurationByItemId($cart_item["itemID"]))'; 
	
			$cart_functions_input2 = '					"cost"		=>	show_price($costUC * $_SESSION["counts"][$j])'; 					
			$cart_functions_output2 = '					"cost"		=>	show_price($costUC * $_SESSION["counts"][$j]),
					"in_stock" => (CONF_CHECKSTOCK)?$r["in_stock"]:100000,
					"product_code" => $r["product_code"],
					"configurations" =>  OneStepOrder_GetOptionsIDs($_SESSION["configurations"][$j]),
					"extra" =>  GetExtraParametrs($_SESSION["gids"][$j])'; 
	
	
			$UpdateFiles[1]['file'] = WBS_DIR."published/SC/html/scripts/core_functions/cart_functions.php";
			$UpdateFiles[1]['data'] = file_get_contents($UpdateFiles[1]['file']); 
			$UpdateFiles[1]['data'] = str_replace($cart_functions_output1,$cart_functions_input1,$UpdateFiles[1]['data']);
			$UpdateFiles[1]['data'] = str_replace($cart_functions_output2,$cart_functions_input2,$UpdateFiles[1]['data']);
			$UpdateFiles[1]['data'] = str_replace($cart_functions_input1,$cart_functions_output1,$UpdateFiles[1]['data']);
			$UpdateFiles[1]['data'] = str_replace($cart_functions_input2,$cart_functions_output2,$UpdateFiles[1]['data']);
			$UpdateFiles[1]['msg'] = "/published/SC/html/scripts/core_functions/cart_functions.php";
		
			$UpdateFiles[1]['check'][0] = '			"in_stock" => (CONF_CHECKSTOCK)?$cart_item["in_stock"]:100000,
			"extra" =>  GetExtraParametrs($cart_item["productID"]),
			"configurations" =>  OneStepOrder_GetOptionsIDs(GetConfigurationByItemId($cart_item["itemID"]))';
			$UpdateFiles[1]['check'][1] = '					"in_stock" => (CONF_CHECKSTOCK)?$r["in_stock"]:100000,
					"product_code" => $r["product_code"],
					"configurations" =>  OneStepOrder_GetOptionsIDs($_SESSION["configurations"][$j]),
					"extra" =>  GetExtraParametrs($_SESSION["gids"][$j])';
		
		
		
		
		 //shopping_cart.php
			$shopping_cart_input = '		$smarty->assign(\'main_body_style\',\'style="\'.(((CONF_SHOPPING_CART_VIEW==2)||($cart_view==CARTVIEW_FRAME))?\'\':\'background:#FFFFFF;\').\'min-width:auto;width:auto;_width:auto;"\');'; 					
			$shopping_cart_output = '		$smarty->assign(\'main_body_style\',\'style="\'.(((CONF_SHOPPING_CART_VIEW==2)||($cart_view==CARTVIEW_FRAME))?\'\':\'background:#FFFFFF;\').\'min-width:auto;width:auto;_width:auto;"\');
		if(CONF_ONESTEPORDER_ENABLE) require_once(\'onesteporder.php\');'; 
	
			$UpdateFiles[2]['file'] = WBS_DIR."published/SC/html/scripts/modules/cart/scripts/shopping_cart.php";
			$UpdateFiles[2]['data'] = file_get_contents($UpdateFiles[2]['file']); 
			$UpdateFiles[2]['data'] = str_replace($shopping_cart_output,$shopping_cart_input,$UpdateFiles[2]['data']);
			$UpdateFiles[2]['data'] = str_replace($shopping_cart_input,$shopping_cart_output,$UpdateFiles[2]['data']);
			$UpdateFiles[2]['msg'] = "/published/SC/html/scripts/modules/cart/scripts/shopping_cart.php";
			$UpdateFiles[2]['check'][0] = '		if(CONF_ONESTEPORDER_ENABLE) require_once(\'onesteporder.php\');';
		
		
			$i = 1;	
			$error = 0;
			$returnValues = array();	
			foreach($UpdateFiles as $File){
				$percent = intval($i/count($UpdateFiles) * 100)."%";	
				$Abort = false;
				if (is_writable($File['file'])) {
					if (!$handle = fopen($File['file'], 'w')) {
						$error = '<script language="javascript">
						document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Файл не найден</div><div class=\"check_file\">'.$File['msg'].'</div>";
						</script>';
						$Abort = true;
					}
					if (fwrite($handle, $File['data']) === FALSE) {
						$error = '<script language="javascript">
						document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Ошибка записи</div><div class=\"check_file\">'.$File['msg'].'</div>";
						</script>';
						$Abort = true;
					}    
					fclose($handle);
				} else {
					$error = '<script language="javascript">
					document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Ошибка прав записи</div><div class=\"check_file\">'.$File['msg'].'</div>";
					</script>';
					$Abort = true;
				} 
				
				foreach($File['check'] as $check){
					$filecontent = file_get_contents($File['file']); 
					$pos = strpos($filecontent, $check);
					if ($pos === false) {
						$error = '<script language="javascript">
						document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Не получилось записать данные в файл.</div><div class=\"check_file\">'.$File['msg'].'</div>";
						</script>';
						$Abort = true;
						break;
					}
				}
			
				echo '<script language="javascript">
				document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';\">'.$percent.'</div>";
				</script>';
				
				if($File['msg'] && !$Abort ){
					echo '<script language="javascript">
					document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_success\">Успешно</div><div class=\"check_file\">'.$File['msg'].'</div>";
					</script>';
				}
					
				echo str_repeat(' ',1024*64);
				flush();
				usleep(500000);
				$i++;
			}
			if(!$error){
				return false;
			}else{
				echo $error;
				return true;
			}
		}
		
		function update_update_files(){

			//cart_functions.php	
			$cart_functions_input1 = '			"in_stock" => (CONF_CHECKSTOCK)?$cart_item["in_stock"]:100000,'; 					
			$cart_functions_output1 = '			"in_stock" => (CONF_CHECKSTOCK)?$cart_item["in_stock"]:100000,
			"extra" =>  GetExtraParametrs($cart_item["productID"]),
			"configurations" =>  OneStepOrder_GetOptionsIDs(GetConfigurationByItemId($cart_item["itemID"]))	'; 
	
			$cart_functions_input2 = '					"in_stock" => (CONF_CHECKSTOCK)?$r["in_stock"]:100000'; 					
			$cart_functions_output2 = '					"in_stock" => (CONF_CHECKSTOCK)?$r["in_stock"]:100000,
					"product_code" => $r["product_code"],
					"configurations" =>  OneStepOrder_GetOptionsIDs($_SESSION["configurations"][$j]),
					"extra" =>  GetExtraParametrs($_SESSION["gids"][$j])'; 
	
	
			$UpdateFiles[0]['file'] = WBS_DIR."published/SC/html/scripts/core_functions/cart_functions.php";
			$UpdateFiles[0]['data'] = file_get_contents($UpdateFiles[0]['file']); 
			
			$UpdateFiles[0]['data'] = str_replace($cart_functions_output1,$cart_functions_input1,$UpdateFiles[0]['data']);
			$UpdateFiles[0]['data'] = str_replace($cart_functions_output2,$cart_functions_input2,$UpdateFiles[0]['data']);
			$UpdateFiles[0]['data'] = str_replace($cart_functions_input1,$cart_functions_output1,$UpdateFiles[0]['data']);
			$UpdateFiles[0]['data'] = str_replace($cart_functions_input2,$cart_functions_output2,$UpdateFiles[0]['data']);
			$UpdateFiles[0]['msg'] = "/published/SC/html/scripts/core_functions/cart_functions.php";
		
			$UpdateFiles[0]['check'][0] = '			"in_stock" => (CONF_CHECKSTOCK)?$cart_item["in_stock"]:100000,
			"extra" =>  GetExtraParametrs($cart_item["productID"]),
			"configurations" =>  OneStepOrder_GetOptionsIDs(GetConfigurationByItemId($cart_item["itemID"]))';
			$UpdateFiles[0]['check'][1] = '					"in_stock" => (CONF_CHECKSTOCK)?$r["in_stock"]:100000,
					"product_code" => $r["product_code"],
					"configurations" =>  OneStepOrder_GetOptionsIDs($_SESSION["configurations"][$j]),
					"extra" =>  GetExtraParametrs($_SESSION["gids"][$j])';
		
			
			$i = 1;	
			$error = 0;
			$returnValues = array();	
			foreach($UpdateFiles as $File){
				$percent = intval($i/count($UpdateFiles) * 100)."%";	
				$Abort = false;
				if (is_writable($File['file'])) {
					if (!$handle = fopen($File['file'], 'w')) {
						$error = '<script language="javascript">
						document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Файл не найден</div><div class=\"check_file\">'.$File['msg'].'</div>";
						</script>';
						$Abort = true;
					}
					if (fwrite($handle, $File['data']) === FALSE) {
						$error = '<script language="javascript">
						document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Ошибка записи</div><div class=\"check_file\">'.$File['msg'].'</div>";
						</script>';
						$Abort = true;
					}    
					fclose($handle);
				} else {
					$error = '<script language="javascript">
					document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Ошибка прав записи</div><div class=\"check_file\">'.$File['msg'].'</div>";
					</script>';
					$Abort = true;
				} 
				
				foreach($File['check'] as $check){
					$filecontent = file_get_contents($File['file']); 
					$pos = strpos($filecontent, $check);
					if ($pos === false) {
						$error = '<script language="javascript">
						document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Не получилось записать данные в файл.</div><div class=\"check_file\">'.$File['msg'].'</div>";
						</script>';
						$Abort = true;
						break;
					}
				}
			
				echo '<script language="javascript">
				document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';\">'.$percent.'</div>";
				</script>';
				
				if($File['msg'] && !$Abort ){
					echo '<script language="javascript">
					document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_success\">Успешно</div><div class=\"check_file\">'.$File['msg'].'</div>";
					</script>';
				}
					
				echo str_repeat(' ',1024*64);
				flush();
				usleep(500000);
				$i++;
			}
			if(!$error){
				return false;
			}else{
				echo $error;
				return true;
			}
		}
		
		function delete_update_files(){
		
			$furl_remove = array();
			//index.php
			$index_input = '	require_once(DIR_FUNC.\'/tax_function.php\' );//*'; 					
			$index_output = '	require_once(DIR_FUNC.\'/tax_function.php\' );//*	
	require_once(DIR_FUNC.\'/onesteporder_functions.php\');'; 
	
			$UpdateFiles[0]['file'] = WBS_DIR."published/SC/html/scripts/index.php";
			$UpdateFiles[0]['data'] = file_get_contents($UpdateFiles[0]['file']); 
			$UpdateFiles[0]['data'] = str_replace($index_output,$index_input,$UpdateFiles[0]['data']);
			$UpdateFiles[0]['msg'] = "/published/SC/html/scripts/index.php";	
			$UpdateFiles[0]['check'][0] = '	require_once(DIR_FUNC.\'/onesteporder_functions.php\');';
			
	
			//cart_functions.php
			$cart_functions_input1 = '			"product_code" => $cart_item["product_code"],'; 					
			$cart_functions_output1 = '			"product_code" => $cart_item["product_code"],	
			"in_stock" => (CONF_CHECKSTOCK)?$cart_item["in_stock"]:100000,
			"extra" =>  GetExtraParametrs($cart_item["productID"]),
			"configurations" =>  OneStepOrder_GetOptionsIDs(GetConfigurationByItemId($cart_item["itemID"]))'; 
	
			$cart_functions_input2 = '					"cost"		=>	show_price($costUC * $_SESSION["counts"][$j])'; 					
			$cart_functions_output2 = '					"cost"		=>	show_price($costUC * $_SESSION["counts"][$j]),
					"in_stock" => (CONF_CHECKSTOCK)?$r["in_stock"]:100000,
					"product_code" => $r["product_code"],
					"configurations" =>  OneStepOrder_GetOptionsIDs($_SESSION["configurations"][$j]),
					"extra" =>  GetExtraParametrs($_SESSION["gids"][$j])'; 
	
	
			$UpdateFiles[1]['file'] = WBS_DIR."published/SC/html/scripts/core_functions/cart_functions.php";
			$UpdateFiles[1]['data'] = file_get_contents($UpdateFiles[1]['file']); 
			$UpdateFiles[1]['data'] = str_replace($cart_functions_output1,$cart_functions_input1,$UpdateFiles[1]['data']);
			$UpdateFiles[1]['data'] = str_replace($cart_functions_output2,$cart_functions_input2,$UpdateFiles[1]['data']);
			$UpdateFiles[1]['msg'] = "/published/SC/html/scripts/core_functions/cart_functions.php";
		
			$UpdateFiles[1]['check'][0] = '			"in_stock" => (CONF_CHECKSTOCK)?$cart_item["in_stock"]:100000,
			"extra" =>  GetExtraParametrs($cart_item["productID"]),
			"configurations" =>  OneStepOrder_GetOptionsIDs(GetConfigurationByItemId($cart_item["itemID"]))';
			$UpdateFiles[1]['check'][1] = '					"in_stock" => (CONF_CHECKSTOCK)?$r["in_stock"]:100000,
					"product_code" => $r["product_code"],
					"configurations" =>  OneStepOrder_GetOptionsIDs($_SESSION["configurations"][$j]),
					"extra" =>  GetExtraParametrs($_SESSION["gids"][$j])';
		
		

		 //shopping_cart.php
			$shopping_cart_input = '		$smarty->assign(\'main_body_style\',\'style="\'.(((CONF_SHOPPING_CART_VIEW==2)||($cart_view==CARTVIEW_FRAME))?\'\':\'background:#FFFFFF;\').\'min-width:auto;width:auto;_width:auto;"\');'; 					
			$shopping_cart_output = '		$smarty->assign(\'main_body_style\',\'style="\'.(((CONF_SHOPPING_CART_VIEW==2)||($cart_view==CARTVIEW_FRAME))?\'\':\'background:#FFFFFF;\').\'min-width:auto;width:auto;_width:auto;"\');
		if(CONF_ONESTEPORDER_ENABLE) require_once(\'onesteporder.php\');'; 
	
			$UpdateFiles[2]['file'] = WBS_DIR."published/SC/html/scripts/modules/cart/scripts/shopping_cart.php";
			$UpdateFiles[2]['data'] = file_get_contents($UpdateFiles[2]['file']); 
			$UpdateFiles[2]['data'] = str_replace($shopping_cart_output,$shopping_cart_input,$UpdateFiles[2]['data']);
			$UpdateFiles[2]['msg'] = "/published/SC/html/scripts/modules/cart/scripts/shopping_cart.php";
			$UpdateFiles[2]['check'][0] = '		if(CONF_ONESTEPORDER_ENABLE) require_once(\'onesteporder.php\');';
		
		
			$i = 1;	
			$error = 0;
			$returnValues = array();	
			foreach($UpdateFiles as $File){
				$percent = intval($i/count($UpdateFiles) * 100)."%";	
				
				if (is_writable($File['file'])) {
					if (!$handle = fopen($File['file'], 'w')) {
						$error = '<script language="javascript">
						document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Файл не найден</div><div class=\"check_file\">'.$File['msg'].'</div>";
						</script>';
						break;
					}
					if (fwrite($handle, $File['data']) === FALSE) {
						$error = '<script language="javascript">
						document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Ошибка записи</div><div class=\"check_file\">'.$File['msg'].'</div>";
						</script>';
						break;
					}    
					fclose($handle);
				} else {
					$error = '<script language="javascript">
					document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_problem\">Ошибка прав записи</div><div class=\"check_file\">'.$File['msg'].'</div>";
					</script>';
					break;
				} 
				
				echo '<script language="javascript">
				document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';\">'.$percent.'</div>";
				</script>';
				if($File['msg']){
					echo '<script language="javascript">
					document.getElementById("information").innerHTML=document.getElementById("information").innerHTML+"<div class=\"check_file_success\">Успешно</div><div class=\"check_file\">'.$File['msg'].'</div>";
					</script>';
				}
				echo str_repeat(' ',1024*64);
				flush();
				//sleep(1);
				usleep(500000);
				$i++;
			}
			if(!$error){
				return false;
			}else{
				echo $error;
				return true;
			}
		}
		
		
		
		
		
		function step0(){
			?>
				<h2 class="sub_title">Модуль оформление заказа в 1 шаг (Версия 2)</h2>
				<div class="block_title">
				<a href="javascript:void(0);" onclick="$('#polzovatelskoe').slideToggle();">Прочитать пользовательское соглашение</a>
				</div>
				<div class="message_info" id="polzovatelskoe" style="display:none">
				<p><b>Условия предоставления скриптов (программных продуктов).</b></p>
				<p>Настоящие Условия являются договором между вами (далее Пользователь) и «JOrange.ru» (далее, Автор). Условия относятся ко всем распространяемым версиям и модификациям программных продуктов с сайта http://www.jorange.ru.</p>
				 <ol>
					<li>Программные продукты JOrange.ru (далее, Продукты) представляют собой исходные коды программ , воспроизведенные в файлах или на бумаге, включая электронную или распечатанную документацию, а также текст данного лицензионного соглашения (далее, Соглашение).</li>
					<li>Скачивание Продуктов свидетельствует о том, что Пользователь ознакомился с содержанием Соглашения, принимает его положения и будет использовать Продукты на условиях данного Соглашения.</li>
					<li>Соглашение вступает в законную силу непосредственно в момент получения Продуктов посредством электронных средств передачи данных.</li>
					<li>Все авторские права на Продукты принадлежат Автору. Продукт в целом или по отдельности является объектом авторского права и подлежит защите согласно российскому и международному законодательству. Использование Продуктов с нарушением условий данного Соглашения, является нарушением законов об авторском праве, и будет преследоваться в соответствии с действующим законодательством.</li>
					<li>Продукты поставляются на условиях «КАК ЕСТЬ» («AS IS») без предоставления гарантий производительности, покупательной способности, сохранности данных, а также иных явно выраженных или предполагаемых гарантий. Автор не несет какой-либо ответственности за причинение или возможность причинения вреда Пользователю, его информации или бизнесу вследствие использования или невозможности использования Продуктов.</li>
					<li>Любое распространение Продукта без предварительного согласия Автора, включая некоммерческое, является нарушением данного Соглашения и влечет за собой ответственность согласно действующему законодательству. </li>
					<li>Пользователь вправе вносить любые изменения в исходный код Продуктов по своему усмотрению. При этом последующее использование Продуктов должно осуществляться в соответствии с данным Соглашением и при условии сохранения всех авторских прав. В случае внесения каких бы то ни было изменений, Автор не несет ответственности за работоспособность Продуктов.</li>
					<li>Автор не несет ответственность, в случае привлечения Пользователя к административной или уголовной ответственности за использование Продуктов в противозаконных целях.</li>
					<li>Прекращение действия данного Соглашения допускается в случае удаления всех полученных файлов и документации, а также их копий. Прекращение действия данного Соглашения не обязывает Автора возвратить средства, потраченные Пользователем на приобретение Продуктов.</li>
			  </ol>
				</div>
				<div class="message_info">	
					Всю необходимую информацию о модуле Вы можете найти на <a href="http://jorange.ru">JOrange.ru</a><br><br>
					<b>Устанавливая данный модуль Вы принимаете условия <a href="javascript:void(0);" onclick="$('#polzovatelskoe').slideToggle();">предоставления скриптов</a></b>
				</div>
				<center>
				<form method=POST>
					<div class="blue-button-small"><input type="submit" name="install_step1" value="Установить"></div>&nbsp;&nbsp;&nbsp;&nbsp;
					<div class="blue-button-small"><input type="submit" name="update_step1" value="Обновить до версии 2"></div>&nbsp;&nbsp;&nbsp;&nbsp;
					<div class="orange-button-small"><input type="submit" name="delete_step1" value="Удалить"></div>
				</form>
				</center>
			<?php
		}
		
		
		
		
		function install_step1(){
			?>
				<h2  class="sub_title">Установка модуля (Шаг 1)</h2>
				<div class="block_title">Проверка файлов:</div>
				<div class="check_files">
					<?php
						$files = array();
						$files[] = "published/SC/html/scripts/core_functions/onesteporder_functions.php";		
						$files[] = "published/SC/html/scripts/modules/cart/scripts/onesteporder.php";
						$files[] = "published/SC/html/scripts/css/onesteporder.css";
						$files[] = "published/SC/html/scripts/css/onesteporderIE.css";
						$files[] = "published/SC/html/scripts/js/onesteporder.js";
						$files[] = "published/SC/html/scripts/templates/frontend/onesteporder/main.html";
						$files[] = "published/SC/html/scripts/templates/frontend/onesteporder/contact.html";
						$files[] = "published/SC/html/scripts/templates/frontend/onesteporder/shipping.html";
						$files[] = "published/SC/html/scripts/templates/frontend/onesteporder/billing.html";
						$files[] = "published/SC/html/scripts/templates/frontend/onesteporder/footer.html";
						$error = false;
						foreach($files as $file){
							if(file_exists(WBS_DIR.$file)){
								echo "<div class=\"check_file_success\">OK</div>";
							}else{
								$error = true;
								echo "<div class=\"check_file_problem\">Файл не найден</div>";
							}
							echo "<div class=\"check_file\">{$file}</div>";
						}
					?>
				</div>
			<?php if(!$error){ ?>
				<form method=POST>
					<div class="orange-button-small"><input type="submit" name="install_step2" value="Продолжить" ></div>
				</form>
			<?php }else{ ?>
				<div class="message_error">Пожалуйста, устраните ошибки для продолжения установки.</div>
			
			<?php
			}
		}
		
		function install_step2(){
			?>
				<h2  class="sub_title">Установка модуля (Шаг 2)</h2>
				<div class="block_title">Корректировка файлов:</div>
				<div class="message_info">	
					Будут добавлены новые записи и удалены старые в файлах webasyst shop-script.<br>
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
				<form method=POST>
					<div class="orange-button-small"><input type="submit" name="install_step3" value="Продолжить" ></div>
				</form>
			<?php
		}
		
		function install_step3(){
			?>
				<h2  class="sub_title">Установка модуля (Шаг 2)</h2>
				<div class="block_title">Корректировка файлов:</div>
				<div class="message_info">	
					Идет процесс обновления файлов webasyst shop-script. Пожалуйста, дождитесь окончания процесса.<br>
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
				
				<?php  if( !update_files()){ ?>
					<form method=POST>
						<div class="orange-button-small"><input type="submit" name="install_step4" value="Продолжить" ></div>
					</form>
				<?php  }else{ ?>
					<div class="message_error">
						При возникновении проблем с корректировкой файлов для начала проверьте права на запись файлов. <br>
						Также Вы можете отредактировать данные файлы вручную, используя инструкцию на сайте.
					</div>
					<form method=POST>
						<div class="orange-button-small"><input type="submit" name="install_step4" value="Продолжить" ></div>
					</form>
			<?php
				}
		}
		
		
		function install_step4(){
			?>
				<h2  class="sub_title">Установка модуля (Шаг 3)</h2>
				<div class="block_title">Записи в БД:</div>
				<div class="message_info">	
					Будут внесены новые записи в таблицы SC_settings и SC_local. Это записи с настройками модуля и перевод строчек.<br>
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
				<form method=POST>
					<div class="orange-button-small"><input type="submit" name="install_step5" value="Продолжить" ></div>
				</form>
			<?php
		}
		
		function install_step5(){
			?>
				<h2  class="sub_title">Установка модуля (Шаг 3)</h2>
				<div class="block_title">Записи в БД:</div>
				<div class="message_info">	
					Идет процесс добавления записей в таблицы SC_settings и SC_local. Пожалуйста, дождитесь окончания процесса.<br>
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
				<?php  if( sql()){ ?>
					<script>$(function() { goToByScroll("install_step6"); })</script>
					<form method=POST>
						<div class="orange-button-small" id="install_step6"><input type="submit" name="install_step6" value="Завершить установку" ></div>
						<br>
						<br>
					</form>
				<?php  }else{ ?>
					<div class="message_error">Пожалуйста, устраните ошибки для продолжения установки.</div>
			<?php
			}
		}

		function install_step6(){
			?>
				<h2  class="sub_title">Установка модуля (Завершение)</h2>
				<div class="block_title">Настройка и завершение.</div>
				<div class="message_info">	
					<b>Шаг 1.</b><br>
					Зайдите в <a href="/login">админ панель</a> в раздел "Дизайн" -> "Языки и перевод" -> Выберите ваш язык и нажмите "Редактировать перевод". 
					Опуститесь в самый низ и нажмите "сохранить".
					<br><br>
					<b>Шаг 2.</b><br>
					Зайдите в <a href="/login">админ панель</a> в раздел "Настройки" -> "Настройки" -> "Корзина и заказы", и проставьте необходимые настройки.
					<br><br>
					<b>Шаг 3.</b><br>
					Удалите файл установки:<br>
					install.php.
					<br><br>
					<b>Подключение Яндекс.Быстрый заказ.</b><br>
					Для подключения "Яндекс.Быстрый заказ" перейдите по ссылке <a href="http://partner.market.yandex.ru/delivery-registration.xml">http://partner.market.yandex.ru/delivery-registration.xml</a> и зарегистрируйте свой магазин в базе данных
					Яндекс, прописав в поле "Адрес страницы перенаправления" путь <b>http://Ваш домен/cart/</b> (http://Ваш домен?ukey=cart для маазинов без ЧПУ).
					<br><br>
					<br><br>
					<b>Внимание!</b> В файле шаблона <u>\published\SC\html\scripts\templates\frontend\onesteporder\main.html</u> встроен фреймворк JQuery.
					Если у вас уже подключен он, то удалите строчку в вышеупомянутом файле.<br>
					&lt;script src=&quot;http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js&quot;&gt;&lt;/script&gt;
					<br><br>
					<b>Внимание!</b> Если Вы будете использовать "информер" в качестве завершающего диалогового окна, то 
					в файле шаблона <u>\published\SC\html\scripts\templates\frontend\shopping_cart_info.html</u> 
					удалите строчку class="{$checkout_class}" в ссылке.
					<br><br>
				</div>

				<?php  
					clean_Cache();
				?>
					
			<?php
			
		}
		
		
		
		
		
		
		
		function update_step1(){
			?>
				<h2  class="sub_title">Обновление модуля до версии 2</h2>
				<div class="block_title">Подтверждение обновления:</div>
				<div class="message_info">	
					Обновления модуля состоит из добавления нескольких записей в базу данных.<br>
				</div>
				<form method=POST>
					<div class="orange-button-small"><input type="submit" name="update_step2" value="Продолжить" ></div>
				</form>
			<?php
			
		}
	
		function update_step2(){
				?>
					<h2  class="sub_title">Обновление модуля (Шаг 2)</h2>
					<div class="block_title">Корректировка файлов:</div>
					<div class="message_info">	
						Будут добавлены новые записи и удалены старые в файлах webasyst shop-script.<br>
					</div>
					<div id="progress" >
						<div style="width:0px;">&nbsp;</div>
					</div>
					<div id="information"  class="check_files"></div>	
					<form method=POST>
						<div class="orange-button-small"><input type="submit" name="update_step3" value="Продолжить" ></div>
					</form>
				<?php
			}
			
			function update_step3(){
				?>
					<h2  class="sub_title">Обновление модуля (Шаг 2)</h2>
					<div class="block_title">Корректировка файлов:</div>
					<div class="message_info">	
						Будут добавлены новые записи и удалены старые в файлах webasyst shop-script.<br>
					</div>
					<div id="progress" >
						<div style="width:0px;">&nbsp;</div>
					</div>
					<div id="information"  class="check_files"></div>	
					
					<?php  if( !update_update_files()){ ?>
						<form method=POST>
							<div class="orange-button-small"><input type="submit" name="update_step4" value="Продолжить" ></div>
						</form>
					<?php  }else{ ?>
						<div class="message_error">Пожалуйста, устраните ошибки для продолжения установки.</div>
					
				<?php
					}
			}
			
		function update_step4(){
			?>
				<h2  class="sub_title">Обновление модуля (Шаг 3)</h2>
				<div class="block_title">Добавление записей в Базу данных:</div>
				<div class="message_info">	
					Будут добавлены несколько новых записей в таблицу перевода и таблицу настроек.
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
			
				<form method=POST>
					<div class="orange-button-small"><input type="submit" name="update_step5" value="Продолжить" ></div>
				</form>
				
			<?php
				
		}
		function update_step5(){
			?>
				<h2  class="sub_title">Обновление модуля (Шаг 3)</h2>
				<div class="block_title">Добавление записей в Базу данных:</div>
				<div class="message_info">	
					Будут добавлены несколько новых записей в таблицу перевода и таблицу настроек.
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
			
				<?php  if( update_sql()){ ?>
					<script>$(function() { goToByScroll("update_step6"); })</script>
					<form method=POST>
						<div class="orange-button-small" id="update_step6"><input type="submit" name="update_step6" value="Завершить удаление" ></div><br><br>
					</form>
				<?php  }else{ ?>
					<div class="message_error">Пожалуйста, устраните ошибки для продолжения установки.</div>
				
			<?php
				}
		}	
		function update_step6(){
			?>
				<h2  class="sub_title">Обновление модуля (Завершение)</h2>
				<div class="block_title">Модуль успешно обновлен.</div>
				<div class="message_info">	
					<b>Шаг 1.</b><br>
					Зайдите в <a href="/login">админ панель</a> в раздел "Дизайн" -> "Языки и перевод" -> Выберите ваш язык и нажмите "Редактировать перевод". 
					Опуститесь в самый низ и нажмите "сохранить".
					<br><br>
					<b>Шаг 2.</b><br>
					Зайдите в <a href="/login">админ панель</a> в раздел "Настройки" -> "Настройки" -> "Корзина и заказы", и проставьте необходимые настройки.
					<br><br>
					<b>Шаг 3.</b><br>
					Удалите файл установки:<br>
					install.php.
					<br><br>
					<br><br>
					<b>Внимание!</b> В файле шаблона <u>\published\SC\html\scripts\templates\frontend\onesteporder\main.html</u> встроен фреймворк JQuery.
					Если у вас уже подключен он, то удалите строчку в вышеупомянутом файле.<br>
					&lt;script src=&quot;http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js&quot;&gt;&lt;/script&gt;
					<br><br>
					<b>Внимание!</b> Если Вы будите использовать "информер" в качестве завершающего диалогового окна, то 
					в файле шаблона <u>\published\SC\html\scripts\templates\frontend\shopping_cart_info.html</u> 
					удалите строчку class="{$checkout_class}" в ссылке.
					<br><br>
				</div>

				<?php  
					clean_Cache();
				?>
					
			<?php
			
		}
		
		
		
		
		
		
		function delete_step1(){
			?>
				<h2  class="sub_title">Удаление модуля</h2>
				<div class="block_title">Подтверждение удаления:</div>
				<div class="message_info">	
					Удаление модуля состоит из 3 этапов.<br>
					1 Шаг - Удаление файлов модуля.<br>
					2 Шаг - Удаление корректировок в файлах webasyst shop-script.<br>
					3 Шаг - Удаление записей и таблиц в БД.<br>
				</div>
				<form method=POST>
					<div class="orange-button-small"><input type="submit" name="delete_step2" value="Продолжить" ></div>
				</form>
			<?php
			
		}
	
		function delete_step2(){
			?>
				<h2  class="sub_title">Удаление модуля (Шаг 1)</h2>
				<div class="block_title">Удаление файлов модуля:</div>
				<div class="check_files">
					<?php
						cleanDirectory('published/SC/html/scripts/templates/frontend/onesteporder');
						rmdir('published/SC/html/scripts/templates/frontend/onesteporder');
						cleanDirectory('published/SC/html/scripts/images/onesteporder');
						rmdir('published/SC/html/scripts/images/onesteporder');
						$files = array();
						$files[] = "published/SC/html/scripts/core_functions/onesteporder_functions.php";		
						$files[] = "published/SC/html/scripts/modules/cart/scripts/onesteporder.php";
						$files[] = "published/SC/html/scripts/css/onesteporder.css";
						$files[] = "published/SC/html/scripts/css/onesteporderIE.css";
						$files[] = "published/SC/html/scripts/js/onesteporder.js";
						
						$error = false;
						foreach($files as $file){
							if(is_dir(WBS_DIR.$file)){
								rmdir(WBS_DIR.$file);
							}else{
								@unlink(WBS_DIR.$file);
							}
							if(file_exists(WBS_DIR.$file)){
								echo "<div class=\"check_file_problem\">Файл не удален</div>";
								$error = true;
							}else{	
								echo "<div class=\"check_file_success\">OK</div>";
							}
							echo "<div class=\"check_file\">{$file}</div>";
						}
					?>
				</div>
			<?php if(!$error){ ?>
				<form method=POST>
					<div class="orange-button-small"><input type="submit" name="delete_step3" value="Продолжить" ></div>
				</form>
			<?php }else{ ?>
				<div class="message_error">Пожалуйста, устраните ошибки для продолжения установки.</div>
			
			<?php
			}
		}

		function delete_step3(){
			?>
				<h2  class="sub_title">Удаление модуля (Шаг 2)</h2>
				<div class="block_title">Удаление корректировок:</div>
				<div class="message_info">	
					Будут удалены внесенные записи в файлах webasyst shop-script
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
				<form method=POST>
					<div class="orange-button-small"><input type="submit" name="delete_step4" value="Продолжить" ></div>
				</form>
			<?php
			
		}
		function delete_step4(){
			?>
				<h2  class="sub_title">Удаление модуля (Шаг 2)</h2>
				<div class="block_title">Удаление корректировок:</div>
				<div class="message_info">	
					Будут удалены внесенные записи в файлах webasyst shop-script
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
				
				<?php  if( !delete_update_files()){ ?>
					<form method=POST>
						<div class="orange-button-small"><input type="submit" name="delete_step5" value="Продолжить" ></div>
					</form>
				<?php  }else{ ?>
					<div class="message_error">Пожалуйста, устраните ошибки для продолжения установки.</div>
				
			<?php
				}
		}
		

		function delete_step5(){
			?>
				<h2  class="sub_title">Удаление модуля (Шаг 3)</h2>
				<div class="block_title">Удаление записей и таблиц в БД:</div>
				<div class="message_info">	
					Будут удалены все записи относящиеся к модулю.
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
			
				<form method=POST>
					<div class="orange-button-small"><input type="submit" name="delete_step6" value="Продолжить" ></div>
				</form>
				
			<?php
				
		}
		function delete_step6(){
			?>
				<h2  class="sub_title">Удаление модуля (Шаг 3)</h2>
				<div class="block_title">Удаление записей и таблиц в БД:</div>
				<div class="message_info">	
					Будут удалены все записи и таблицы относящиеся к модулю.
				</div>
				<div id="progress" >
					<div style="width:0px;">&nbsp;</div>
				</div>
				<div id="information"  class="check_files"></div>	
			
				<?php  if( delete_sql()){ ?>
					<script>$(function() { goToByScroll("delete_step7"); })</script>
					<form method=POST>
						<div class="blue-button-small" id="delete_step7"><input type="submit" name="delete_step7" value="Завершить удаление" ></div>
						<br>
						<br>
					</form>
				<?php  }else{ ?>
					<div class="message_error">Пожалуйста, устраните ошибки для продолжения установки.</div>
				
			<?php
				}
		}	
		function delete_step7(){
			?>
				<h2  class="sub_title">Удаление модуля (Завершение)</h2>
				<div class="block_title">Модуль успешно удален</div>
				<div class="message_info">	
					Все файлы и записи были успешно удалены из Вашего магазина.<br><br>
					Удалите файл установки:<br>
					<b>install.php</b>
				</div>

				<?php  
					clean_Cache();
				?>
					
			<?php
			
		}
	
	
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
	<html lang="ru">
	<head>
		<title>Модуль Ajax оформление заказа в 1 шаг + Яндекс.Быстрый заказ - JOrange.ru</title>
		<link rel="stylesheet" type="text/css" href="http://www.jorange.ru/templates/jorange/user/install.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script>
		function goToByScroll(id){
			$('html,body').animate({scrollTop: $("#"+id).offset().top},'slow');
		}
		</script>
	</head>
	<body>
		<div class="divGreyBlackTop"></div>
		<div class="divGreyBlackTop2"></div>
		<div class="iefix"><div class="mainWidth">
		<?php
			if(isset($_POST['install_step1'])){
				install_step1();
			}else if(isset($_POST['install_step2'])){
				install_step2();
			}else if(isset($_POST['install_step3'])){
				install_step3();
			}else if(isset($_POST['install_step4'])){
				install_step4();
			}else if(isset($_POST['install_step5'])){
				install_step5();
			}else if(isset($_POST['install_step6'])){
				install_step6();
			}else if(isset($_POST['delete_step1'])){
				delete_step1();
			}else if(isset($_POST['delete_step2'])){
				delete_step2();
			}else if(isset($_POST['delete_step3'])){
				delete_step3();
			}else if(isset($_POST['delete_step4'])){
				delete_step4();
			}else if(isset($_POST['delete_step5'])){
				delete_step5();
			}else if(isset($_POST['delete_step6'])){
				delete_step6();
			}else if(isset($_POST['delete_step7'])){
				delete_step7();
			}else if(isset($_POST['update_step1'])){
				update_step1();
			}else if(isset($_POST['update_step2'])){
				update_step2();
			}else if(isset($_POST['update_step3'])){
				update_step3();
			}else if(isset($_POST['update_step4'])){
				update_step4();
			}else if(isset($_POST['update_step5'])){
				update_step5();
			}else if(isset($_POST['update_step6'])){
				update_step6();
			}else{
				step0();
			}
	
		?>
		</div></div>
	</body>
	</html>
	<?php
}
?>