<?php

namespace Drupal\geocode_lookup;

use GuzzleHttp\Client;

/**
 * Class Lookup.
 *
 * @package Drupal\geocode_lookup
 */
class Lookup implements LookupInterface {

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \GuzzleHttp\Client
   */
  protected $guzzleClient;

  /**
   * Address string sent to google api.
   *
   * @var string
   *
   */
  protected $googleAddressString;

  protected $lat;
  protected $long;

  /**
   * Constructor.
   */
  public function __construct(Client $http_client) {
    $this->guzzleClient = $http_client;
    $this->googleApiKey = \Drupal::config('geolocation.settings')->get('google_map_api_key');
  }

  /**
   * Pulls Address fields into a formatted string for Google lookup.
   *
   * This will need changing for different states, below works for NZ.
   */
  public function setAddress(array $address_array) {

    $string = '';

    // Take the bits of the address we need and make them
    // into a string for google.
    $aa = $address_array[0];

    $string = (!empty($aa['address_line1'])) ? $aa['address_line1'] . ', ' : '';
    $string .= (!empty($aa['address_line2'])) ? $aa['address_line2'] . ', ' : '';
    $string .= (!empty($aa['dependent_locality'])) ? $aa['dependent_locality'] . ', ' : '';
    $string .= (!empty($aa['locality'])) ? $aa['locality'] . ', ' : '';
    $string .= (!empty($aa['postal_code'])) ? $aa['postal_code'] . ', ' : '';

    $string = str_replace(" ", "+", $string);
    $this->resetIfNewAddress($string);
  }

  public function resetIfNewAddress(string $newAddress) {
    // Unset lat & lng if this is a new address to ensure lookup is performed.
    if ($this->googleAddressString !== $newAddress) {
      $this->googleAddressString = $newAddress;
      $this->lat = NULL;
      $this->long = NULL;
    }
  }

  /**
   * Returns the address string in a useful format for lookup on Google.
   *
   * @return string
   *   Google friendly string for lookup
   */
  public function getGoogleString() {
    return $this->googleAddressString;
  }

  /**
   * Returns latitude for object, calls lookup if $long is not available.
   *
   * @return string
   *   latitude
   */
  public function getLat() {
    if (!is_numeric($this->lat)) {
      $this->performLookup();
    }
    return $this->lat;
  }

  /**
   * Returns longitude for object, calls lookup if $long is not available.
   *
   * @return string
   *   longitude
   */
  public function getLong() {
    if (!is_numeric($this->long)) {
      $this->performLookup();
    }
    return $this->long;
  }

  /**
   * Looks up data on Google and sets lat & long for obj.
   *
   * Is the google string set?
   * Use Guzzle to get the data from google and then set data as reqd.
   */
  private function performLookup() {

    $remote_base = 'https://maps.googleapis.com/maps/api/geocode/json';

    if (strlen($this->getGoogleString())) {
      // If there's no address then don't bother, we want it to be blank.
      $query = [
        'address' => $this->getGoogleString(),
        'key' => $this->googleApiKey,
      ];

      try {
        $response = $this->guzzleClient->request('GET', $remote_base, ['query' => $query]);
        if ('200' == $response->getStatusCode()) {
          $ret = json_decode($response->getBody());
          $this->lat = $ret->results[0]->geometry->location->lat;
          $this->long = $ret->results[0]->geometry->location->lng;
        }
      }
      catch (RequestException $e) {
        // Log or ignore? In this case it will be apparent if the lookups fail
        // and that's okay, so don't even log it.
      }
    }
    else {
      $this->lat = NULL;
      $this->long = NULL;
    }
  }

}
