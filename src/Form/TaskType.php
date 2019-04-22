<?php

declare(strict_types=1);

namespace App\Form;

use App\Attempt\Profile\ProfileProviderInterface;
use App\Attempt\Settings\SettingsProviderInterface;
use App\Doctrine\QueryResult;
use App\Entity\Profile;
use App\Entity\Task;
use App\Security\User\CurrentUserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TaskType extends AbstractType
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var ProfileProviderInterface */
    private $profileProvider;

    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        ProfileProviderInterface $profileProvider,
        SettingsProviderInterface $settingsProvider,
        EntityManagerInterface $entityManager
    ) {
        $this->currentUserProvider = $currentUserProvider;
        $this->profileProvider = $profileProvider;
        $this->settingsProvider = $settingsProvider;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dateTimeOptions = [
            'choice_translation_domain' => 'datetime',
            'date_format' => 'ddMMMy',
        ];

        $builder
            ->add('timesCount', NumberType::class)
            ->add('addTime', DateTimeType::class, $dateTimeOptions)
            ->add('limitTime', DateTimeType::class, $dateTimeOptions)
            ->add('profile', ChoiceType::class, [
                'choices' => $this->getAvailableProfilesIdList(),
                'choice_label' => false,
                'expanded' => true,
                'mapped' => false,
            ])
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'setSettingsByPassedProfile']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'allow_extra_fields' => true,
        ]);
    }

    private function getAvailableProfilesIdList(): array
    {
        return QueryResult::column(
            $this->entityManager
                ->createQueryBuilder()
                ->select('distinct p.id as profileId')
                ->from(Profile::class, 'p')
                ->where('p.author = :currentUser')
                ->orWhere('p.isPublic = true')
                ->getQuery()
                ->setParameter('currentUser', $this->currentUserProvider->getCurrentUserOrGuest())
        );
    }

    public function setSettingsByPassedProfile(FormEvent $event): void
    {
        $profileField = $event->getForm()->get('profile');
        $profileId = $profileField->getData();
        $profile = !$profileId ? null : $this->entityManager
            ->getRepository(Profile::class)
            ->find($profileId);

        if (null === $profile) {
            $profileField->addError(new FormError('Необходимо выбрать профиль'));

            return;
        }

        $settings = $this->settingsProvider->getOrCreateSettingsByProfile($profile);
        /** @var Task $task */
        $task = $event->getData();
        $task->setSettings($settings);
    }
}
