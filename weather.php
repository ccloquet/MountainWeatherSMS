<?php

  // ----------------------------------------------------------------------------------------------------------------
  // Quickly & dirtily written by C. Cloquet (Poppy), 2017
  // Licence : MIT
  // ----------------------------------------------------------------------------------------------------------------

  // ----------------------------------------------------------------------------------------------------------------
  // retrieves data from weather underground
  // hourly forecasts
  // needs the anvil plan
  // developer : 500 queries/day free
  //
  //
  // Below, YOUR_WEATHER_UNDERGROUND_API_KEY should be replaced by an api key you get on wunderground.com/weather/api/
  // ----------------------------------------------------------------------------------------------------------------

  // ----------------------------------------------------------------------------------------------------------------
  // if the pressure stays the same from one hour to the second, does not display the pressure at hour 2
  // same for weather condition
  // ----------------------------------------------------------------------------------------------------------------

  // ----------------------------------------------------------------------------------------------------------------
  // this script can be used as a webhook in a Twilio SMS number (www.twilio.com)
  // ----------------------------------------------------------------------------------------------------------------

  // ----------------------------------------------------------------------------------------------------------------
  // the user should then send an SMS to the Twilio number, with the following message :
  // meteo,CITY,COUNTRY
  //
  // eg : meteo,Brussels,Belgium
  //
  // or, when the city is in France, the user can omit the country
  //   
  // eg : meteo,Chambéry
  //
  // alternatively, the user can make the query in his browser : 
  //
  // https://www.myprettydomain.com/weather.php?Body=meteo,Grenoble
  // ----------------------------------------------------------------------------------------------------------------
  
  $key     = YOUR_WEATHER_UNDERGROUND_API_KEY;

  $payload = $_REQUEST['Body']; // meteo,city,country (country = France by default)
  $epl     = explode(',', $payload);

  if ( (sizeof($epl) != 2) & (sizeof($epl) != 3) )  
  {
	die('');
  }
  if (strtolower(trim($epl[0])) != 'meteo') 
  {
	die('');
  }

  $city    = trim($epl[1]);
  $country = 'France';
  if (sizeof($epl > 2)) 
  {
	$country = trim($epl[2]);
  }

  $json_string = file_get_contents("https://api.wunderground.com/api/".$key."/api/hourly10day/q/".$country."/".$city.".json");
  $pj          = json_decode($json_string, true);
  $pjhf        = $pj['hourly_forecast'];

  $MY_EOL      = PHP_EOL;				// FOR HTML
  if (isset($_GET['Body'])) $MY_EOL = "<br>";		// Detects that the query comes from Twilio -> SMS need PHP_EOL

  $old_mday = 'A';
  $old_cond = "A";
  $old_hpa  = 0;

  echo '<?xml version="1.0" encoding="UTF-8"?><Response><Message>';

  for ($i=0; $i<min(sizeof($pjhf), 48); ++$i)
  { 
    $q = $pjhf[$i];
    $t = $q['FCTTIME'];
    if ($old_mday != $t['mday_padded']) echo $MY_EOL . $t['mday_padded'] . '/' . $t['mon'] . ' T %Prec %Hu m/s hPa' . $MY_EOL;
    $old_mday = $t['mday_padded'];

    $new_hpa = '';
    if ($old_hpa != $q['mslp']['metric']) $new_hpa = $q['mslp']['metric'];
    $old_hpa = $q['mslp']['metric'];

    echo $t['hour_padded']. 'h ' . $q['temp']['metric'] . ' '. $q['pop'] . ' ' . $q['humidity'] . ' ' . $q['wspd']['metric'] . ' ' . $new_hpa;

    if ($old_cond != $q['condition']) echo ' ' . $q['condition'];
    $old_cond = $q['condition'];

    echo $MY_EOL;
  }

  echo $MY_EOL.'data: weatherunderground.com. NO WARRANTY OF ANY KIND.';

  echo '</Message></Response>';
?>
