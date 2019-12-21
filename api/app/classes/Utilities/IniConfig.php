<?php
namespace FrcPortal\Utilities;

use Illuminate\Database\Eloquent\Model as Eloquent;


class IniConfig {
  /**
  * @var string
  */
  public static $iniFile = __DIR__ . '/../../secured/config.ini';
  /**
  * @var null
  */
  protected static $iniData = NULL;

  public static function setIniFile($file) {
    $info = pathinfo($file);
    if($info['extension'] == 'ini' && file_exists($file)) {
      self::$iniFile = $file;
      return true;
    }
    return false;
  }

  public static function parseIniFile() {
    self::$iniData = (object) parse_ini_file(self::$iniFile);
  }

  public static function iniData() {
    return self::$iniData;
  }

  public static function iniDataProperty($prop) {
    if(isset(self::$iniData->$prop)) {
  		return self::$iniData->$prop;
  	}
  	return null;
  }
}
