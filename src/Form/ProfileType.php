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

    public function __construct(AuthorizationCheckerInterface $ch)
    {
        $this->ch = $ch;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
->add('description')
            ->add('minutes')
            ->add('seconds')
            ->add('examplesCount')
            ->add('addPerc')
            ->add('subPerc')
            ->add('multPerc')
            ->add('divPerc')
->add('isDemanding')
;

        foreach (['add', 'sub', 'mult', 'div'] as $k) {
            foreach (['F', 'S', ''] as $n) {
                foreach (['Min', 'Max'] as $m) {
                    $v = $k.$n.$m;
                    $builder->add($v);
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
