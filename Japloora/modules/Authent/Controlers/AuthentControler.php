<?php

namespace Japloora;

use Japloora\ControlerBase;
use Japloora\AuthentNegociation;

class AuthentControler extends ControlerBase {

  static function defineRoutes() {
    return array(
      array(
        'path' => 'authent',
        'scheme' => ['http', 'https'],
        'method' => 'GET',
        'parameters' => array(
          'Login',
          'Pass',
        ),
        'callback' => 'GenerateToken',
        'format' => 'JSON'
      ),
    );
  }

  public function GenerateToken($params) {

    $pass = $params['Query']['Pass'];
    $login = $params['Query']['Login'];

    $token_data = AuthentNegociation::authentifie($login, $pass);

    // JSON OutPut need JSON Encodable Variable
    return array('datas' => ["login" => $login, "token" => $token_data['token'], 'expiration' => $token_data['expiration']]);
  }

}
