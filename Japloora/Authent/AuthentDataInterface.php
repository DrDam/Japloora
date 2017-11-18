<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Japloora\Authent;

/**
 * Description of AuthentDataInterface
 *
 * @author drdam
 */
interface AuthentDataInterface
{
    public static function connexion();

    public function getUser($user_id, $withPass = false);

    public function getAllUsers();
    
    public function getUserByToken($token);
    
    public function updateUser($user);

    public function deleteUser($user_id);
    
    public function getUserByLogin($login);
    
    public function createUser($user);
    
}
