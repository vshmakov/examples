<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Form\SettingsType;
use App\Repository\SettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/settings")
 */
class SettingsController extends AbstractController
{
    use BaseTrait;

    /**
     * @Route("/{id}", name="settings_show", methods="GET")
     */
    public function show(Settings $settings) : Response
    {
        $this->denyAccessUnlessGranted('SHOW', $settings);
        $form = $this->createForm(SettingsType::class, $settings, ['disabled' => true]);

        return $this->render('settings/show.html.twig', [
            'settings' => $settings,
            'form' => $form->createView()
        ]);
    }
}