<?php
// Most of the work here contributed by geeks4hire (Ben Horan)
// Skyper decode by Andy Taylor (MW0MWZ)

include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// Function to reverse the ROT1 used for Skyper
function un_skyper($s, $n = -1) {
  $output = "";
  $messageTextArray = str_split($message);
  $skyperRIC = ord($messageTextArray[0]) - 31;
  unset($messageTextArray[0]);

  if (count($messageTextArray) >= 1) {
    $skyperSlot = ord($messageTextArray[1]) - 32;
    unset($messageTextArray[1]);
    foreach($messageTextArray as $asciiChar) {
    $asciiAsInt = ord($asciiChar);
    $convretedAsciiAsInt = $asciiAsInt -1;
    $convertedAsciiChar = chr($convretedAsciiAsInt);
    $output .= $convertedAsciiChar;
  }
    $output = "Skyper RIC: $skyperRIC / Skyper Slot: $skyperSlot <br>".$output;
    return $output;
  } else {
    $output = "Skyper RIC: $skyperRIC / No Message";
    return $output;
  }
}
?>
<b><?php echo $lang['pocsag_list'];?></b>
<table>
  <tr>
    <th><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['pocsag_timeslot'];?><span><b>Message Mode</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['target'];?><span><b>RIC / CapCode of the receiving Pager</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['pocsag_msg'];?><span><b>Message contents</b></span></a></th>
  </tr>

<?php
  foreach ($logLinesDAPNETGateway as $dapnetMessageLine) {
      $dapnetMessageArr = explode(" ", $dapnetMessageLine);
      $dapnetMessageTxtArr = explode('"', $dapnetMessageLine);
      $utc_time = $dapnetMessageArr["0"]." ".substr($dapnetMessageArr["1"],0,-4);
      $utc_tz =  new DateTimeZone('UTC');
      $local_tz = new DateTimeZone(date_default_timezone_get ());
      $dt = new DateTime($utc_time, $utc_tz);
      $dt->setTimeZone($local_tz);
      $local_time = $dt->format('H:i:s M jS');
      $pocsag_timeslot = $dapnetMessageArr["6"];
      $pocsag_ric = str_replace(',', '', $dapnetMessageArr["8"]);
      $pocsag_msg = $dapnetMessageTxtArr["1"];
    
      // Decode Skyper Messages
      if ($pocsag_ric == "0004520") {
        $pocsag_msg = un_skyper($pocsag_msg);
      } 
   
      // Formatting long messages without spaces
      if (strpos($pocsag_msg, ' ') == 0 && strlen($pocsag_msg) >= 45) {
        $pocsag_msg = wordwrap($pocsag_msg, 45, ' ', true);
      }
?>

  <tr>
    <td style="width: 140px; vertical-align: top; text-align: left;"><?php echo $local_time; ?></td>
    <td style="width: 70px; vertical-align: top; text-align: center;"><?php echo "Slot ".$pocsag_timeslot; ?></td>
    <td style="width: 90px; vertical-align: top; text-align: center;"><?php echo $pocsag_ric; ?></td>
    <td style="width: max-content; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal !important;"><?php echo $pocsag_msg; ?></td>
  </tr>

<?php
  } // foreach
?>

</table>
