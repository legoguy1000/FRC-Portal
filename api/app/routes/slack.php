<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/slack', function () {
  $this->post('', function ($request, $response, $args) {
    $formData = $request->getParsedBody();
    $json = urldecode($formData['payload']);
    $data = json_decode($json,true);
    $responseStr = '';

    if($data['callback_id'] == 'sign_out' && $data['actions'][0]['name'] == 'sign_out') {
      $responseArr = 'Something went wrong.  We were unable to sign you out.';
      $answer = explode('-',$data['actions'][0]['value']);
      $user_id = $answer[0];
      $hours_id = $answer[1];
      $time = date('Y-m-d').' 18:00:00';
      if(date('N') <= 5) {
        $time = date('Y-m-d').' 21:00:00';
      } else {
          $time = date('Y-m-d').' 18:00:00';
      }
      $signIn = FrcPortal\MeetingHour::where('user_id',$user_id)->where('hours_id',$hours_id)->whereNull('time_out')->first();
      if(!is_null($signIn)) {
        $signIn->time_out = $time;
        if($signIn->save()) {
          $responseStr = 'You signed out at '.date('M d, Y H:i A', strtotime($time));
        }
      }
    } else if($data['callback_id'] == 'remote_sign_in' && $data['actions'][0]['name'] == 'approve') {
      $responseArr = 'Something went wrong.  We were unable to sign you out.';
      $answer = explode('-',$data['actions'][0]['value']);
      $user_id = $answer[0];
      $time = $answer[1];
      $signIn = FrcPortal\MeetingHour::where('user_id',$user->user_id)->whereNotNull('time_in')->whereNull('time_out')->first();
      if(is_null($signIn)) {
        $hours = FrcPortal\MeetingHour::create(['user_id' => $user_id, 'time_in' => date('Y-m-d H:i:s',$time)]);
      }

    }

    $response->getBody()->write($responseStr);
    return $response;
  });
  $this->post('/myHours', function ($request, $response, $args) {
    $formData = $request->getParsedBody();
    $responseStr = '';
    $token = $formData['token'];
    $slack_id = $formData['user_id'];
    $user_name = $formData['user_name'];

    $user = FrcPortal\User::where('slack_id',$slack_id)->first();
    if(!is_null($user)) {
      $season = FrcPortal\Season::where('year',date('Y'))->first();
      if(!is_null($season)) {
    	   $annualReq = FrcPortal\AnnualRequirement::where('season_id',$season->season_id)->where('user_id',$user->user_id)->first();
         $responseStr = $annualReq->total_hours;
      }
    }  else {
    $responseStr = 'I don\'t know who you are.  Please check your portal profile to verify your Slack user ID is set.';
    }
    $response->getBody()->write($responseStr);
    return $response;
  });
  $this->post('/myHours', function ($request, $response, $args) {
    $formData = $request->getParsedBody();
    $responseStr = '';
    $token = $formData['token'];
    $slack_id = $formData['user_id'];
    $user_name = $formData['user_name'];
    if(!empty($formData['text']) && filter_var($formData['text'], FILTER_VALIDATE_EMAIL)) {
      $email = $formData['text'];
      $user = FrcPortal\User::where('email',$email)->orWhere('team_email',$email)->first();
      if(!empty($user)) {
        $user->slack_id = $user_id;
        if($user->save()) {
          $responseStr = 'Slack ID added to profile.';
        } else {
          $responseStr = 'Something went wrong adding slack ID to profile.';
        }
      } else {
        $responseStr = 'I don\'t know who you are. The email "'.$email.'" doesn\t link to known user. Please check your portal profile to verify your email is set.';
    } else {
      $responseStr = 'Invalid email provided.';
    }
    $response->getBody()->write($responseStr);
    return $response;
  });
  /*$this->post('/signin', function ($request, $response, $args) {
    $formData = $request->getParsedBody();
    $responseStr = '';
    $token = $formData['token'];
    $slack_id = $formData['user_id'];
    $user_name = $formData['user_name'];

    $user = FrcPortal\User::where('slack_id',$slack_id)->first();
    if(!is_null($user)) {
      $signIn = FrcPortal\MeetingHour::where('user_id',$user->user_id)->whereNotNull('time_in')->whereNull('time_out')->first();
      if(is_null($signIn)) {
        $time = time();
        //Send question to mentors channel
      } else {
        $responseStr = 'You are already signed in.  Last sign in at '.date('M d, Y H:i A', strtotime($signIn->time_in )).'. Please sign out first before signing in again.';
      }
    }
    $response->getBody()->write($responseStr);
    return $response;
  });*/
});

















?>
