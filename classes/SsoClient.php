<?php
namespace Classes;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;
/** Copiar a cada aplicación; requiere firebase/php-jwt y ext-curl. */
final class SsoClient {
 public static function start(): void {
  $state=bin2hex(random_bytes(32));
  $_SESSION['portal_state']=$state; $_SESSION['portal_state_time']=time();
  header('Location: '.rtrim($_ENV['SSO_PORTAL_URL'],'/').'/authorize?'.http_build_query([
   'client_id'=>$_ENV['SSO_CLIENT_ID'],'state'=>$state])); exit;
 }
 public static function consume(): object {
  $expected=$_SESSION['portal_state'] ?? '';
  $created=$_SESSION['portal_state_time'] ?? 0;
  unset($_SESSION['portal_state'],$_SESSION['portal_state_time']);
  if (!$expected || !hash_equals($expected,(string)($_GET['state'] ?? '')) || time()-$created>600 ||
      !preg_match('/^[a-f0-9]{64}$/',(string)($_GET['code'] ?? ''))) throw new RuntimeException('Acceso SSO inválido.');
  $curl=curl_init(rtrim($_ENV['SSO_BACKCHANNEL_URL'] ?? $_ENV['SSO_PORTAL_URL'],'/').'/token');
  curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,
   CURLOPT_HTTPHEADER=>['Host: '.parse_url($_ENV['SSO_PORTAL_URL'],PHP_URL_HOST)],
   CURLOPT_FOLLOWLOCATION=>false,CURLOPT_POSTFIELDS=>http_build_query([
    'client_id'=>$_ENV['SSO_CLIENT_ID'],'client_secret'=>$_ENV['SSO_CLIENT_SECRET'],'code'=>$_GET['code']])]);
  $body=curl_exec($curl); $status=curl_getinfo($curl,CURLINFO_HTTP_CODE); $transportError=curl_errno($curl); curl_close($curl);
  $data=json_decode((string)$body,true);
  if ($status!==200 || !isset($data['access_token'])) {
   error_log('SSO Agroflorsa canje_http='.(int)$status.' curl='.(int)$transportError);
   throw new RuntimeException('No fue posible validar el acceso.', 1002);
  }
  $key=file_get_contents($_ENV['SSO_PUBLIC_KEY']);
  $c=JWT::decode($data['access_token'],new Key($key,'RS256'));
  if (($c->iss ?? null)!==$_ENV['SSO_ISSUER'] || ($c->aud ?? null)!==$_ENV['SSO_CLIENT_ID'] ||
      !is_string($c->sub ?? null) || !ctype_digit($c->sub) ||
      !isset($c->exp,$c->iat,$c->nbf,$c->jti) || $c->exp<=time() || $c->exp-$c->iat>300) throw new RuntimeException('JWT inválido.');
  session_regenerate_id(true);
  $_SESSION['portal_subject']=$c->sub;
  $_SESSION['portal_expires']=$c->exp;
  $_SESSION['portal_refresh']=$data['refresh_token'] ?? null;
  $_SESSION['portal_checked']=time();
  return $c;
 }
 public static function renew():void {
  if(empty($_SESSION['portal_refresh']))return;
  if(time()-(int)($_SESSION['portal_checked']??0)<60 && ($_SESSION['portal_expires']??0)>time()+30)return;
  $curl=curl_init(rtrim($_ENV['SSO_BACKCHANNEL_URL']??$_ENV['SSO_PORTAL_URL'],'/').'/refresh');
  curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,
   CURLOPT_HTTPHEADER=>['Host: '.parse_url($_ENV['SSO_PORTAL_URL'],PHP_URL_HOST)],
   CURLOPT_POSTFIELDS=>http_build_query(['client_id'=>$_ENV['SSO_CLIENT_ID'],'client_secret'=>$_ENV['SSO_CLIENT_SECRET'],'refresh_token'=>$_SESSION['portal_refresh']])]);
  $body=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_HTTP_CODE);curl_close($curl);
  if($status===401 || $status===403){unset($_SESSION['portal_refresh']);$_SESSION['portal_expires']=0;return;}
  if($status!==200)throw new RuntimeException('Renovación temporalmente no disponible.');
  $data=json_decode($body,true);
  $c=JWT::decode($data['access_token']??'',new Key(file_get_contents($_ENV['SSO_PUBLIC_KEY']),'RS256'));
  if(($c->iss??null)!==$_ENV['SSO_ISSUER'] || ($c->aud??null)!==$_ENV['SSO_CLIENT_ID'] || ($c->sub??null)!==$_SESSION['portal_subject'] || !isset($c->iat,$c->nbf,$c->exp) || $c->exp<=time() || $c->exp-$c->iat>300)throw new RuntimeException('Renovación inválida.');
  $_SESSION['portal_expires']=$c->exp;$_SESSION['portal_checked']=time();
 }
}
