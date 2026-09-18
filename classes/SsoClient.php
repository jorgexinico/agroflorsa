<?php
namespace Classes;
use RuntimeException;
/** Identidad obtenida directamente de Login por canje autenticado del código. */
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
  if (!is_string($expected) || $expected==='' || !is_string($_GET['state'] ?? null) ||
      !hash_equals($expected,$_GET['state']) || !is_int($created) || $created>time() || time()-$created>600 ||
      !is_string($_GET['code'] ?? null) || !preg_match('/^[a-f0-9]{64}$/',$_GET['code'])) throw new RuntimeException('Acceso SSO inválido.',1001);
  $endpoint=rtrim($_ENV['SSO_BACKCHANNEL_URL'] ?? $_ENV['SSO_PORTAL_URL'],'/');
  $parts=parse_url($endpoint);
  $portalHost=parse_url($_ENV['SSO_PORTAL_URL'],PHP_URL_HOST);
  $local=static fn($host)=>is_string($host) && ($host==='localhost' || $host==='127.0.0.1' || str_ends_with($host,'.localhost'));
  if (($parts['scheme'] ?? '')!=='https' && !(($parts['scheme'] ?? '')==='http' && $local($parts['host'] ?? '') && $local($portalHost))) {
   throw new RuntimeException('El canje de identidad requiere HTTPS.',1003);
  }
  $curl=curl_init($endpoint.'/token');
  curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,
   CURLOPT_HTTPHEADER=>['Host: '.parse_url($_ENV['SSO_PORTAL_URL'],PHP_URL_HOST)],
   CURLOPT_FOLLOWLOCATION=>false,CURLOPT_POSTFIELDS=>http_build_query([
    'client_id'=>$_ENV['SSO_CLIENT_ID'],'client_secret'=>$_ENV['SSO_CLIENT_SECRET'],'code'=>$_GET['code'],
    'response_format'=>'identity'])]);
  $body=curl_exec($curl); $status=curl_getinfo($curl,CURLINFO_HTTP_CODE); $transportError=curl_errno($curl); curl_close($curl);
  $data=json_decode((string)$body,true);
  if ($status!==200 || !is_array($data) || !is_array($data['identity'] ?? null)) {
   error_log('SSO Agroflorsa canje_http='.(int)$status.' curl='.(int)$transportError);
   if ($status===200 && is_array($data) && isset($data['access_token']) && !isset($data['identity'])) {
    throw new RuntimeException('Login devolvió JWT en lugar de identity.',1004);
   }
   throw new RuntimeException('No fue posible validar el acceso.', 1002);
  }
  $c=(object)$data['identity'];
  if (($c->iss ?? null)!==$_ENV['SSO_ISSUER'] || ($c->aud ?? null)!==$_ENV['SSO_CLIENT_ID'] ||
      !is_string($c->sub ?? null) || !preg_match('/^[1-9][0-9]{0,63}$/D',$c->sub)) throw new RuntimeException('Identidad de Login inválida.',1005);
  return $c;
 }
}
