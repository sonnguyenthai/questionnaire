<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 02/01/18
 * Time: 03:15
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * Class RegistrationType
 * @package AppBundle\Form
 */
class RegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array('label' => 'Email '))
            ->add('username', TextType::class, array('label' => 'Pseudo '))
            ->add('firstname', TextType::class, array('label' => 'Prénom '))
            ->add('lastname', TextType::class, array('label' => 'Nom '))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options' => array('label' => 'Mot de passe '),
                'second_options' => array('label' => 'Répéter le mot de passe '),
                'invalid_message' => 'fos_user.password.mismatch',
            ));
    }

    /**
     * @return string
     */
    public function getParent()

    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    /**
     * @return string
     */
    public function getBlockPrefix()

    {
        return 'app_user_registration';
    }

    /**
     * @return string
     */
    public function getName()

    {
        return $this->getBlockPrefix();
    }
}