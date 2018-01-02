<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 01/01/18
 * Time: 18:39
 */

namespace AppBundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle' ;
    }
}
