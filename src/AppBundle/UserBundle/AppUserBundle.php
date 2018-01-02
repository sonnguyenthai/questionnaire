<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 01/01/18
 * Time: 18:39
 */

namespace AppBundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AppUserBundle
 * @package AppBundle\UserBundle
 */
class AppUserBundle extends Bundle
{
    /**
     * @return string
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
