<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 01/01/18
 * Time: 20:55
 */

namespace AppBundle\Form ;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\AbstractType;


class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $constraintsOptions = array(
            'message' => 'fos_user.current_password.invalid',
        );

        if (!empty($options['validation_groups'])) {
            $constraintsOptions['groups'] = array(reset($options['validation_groups']));
        }

        $builder
            ->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('email', null, array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('lastname', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('firstname', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('current_password', PasswordType::class, array(
            'label' => 'Mot de passe actuel: ',
            'translation_domain' => 'FOSUserBundle',
            'mapped' => false,
            'constraints' => array(
                new NotBlank(),
                new UserPassword($constraintsOptions),
            ),
        ));
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }
}