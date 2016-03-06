<?php
require_once('config.php');

/**
 * This class provides all of the functionality needed to encode and
 * decode shortened URLs.
 */

class ShortUrl
{
  /**
   * @var string the characters used in building the short URL 
   */
  protected static $chars = "123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";

  /**
   * @var string holds the name of the database table to use 
   */
  protected static $table = DB_TABLENAME;

  /**
   * @var object holds a reference to a PDO object
   */
  protected $pdo;
	
  /**
   * Constructor
   */
  public function __construct() {
    $this->pdo = $this->connection();
  }
	
	/**
	 * Connect to the database
	 */
	public function connection() {
	  $pdo = new PDO(DB_SERVER . ":host=" . DB_HOST . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
	  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
	  return $pdo;
	}

  /** 
   * Performs validation of the URLs format, connects to the URL to make sure it exists, 
   * and checking the database to see if the URL is already there.
   * 
   * @param string $url the long URL to be shortened
   * @return string the short string on success
   */    
  public function urlToShortString($url) {
    if ($this->validateUrlFormat($url) == false) {
      throw new \Exception("URL does not have a valid format.");
    }

    if (!$this->verifyUrlExists($url)) {
      throw new \Exception("URL does not appear to exist.");
    }

    $shortString = $this->urlExistsInDb($url);
    if ($shortString == false) {
      $shortString = $this->createShortUrl($url);
    }

    return $shortString;
  }

  /**
   * Create a short string from a long URL.
   * 
   * @param string $url the long URL
   * @return string the created short string
   */
  protected function createShortUrl($url) {
    $params = array(
      "long_url" => $url,
    );
    // Inserts a new row into the database.
    $query = "INSERT INTO " . self::$table . " (long_url) " . " VALUES (:long_url)";
    $stmnt = $this->pdo->prepare($query);
    $stmnt->execute($params);
    $id = $this->pdo->lastInsertId();
 
    // encode the autoincremented id
    $shortString = $this->convertIntToShortString($id);    
    $this->insertShortStringInDb($id, $shortString);
    return $shortString;
  }
 
  /**
   * Retrieve a long URL from a short string.
	 *
   * @param string $string the short string associated with a long URL
   * @return string the long URL
   */
  public function shortStringToUrl($string) {
    $urlRow = $this->getUrlFromDb($string);
    if (empty($urlRow)) {
      throw new \Exception("Short String does not appear to exist.");
    }

    return $urlRow["long_url"];
  }

  /**
   * Check to see if the supplied URL is a valid format
   * 
   * @param string $url the long URL
   * @return boolean whether URL is a valid format
   */
  protected function validateUrlFormat($url) {
    return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
  }

  /* Check to see if the URL exists
   * 
   * Uses CURL to access the URL and make sure a 404 error is not returned.
   * 
   * @param string $url the long URL
   * @return boolean whether the URL does not return a 404 code
   */
  protected function verifyUrlExists($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return (!empty($response) && $response != 404);
  }

  /**
   * Check the database for the long URL.
   *  
   * @param string $url the long URL
   * @return string|boolean the short url if it exists - false if it does not
   */
  protected function urlExistsInDb($url) {
	  $params = array(
      "long_url" => $url
    );
    $query = "SELECT short_url FROM " . self::$table .
      " WHERE long_url = :long_url LIMIT 1";
    $stmt = $this->pdo->prepare($query);
    $stmt->execute($params);

    $result = $stmt->fetch();
    return (empty($result)) ? false : $result["short_url"];
  }

  /**
   * Converts the autoincreamented id to a short string.
   * 
   * This method does the actual conversion of the ID integer to a short string.
   * If successful, it returns the created string. If there is an error, an
   * exception is thrown.
   * 
   * @param int $id the integer to be converted
   * @return string the created short string
   */
	protected function convertIntToShortString($id, $base=62) {
	  $id = intval($id);
    if ($id < 1) {
      throw new \Exception("The ID is not a valid integer");
    }
	  $str = '';
	  do {
		$i = $id % $base;
		$str = self::$chars[$i] . $str;
		$id = ($id - $i) / $base;
	  } while($id > 0);
		
	  return $str;
	}

  /**
   * Updates the database row with the short string. If
   * successful, true is returned. An exception is thrown if there is an 
   * error.
   * 
   * @param int $id the ID of the database row to update
   * @param string $string the short string to associate with the row
   * @return boolean on success
   */
  protected function insertShortStringInDb($id, $string) {
	  $params = array(
      "short_url" => $string,
      "id" => $id,
    );
    $query = "UPDATE " . self::$table .
        " SET short_url = :short_url WHERE id = :id";
    $stmnt = $this->pdo->prepare($query);
    $stmnt->execute($params);

    if ($stmnt->rowCount() < 1) {
      throw new \Exception("Row was not updated with short string.");
    }

    return true;
  }

  /**
   * Get the long URL from the database.
   * 
   * @param string $string the short string to look for in the database
   * @return string|boolean the long URL or false if it does not exist
   */
  protected function getUrlFromDb($string) {
    $params=array(
      "short_url" => $string,
    );
    $query = "SELECT id, long_url FROM " . self::$table .
        " WHERE short_url = :short_url LIMIT 1";
    $stmt = $this->pdo->prepare($query);
    $stmt->execute($params);  

    $result = $stmt->fetch();
    return (empty($result)) ? false : $result;
  }

}
