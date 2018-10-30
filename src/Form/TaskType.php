<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RadioType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('timesCount', null, ['label' => 'Количество повторений'])
            ->add('addTime', null, [
                'label' => 'Время начала задания',
                'choice_translation_domain' => 'datetime',
                'date_format' => 'ddMMMy',
            ])
            ->add('limitTime', null, [
                'label' => 'Время окончания задания',
                'choice_translation_domain' => 'datetime',
                'date_format' => 'ddMMMy',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'allow_extra_fields' => true,
        ]);
    }
}
