<?php
/**/
class NewsletterCurlHelper
{
  /*
    Call this method to return a singleton
  */
  public static function Instance()
  {
    static $inst = null;
    if ($inst === null) {
      $inst = new NewsletterCurlHelper();
    }
    return $inst;
  }
  /**
   * callCurl - Makes curl call
   *
   * @param string $opcall - Operation call, e.g. https://example-api.net/api-action
   * @param string postData - JSON Data to post (optional based on call)
   * @param string encodedAuth - Encoded Authentication
   */
  public function callCurl($opcall = null, $postData = null, $encodedAuth = null)
  {
    $result = null;
    if ($encodedAuth && $opcall) {
      $curl_options = [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_HTTPHEADER => array('Authorization: Basic ' . $encodedAuth, 'Accept:application/json')
      ];
      if ($postData) {
        $curl_options[CURLOPT_POST] = true;
        $curl_options[CURLOPT_POSTFIELDS] = $postData;
        array_push($curl_options[CURLOPT_HTTPHEADER], 'Content-Type:application/json');
      }
      $ch = curl_init($opcall);
      curl_setopt_array($ch, $curl_options);
      $result = json_decode(curl_exec($ch));
      curl_close($ch);
    }
    return $result;
  }
}
