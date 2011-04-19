<?php

/*
  Plugin Name: Comment-spam
  Description: Simple spam check via stopforumspam.com. *PLEASE NOTE*, stopforumspam has a limit of 20,000 calls to its API per day. If you're getting 20,000 comments a day (god help you), don't use this plugin or     they'll think you're DoSing them. Requires cURL access.
  Author: Mark Watkinson
  Version: 1.0
  Author URI: http://asgaard.co.uk
  License: WTFPL <http://en.wikipedia.org/wiki/WTFPL>
*/
abstract class comment_spam {

  static function init() {
    add_filter('pre_comment_approved', 'comment_spam::filter');
  }

  /**
   * Queries the stopforumspam API for the given IP
   */
  private static function query($ip) {
    $url = 'http://www.stopforumspam.com/api?ip=' . $ip;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response_text = curl_exec($ch);
    curl_close($ch);
    return $response_text;
  }

  static function filter($approved) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $response = self::query($ip);
    
    // we can either approve, unapprove (ie flag for human review), or spam it.
    // this is a goto situation, we'll use exceptions to simulate the goto
    // logic. The catch block at the end is the unapprove label, and the
    // spam-case is nested inside some checks.

    $approved = 1;
    try {
      if ($response === false) throw new Exception();
      $xml = simplexml_load_string($response);
      if ($xml === false) throw new Exception();
      if (!property_exists($xml, 'appears')) throw new Exception();
      if (!strcasecmp($xml->appears, 'yes')) {
        if (!property_exists($xml, 'lastseen')) throw new Exception();
        $m;
        // retrieve the date from the response. Don't catch leading zeros.
        // capture each element as a subgroup
        if (!preg_match('/0*(\d+)-0*(\d+)-0*(\d+)\s+0*(\d+):0*(\d+):0*(\d+)/',
          $xml->lastseen, $m)) throw new Exception();
        // now coerce them all into ints
        foreach($m as &$match) $match = (int)$match;
        list($null, $year, $month, $day, $hour, $minute, $second) = $m;
        // finally!
        $most_recent = mktime($hour, $minute, $second, $month, $day, $year);
        // don't autospam it if it's been a few months since it was last seen,
        // it could be a dynamic ip
        if ($most_recent < time() - 60*60*24*90) throw new Exception();
        else $approved = 'spam';
      }
    } catch (Exception $e) {
      // label unapprove
      $approved = 0;
    }
    return $approved;
  }

}

add_action('init', 'comment_spam::init');

