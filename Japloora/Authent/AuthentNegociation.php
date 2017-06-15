<?php
/**
 * Created by PhpStorm.
 * User: damien_robert
 * Date: 14/06/2017
 * Time: 10:38
 */

namespace Japloora\Authent;


class AuthentNegociation {

  /**
   * @param $login
   * @param $pass
   * @return array
   */
  public static function authentifie($login, $pass) {

    // Check login/password
    // if check ok

    // Generate Token
    $token_data = self::generateToken($login, $pass);
    return $token_data;
  }

  /**
   * @param $login
   * @param $token
   * @return bool
   */
  public static function checkToken($login, $token) {

    // Get User in DB
    $pass = "machin";

    $valid_token = self::generateToken($login, $pass);

    return ($token === $valid_token['token']);
  }

  /**
   * @param $login
   * @param $pass
   * @return array
   */
  private static function generateToken($login, $pass) {

    $md5 = md5($pass);
    $salt = floor(time() / 1000);
    $string = $login . $md5 . $salt;
    $token_data = ['token' => md5($string), 'expiration' => ($salt+1) * 1000] ;
    return $token_data;
  }

}