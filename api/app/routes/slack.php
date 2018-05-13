<?php
use Illuminate\Database\Capsule\Manager as DB;
$app->group('/slack', function () {
  $this->post('', function ($request, $response, $args) {
    $formData = $request->getParsedBody();
    $json = urldecode($formData['payload']);
    $data = json_decode($json,true);
    $responseArr = '';
    
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
      $signIn = FrcPortal\MeetingHour::where('user_id',$user_id)->where('hours_id',$hours_id)->whereNull('time_out')->get();
      if(!is_null($signIn)) {
        $signIn->time_out = $time;
        if($signIn->save()) {
          $responseArr = 'You signed out at '.date('M d, Y H:i A', strtotime($time));
        }
      }
    }
    $response = $response->withJson($responseArr);
    return $response;
  });
});

















?>
