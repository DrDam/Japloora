<?php

namespace Japloora\Authent\Controler;

use Japloora\ControlerBase;

use Japloora\Authent\AuthentFactory;

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

    $authentDB = AuthentFactory::connexion();
      
    $pass = $params['Query']['Pass'];
    $login = $params['Query']['Login'];

    $userId = $authentDB->authentifie($login, $pass);
    if($userId === FALSE) {
        core_403();
    }
    
    $token_data = $authentDB->generateToken($userId);
   
    // JSON OutPut need JSON Encodable Variable
    return array('datas' => ["token" => $token_data['token'], 'expiration' => $token_data['expiration']]);
  }

}
