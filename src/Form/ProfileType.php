<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProfileType extends AbstractType
{
private $ch;

public function __construct(AuthorizationCheckerInterface $ch) {
$this->ch=$ch;
}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
->add("description")
            ->add('minutes')
            ->add('seconds')
            ->add('examplesCount')
            ->add('addMin')
            ->add('addMax')
            ->add('subMin')
            ->add('subMax')
            ->add('minSub')
            ->add('multMin')
            ->add('multMax')
            ->add('divMin')
            ->add('divMax')
            ->add('minDiv')
            ->add('addPerc')
            ->add('subPerc')
            ->add('multPerc')
            ->add('divPerc')
            ->add('addTime');

if ($this->ch->isGranted("ROLE_SUPER_ADMIN")) {
        $builder
            ->add('isPublic')
            ->add('author');
}
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
