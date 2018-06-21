<?php

namespace App\Controller;

use App\Entity\Example;
use App\Form\ExampleType;
use App\Repository\ExampleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/example")
 */
class ExampleController extends Controller
{
    /**
     * @Route("/", name="example_index", methods="GET")
     */
    public function index(ExampleRepository $exampleRepository): Response
    {
        return $this->render('example/index.html.twig', ['examples' => $exampleRepository->findAll()]);
    }

    /**
     * @Route("/new", name="example_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $example = new Example();
        $form = $this->createForm(ExampleType::class, $example);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($example);
            $em->flush();

            return $this->redirectToRoute('example_index');
        }

        return $this->render('example/new.html.twig', [
            'example' => $example,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="example_show", methods="GET")
     */
    public function show(Example $example): Response
    {
        return $this->render('example/show.html.twig', ['example' => $example]);
    }

    /**
     * @Route("/{id}/edit", name="example_edit", methods="GET|POST")
     */
    public function edit(Request $request, Example $example): Response
    {
        $form = $this->createForm(ExampleType::class, $example);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('example_edit', ['id' => $example->getId()]);
        }

        return $this->render('example/edit.html.twig', [
            'example' => $example,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="example_delete", methods="DELETE")
     */
    public function delete(Request $request, Example $example): Response
    {
        if ($this->isCsrfTokenValid('delete'.$example->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($example);
            $em->flush();
        }

        return $this->redirectToRoute('example_index');
    }
}
