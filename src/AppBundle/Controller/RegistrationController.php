<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 29/12/17
 * Time: 20:23
 */

namespace AppBundle\Controller ;

use AppBundle\Form\UserType ;
use AppBundle\Entity\User ;
use Symfony\Bundle\FrameworkBundle\Controller\Controller ;
use Symfony\Component\HttpFoundation\Request ;
use Symfony\Component\Routing\Annotation\Route ;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface ;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction ( Request $request , UserPasswordEncoderInterface $passwordEncoder )
    {
        // 1) build the form
        $user = new User ();
        $form = $this -> createForm ( UserType :: class , $user );
        $user->setEnabled(true);

        // 2) handle the submit (will only happen on POST)
        $form -> handleRequest ( $request );
        if ( $form -> isSubmitted () && $form -> isValid ()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder -> encodePassword ( $user , $user -> getPlainPassword ());
            $user -> setPassword ( $password );

            // 4) save the User!
            $em = $this -> getDoctrine () -> getManager ();
            $em -> persist ( $user );
            $em -> flush ();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            //return $this -> redirectToRoute ( 'home' );

            return $this->render('registration/confirmed.html.twig');
        }

        return $this -> render (
            'registration/register.html.twig' ,
            array ( 'form' => $form -> createView ())
        );
    }

    /**
     * Tell the user his account is now confirmed.
     */
    public function confirmedAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('@FOSUser/Registration/confirmed.html.twig', array(
            'user' => $user,
            'targetUrl' => $this->getTargetUrlFromSession(),
        ));
    }

    /**
     * @return mixed
     */
    private function getTargetUrlFromSession()
    {
        $key = sprintf('_security.%s.target_path', $this->get('security.token_storage')->getToken()->getProviderKey());

        if ($this->get('session')->has($key)) {
            return $this->get('session')->get($key);
        }
    }
}