<?php
  /**
   * Canvas API cURL Class
   *
   * This class was built specifically for use with the Instructure Canvas RESST
   * API.
   *
   * PHP version >= 5.2.0
   *
   * @author Christopher Esbrandt <cesbrandt@ecpi.edu>
   */
  class Curl {
    public $curl;
    public $get;
    public $put;
    private $token;
    private $baseURL;
    private $initCurl;
    private $restartCurl;
    private $closeCurl;
    private $setOpt;
    private $setURLData;
    private $urlPath;
    private $callAPI;
    private $exec;

    /**
     * Contructor function
     *
     * @param $base_url
     */
    public function __construct($token, $domain) {
      if(is_null($token)) {
        throw new \ErrorException('No admin token supplied.');
      }
      if(is_null($domain)) {
        throw new \ErrorException('No domain supplied.');
      }
      $this->token = $token;
      $this->baseURL = 'https://' . $domain . '/api/v1';
      $this->initCurl();
    }

    /**
     * Initialize a cURL call
     */
    private function initCurl() {
      $this->curl = curl_init();
      $this->setOpt(CURLOPT_RETURNTRANSFER, true);
      $this->setOpt(CURLOPT_HEADER, true);
      $this->setOpt(CURLOPT_HTTPHEADER, array('Content-Type: application/json', $this->token));
    }

    /**
     * Restart cURL for multiple calls
     */
    private function restartCurl() {
      $this->closeCurl();
      $this->initCurl();
    }

    /**
     * Close cURL after all calls have been made
     */
    public function closeCurl() {
      curl_close($this->curl);
    }

    /**
     * Execute cURL function
     *
     * @return array
     */
    private function exec($url = NULL) {
      if(!is_null($url)) {
        $this->setURLData($url);
      }
      $results = curl_exec($this->curl);
      $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
      $header = substr($results, 0, $headerSize);
      $results = json_decode(substr($results, $headerSize));
      $this->restartCurl();
      return array($header, $results);
    }

    /**
     * Calls exec() for each page of the API results
     *
     * @return array
     */
    private function callAPI() {
      $currRegex = '/\bpage=\K(\d+\b)(?=[^>]*>; rel="current")/';
      $lastRegex = '/\bpage=\K(\d+\b)(?=[^>]*>; rel="last")/';
      $results = array();
      $call = $this->exec();
      if(substr($call[0], 0, 12) != 'HTTP/1.1 302' && substr($call[0], 0, 12) != 'HTTP/1.1 404') {
        if(is_array($call[1])) {
          foreach($call[1] as $result) {
            array_push($results, $result);
          }
        } else {
          array_push($results, $call[1]);
        }
        preg_match($currRegex, $call[0], $current);
        preg_match($lastRegex, $call[0], $last);
      }
      if(sizeof($current) !== 0) {
        while($current[0] !== $last[0]) {
          $newUrl = $this->baseURL . $this->urlPath . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . ($current[0] + 1);         
          $call = $this->exec($newUrl);
          if(substr($call[0], 0, 12) != 'HTTP/1.1 302' && substr($call[0], 0, 12) != 'HTTP/1.1 404') {
            if(is_array($call[1])) {
              foreach($call[1] as $result) {
                array_push($results, $result);
              }
            } else {
              array_push($results, $call[1]);
            }
            preg_match($currRegex, $call[0], $current);
            preg_match($lastRegex, $call[0], $last);
          }
        }
      }
      return $results;
    }

    /**
     * POST function
     *
     * @param $url, $data
     *
     * @return array
     */
    public function post($url, $data = NULL) {
      if(is_null($data)) {
        throw new \ErrorException('No data supplied.');
      }
      $this->setURLData($url, json_encode($data));
      $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
      return $this->callAPI();
    }

    /**
     * PUT function
     *
     * @param $url, $data
     *
     * @return array
     */
    public function put($url, $data = NULL) {
      if(is_null($data)) {
        throw new \ErrorException('No data supplied.');
      }
      $this->setURLData($url, json_encode($data));
      $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
      return $this->callAPI();
    }

    /**
     * GET function
     *
     * @param $url, $data
     *
     * @return array
     */
    public function get($url, $data = NULL) {
      $this->setURLData($url . (!is_null($data) ? (((strpos($url, '?') !== false) ? '&' : '?') . http_build_query($data)) : ''));
      $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
      return $this->callAPI();
    }

    /**
     * Set the target URL and supplied data function
     *
     * @param $url
     * @param $data
     */
    private function setURLData($url, $data = NULL) {
      if(is_null($url)) {
        throw new \ErrorException('No target URL supplied.');
      }
      $this->urlPath = $url;
      $this->setOpt(CURLOPT_URL, $this->baseURL . $this->urlPath . ((strpos($url, '?') !== false) ? '&' : '?') . 'per_page=100');
      if(!is_null($data)) {
        $this->setOpt(CURLOPT_POSTFIELDS, $data);
      }
    }

    /**
     * Set cURL Options function
     *
     * @param $option
     * @param $value
     */
    private function setOpt($options, $value = null) {
      if(is_array($options)) {
        foreach($options as $option => $value) {
          curl_setopt($this->curl, $option, $value);
        }
      } else {
        curl_setopt($this->curl, $options, $value);
      }
    }
  }
?>