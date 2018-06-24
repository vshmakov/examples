<?php

namespace App\Controller;

use App\Entity\Transfer;
use App\Form\TransferType;
use App\Repository\TransferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/transfer")
 */
class TransferController extends Controller
{
    /**
     * @Route("/", name="transfer_index", methods="GET")
     */
    public function index(TransferRepository $transferRepository): Response
    {
        return $this->render('transfer/index.html.twig', ['transfers' => $transferRepository->findAll()]);
    }

    /**
     * @Route("/new", name="transfer_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $transfer = new Transfer();
        $form = $this->createForm(TransferType::class, $transfer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($transfer);
            $em->flush();

            return $this->redirectToRoute('transfer_index');
        }

        return $this->render('transfer/new.html.twig', [
            'transfer' => $transfer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transfer_show", methods="GET")
     */
    public function show(Transfer $transfer): Response
    {
        return $this->render('transfer/show.html.twig', ['transfer' => $transfer]);
    }

    /**
     * @Route("/{id}/edit", name="transfer_edit", methods="GET|POST")
     */
    public function edit(Request $request, Transfer $transfer): Response
    {
        $form = $this->createForm(TransferType::class, $transfer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('transfer_edit', ['id' => $transfer->getId()]);
        }

        return $this->render('transfer/edit.html.twig', [
            'transfer' => $transfer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transfer_delete", methods="DELETE")
     */
    public function delete(Request $request, Transfer $transfer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transfer->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($transfer);
            $em->flush();
        }

        return $this->redirectToRoute('transfer_index');
    }
}
