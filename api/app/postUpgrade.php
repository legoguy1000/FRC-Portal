<?php
require_once(__DIR__.'/includes.php');
use Illuminate\Database\Capsule\Manager as Capsule;
$version = VERSION;

//always update composer after pull
updateComposer();

/**
* 2.6.0
**/
if($version >= '2.6.0') {
  //Create Event Types Table
  if(Capsule::schema()->hasColumn('events','type')) {
    try {
      Capsule::schema()->table('events', function ($table) {
        $table->string('type')->nullable()->default(null)->change();
      });
    } catch (Exception $e) {
      //Exception will be logged in Monolog
    }
  }
  if(!Capsule::schema()->hasTable('event_types')) {
    require_once('database/EventType.php');
    try {
      Capsule::schema()->table('events', function ($table) {
        $table->foreign('type')->references('type')->on('event_types')->onDelete('set null')->onUpdate('cascade');
      });
    } catch (Exception $e) {
      //Exception will be logged in Monolog
    }
  }
}

/**
* 2.7.0
**/
if($version >= '2.7.0') {
  //Change Column Name
  if(Capsule::schema()->hasTable('events') && Capsule::schema()->hasColumn('events','time_slots') && !Capsule::schema()->hasColumn('events','time_slots_required')) {
    //Backup Database
    exportDB();
    try {
      Capsule::schema()->table('events', function($table) {
        $table->renameColumn('time_slots', 'time_slots_required');
      });
    } catch (Exception $e) {
      //Exception will be logged in Monolog
    }
  }
}

/**
* 2.10.0
**/
if($version >= '2.10.0') {
  if(!Capsule::schema()->hasTable('event_food')) {
    require_once('database/EventFood.php');
    try {
      Capsule::schema()->table('event_food', function ($table) {
        $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade')->onUpdate('cascade');
      });
    } catch (Exception $e) {
      //Exception will be logged in Monolog
    }
  }
  if(!Capsule::schema()->hasTable('event_food_event_requirements')) {
    require_once('database/EventFoodUser.php');
    try {
      Capsule::schema()->table('event_food_event_requirements', function ($table) {
        $table->foreign('food_id')->references('food_id')->on('event_food')->onDelete('cascade')->onUpdate('cascade');
        $table->foreign('ereq_id')->references('ereq_id')->on('event_requirements')->onDelete('cascade')->onUpdate('cascade');
      });
    } catch (Exception $e) {
      //Exception will be logged in Monolog
    }
  }
  if(Capsule::schema()->hasTable('seasons') && !Capsule::schema()->hasColumn('seasons','hour_requirement_week')) {
    try {
      Capsule::schema()->table('seasons', function ($table) {
        $table->integer('hour_requirement_week')->after('hour_requirement');
      });
    } catch (Exception $e) {
      //Exception will be logged in Monolog
    }
  }
}

/**
* 2.11.0
**/
if($version >= '2.11.0') {
  if(Capsule::schema()->hasTable('seasons')) {
    if(!Capsule::schema()->hasColumn('seasons','membership_form_map')) {
      try {
        Capsule::schema()->table('seasons', function ($table) {
          $table->text('membership_form_map')->after('join_spreadsheet');
        });
      } catch (Exception $e) {
        //Exception will be logged in Monolog
      }
    }
    if(!Capsule::schema()->hasColumn('seasons','membership_form_sheet')) {
      try {
        Capsule::schema()->table('seasons', function ($table) {
          $table->string('membership_form_sheet')->after('membership_form_map')->nullable()->default('Form Responses 1');
        });
      } catch (Exception $e) {
        //Exception will be logged in Monolog
      }
    }
  }
  $set = FrcPortal\Setting::where('setting','jwt_key')->orWhere('setting','jwt_signin_key')->update(['section' => 'jwt']);
}

/**
* 2.11.2
**/
if($version >= '2.11.2') {
  if(Capsule::schema()->hasTable('settings')) {
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'cronjob', 'setting' => 'enable_cronjob-changeUserStatus'], ['value' => false]);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'cronjob', 'setting' => 'enable_cronjob-importSlackProfiles'], ['value' => false]);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'cronjob', 'setting' => 'enable_cronjob-pollMembershipFormResponses'], ['value' => false]);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'cronjob', 'setting' => 'enable_cronjob-updateEventsFromGoogle'], ['value' => false]);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'cronjob', 'setting' => 'enable_cronjob-tooLong'], ['value' => false]);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'cronjob', 'setting' => 'enable_cronjob-endOfDayHoursToSlack'], ['value' => false]);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'team', 'setting' => 'location'], ['value' => '']);
  }
}

/**
* 2.13.0
**/
if($version >= '2.13.7') {
  if(Capsule::schema()->hasTable('meeting_hours')) {
    if(!Capsule::schema()->hasColumn('meeting_hours','created_at') && !Capsule::schema()->hasColumn('meeting_hours','updated_at')) {
      try {
        Capsule::schema()->table('meeting_hours', function ($table) {
          $table->timestamps();
        });
      } catch (Exception $e) {
        //Exception will be logged in Monolog
      }
    }
  }
  if(!Capsule::schema()->hasTable('logs')) {
    require_once('database/Logs.php');
    Capsule::schema()->table('logs', function ($table) {
      $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
    });
  }
  if(Capsule::schema()->hasTable('settings')) {
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'team', 'setting' => 'google_form_url'], ['value' => '']);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'other', 'setting' => 'membership_form_name'], ['value' => '']);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'other', 'setting' => 'google_analytics_id'], ['value' => '']);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'login', 'setting' => 'amazon_login_enable'], ['value' => false]);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'login', 'setting' => 'amazon_oauth_client_id'], ['value' => '']);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'login', 'setting' => 'amazon_oauth_client_secret'], ['value' => '']);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'login', 'setting' => 'github_login_enable'], ['value' => false]);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'login', 'setting' => 'github_oauth_client_id'], ['value' => '']);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'login', 'setting' => 'github_oauth_client_secret'], ['value' => '']);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'login', 'setting' => 'require_team_email'], ['value' => '0']);
  }
  if(Capsule::schema()->hasTable('annual_requirements')) {
    if(!Capsule::schema()->hasColumn('annual_requirements','stims_date')) {
      try {
        Capsule::schema()->table('annual_requirements', function ($table) {
          $table->dateTime('stims_date')->after('stims');
        });
      } catch (Exception $e) {
        //Exception will be logged in Monolog
      }
    }
    if(!Capsule::schema()->hasColumn('annual_requirements','dues_date')) {
      try {
        Capsule::schema()->table('annual_requirements', function ($table) {
          $table->dateTime('dues_date')->after('dues');
        });
      } catch (Exception $e) {
        //Exception will be logged in Monolog
      }
    }
  }
}

/**
* 2.15.0
**/
if($version >= '2.14.2') {
  //create Admin Account
  if(file_exists(__DIR__.'/secured/config.ini')) {
    $iniData = parse_ini_file(__DIR__.'/secured/config.ini', true);
    if(is_null($iniData['admin']['admin_user']) || $iniData['admin']['admin_user'] == '' || is_null($iniData['admin']['admin_pass']) || $iniData['admin']['admin_pass'] == '') {
      $admin_data = array();
      $admin_data['admin_user'] = 'admin';
      $password = bin2hex(openssl_random_pseudo_bytes(10));
      $admin_data['admin_pass'] = hash('sha512',$password);
      $iniData['admin'] = $admin_data;
      write_ini_file($iniData, __DIR__.'/secured/config.ini', true);
    }
    if(is_null($iniData['encryption']) || is_null($iniData['encryption']['encryption_key'])) {
      $enc_data = array();
      $enc_data['encryption_key'] = bin2hex(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
      $iniData['encryption'] = $enc_data;
      write_ini_file($iniData, __DIR__.'/secured/config.ini', true);
    }
  }
  if(Capsule::schema()->hasTable('seasons')) {
    if(Capsule::schema()->hasColumn('seasons','bag_day')) {
      try {
        Capsule::schema()->table('seasons', function ($table) {
          $table->string('bag_day')->nullable()->default(null)->change();
        });
      } catch (Exception $e) {
        //Exception will be logged in Monolog
      }
    }
  }
  if(Capsule::schema()->hasTable('users')) {
    if(Capsule::schema()->hasColumn('users','password')) {
      try {
        Capsule::schema()->table('users', function ($table) {
          $table->dropColumn('password');
        });
      } catch (Exception $e) {
        //Exception will be logged in Monolog
      }
    }
  }
  if(Capsule::schema()->hasTable('settings')) {
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'team', 'setting' => 'enable_team_emails'], ['value' => false]);
    $file = __DIR__.'/secured/service_account_credentials.json';
    $setting = FrcPortal\Setting::where('section', 'service_account')->where('setting','google_service_account_data')->first();
  	if(file_exists($file) && $setting->value == '') {
      $client_email = '';
      $json_encypt = '';
  		$json = file_get_contents($file);
      $file_data = json_decode($json);
      $client_email = $file_data->client_email;
      $json_encypt = encryptItems($json);
      $data = $client_email.','.$json_encypt;
      $setting = FrcPortal\Setting::firstOrCreate(['section' => 'service_account', 'setting' => 'google_service_account_data'], ['value' => $data]);
  	}
    $settings = FrcPortal\Setting::where('section', 'login')->where('setting','like','%oauth_client_secret')->get();
    foreach($settings as $secret) {
      if($secret->value != '') {
        $secret->value = encryptItems($secret->value);
      }
      $secret->section = 'oauth';
      $secret->save();
    }
    $settings = FrcPortal\Setting::where('section', 'login')->where('setting','like','%oauth_client_id')->get();
    foreach($settings as $client_id) {
      $client_id->section = 'oauth';
      $client_id->save();
    }
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'login', 'setting' => 'yahoo_login_enable'], ['value' => false]);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'oauth', 'setting' => 'yahoo_oauth_client_id'], ['value' => '']);
    $setting = FrcPortal\Setting::firstOrCreate(['section' => 'oauth', 'setting' => 'yahoo_oauth_client_secret'], ['value' => '']);
  }
}


/*
//Add Column
if(Capsule::schema()->hasTable('events') && !Capsule::schema()->hasColumn('events','payment_amount')) {
  //Backup Database
  exportDB();
  try {
    Capsule::schema()->table('events', function($table) {
      $table->decimal('payment_amount', 5, 2)->default(00.00);
    });
  } catch (Exception $e) { }
}
//Add Column
if(Capsule::schema()->hasTable('events') && !Capsule::schema()->hasColumn('events','hotel_info')) {
  //Backup Database
  exportDB();
  try {
    Capsule::schema()->table('events', function($table) {
    $table->text('hotel_info');
    });
  } catch (Exception $e) { }
} */
?>
