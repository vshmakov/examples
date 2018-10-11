<?php

namespace App\Controller\Admin;

use App\Entity\Ip;
use App\Form\IpType;
use App\Repository\IpRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/ip")
 */
class IpController extends Controller
{
    /**
     * @Route("/", name="ip_index", methods="GET")
     */
    public function index(IpRepository $ipRepository): Response
    {
throw new \Exception("MyTestException");
        return $this->render('ip/index.html.twig', ['ips' => $ipRepository->findAll()]);
    }

    /**
     * @Route("/new", name="ip_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $ip = new Ip();
        $form = $this->createForm(IpType::class, $ip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($ip);
            $em->flush();

            return $this->redirectToRoute('ip_index');
        }

        return $this->render('ip/new.html.twig', [
            'ip' => $ip,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="ip_show", methods="GET")
     */
    public function show(Ip $ip): Response
    {
        return $this->render('ip/show.html.twig', ['ip' => $ip]);
    }

    /**
     * @Route("/{id}/edit", name="ip_edit", methods="GET|POST")
     */
    public function edit(Request $request, Ip $ip): Response
    {
        $form = $this->createForm(IpType::class, $ip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ip_edit', ['id' => $ip->getId()]);
        }

        return $this->render('ip/edit.html.twig', [
            'ip' => $ip,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="ip_delete", methods="DELETE")
     */
    public function delete(Request $request, Ip $ip): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ip->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($ip);
            $em->flush();
        }

        return $this->redirectToRoute('ip_index');
    }
}
