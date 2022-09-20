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
$telebot = $_GET['telebot'];

/*===[CC Info Validation]=====================================*/
if($telebot == ""){
    exit();
}
Tikol4LifeAnimation('<b>OppaTikolero Bot</b>%0ATest Complete, You may now continue checking cards with our checker.%0A%0A<a href="https://tikol4life.azurewebsites.net/">Tikol4Life Checker</a>');

/*===[PHP Functions]==========================================*/
function Tikol4LifeAnimation($message){
    $url = $GLOBALS['token_url']."/sendMessage?chat_id=".$GLOBALS['telebot']."&text=".$message."&parse_mode=HTML";
    file_get_contents($url); 
}
?>