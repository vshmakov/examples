<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)     {
$builder->add('description', TextType::class);

foreach (['examplesCount', 'tryDuration', 'addMax', 'addMin', 'subMax', 'subMin', 'minSub', 'multMax', 'multMin', 'divMax', 'divMin', 'minDiv'] as $field) {
        $builder->add($field, IntegerType::class, []);
}

foreach (['addPerc', 'subPerc', 'multPerc', 'divPerc'] as $field) {
$builder->add($field, PercentType::class, [
'type'=>'integer'
]);
}

$builder
->add('save', SubmitType::class);
    }

}