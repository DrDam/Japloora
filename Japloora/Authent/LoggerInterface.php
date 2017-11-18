<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Japloora\Authent;

/**
 * Description of LoggerInterface
 *
 * @author drdam
 */
interface LoggerInterface
{
    public function log($array);
    static public function getId();
}
