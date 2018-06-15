<?php

include_once(__DIR__.'/../includes.php');

use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::schema()->create('settings', function ($table) {
     $table->char('setting_id',13)->primary();
     $table->string('section');
     $table->string('setting');
     $table->text('value');
 });
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'other', 'setting' => 'google_api_key'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'other', 'setting' => 'google_calendar_id'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'other', 'setting' => 'google_drive_id'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'other', 'setting' => 'timezone'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'other', 'setting' => 'school_month_end'], ['value' => 'June']);

 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'facebook_oauth_client_secret'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'microsoft_oauth_client_secret'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'google_oauth_client_secret'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'google_oauth_client_id'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'facebook_oauth_client_id'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'microsoft_oauth_client_id'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'local_login_enable'], ['value' => '1']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'google_login_enable'], ['value' => '0']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'facebook_login_enable'], ['value' => '0']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'microsoft_login_enable'], ['value' => '0']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'require_team_email'], ['value' => '0']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'jwt_key'], ['value' => hash('sha512',bin2hex(random_bytes(64)))]);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'login', 'setting' => 'jwt_signin_key'], ['value' => hash('sha512',bin2hex(random_bytes(64)))]);

 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'team', 'setting' => 'team_name'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'team', 'setting' => 'team_number'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'team', 'setting' => 'env_url'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'team', 'setting' => 'team_logo_url'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'team', 'setting' => 'team_domain'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'team', 'setting' => 'team_color_primary'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'team', 'setting' => 'team_color_secondary'], ['value' => '']);

 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'notification', 'setting' => 'slack_enable'], ['value' => '0']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'notification', 'setting' => 'email_enable'], ['value' => '0']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'notification', 'setting' => 'slack_url'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'notification', 'setting' => 'slack_api_token'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'notification', 'setting' => 'slack_team_id'], ['value' => '']);
 $setting = FrcPortal\Setting::updateOrCreate(['section' => 'notification', 'setting' => 'notification_email'], ['value' => '']);

?>
