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
if($version >= '2.13.6') {
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

//Create User Category Tables
/*
require_once('UserCategory.php');
require_once('UserUserCategory.php');
*/


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
