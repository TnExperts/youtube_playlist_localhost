<?php

/**
 *
 */
class httpServicesAPI
{
  protected $curl=null;
  protected $cookiejar_filename='Curl';
  protected $header=[];
  protected $active_cookies;


  function __construct()
  {

  }

  public function do_set_options()
  {
    $this->curl=isset($this->curl)?$this->curl:curl_init();
    curl_setopt($this->curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36');
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($this->curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($this->curl, CURLOPT_FAILONERROR, false);
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
  }

  public function renew_cookies()
  {
    $cookies=$this->cookiejar_filename.time();
    $this->active_cookies=$cookies;
    curl_setopt($this->curl, CURLOPT_COOKIEJAR, __DIR__.'/cookies/'.$cookies );
    curl_setopt($this->curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies/'.$cookies );
  }

  public function set_cookies($cookies)
  {
    $this->active_cookies=$cookies;
    curl_setopt($this->curl, CURLOPT_COOKIEJAR, __DIR__.'/cookies/'.$cookies );
    curl_setopt($this->curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies/'.$cookies );
  }

  public function close()
  {
    if(isset($this->curl)) curl_close($this->curl);
    $this->curl=NULL;
    return $this;
  }

  public function execute()
  {
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER,1);
    $bla=curl_exec($this->curl);
    return $bla;
  }

  public function debug()
  {
    return [
      'info'=>curl_getinfo($this->curl),
      'EFFECTIVE_URL'=>curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL),
      'HEADER_OUT'=>curl_getinfo($this->curl, CURLINFO_HEADER_OUT),
      'error'=>[
        'no'=>curl_errno($this->curl),
        'txt'=>curl_error($this->curl)
        ]
      ];
  }

  public function post($url, $params = [], $redirectLoc=false)
  {
    $req_param = json_encode($params);
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_POST, 1);
    curl_setopt($this->curl, CURLOPT_HEADER, false);
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->header);
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $req_param);
    $resp=$this->execute();
    return $redirectLoc===false?$resp:curl_getinfo($this->curl,CURLINFO_EFFECTIVE_URL);
  }

  public function get($url, $redirectLoc=false)
  {
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->header);
    curl_setopt($this->curl, CURLOPT_URL, $url);
    return $redirectLoc===false?$this->execute():$this->debug();
  }
}
