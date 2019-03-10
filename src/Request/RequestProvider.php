<?php

namespace App\Request;

use App\Request\DataTables\DataTablesRequest;
use App\Request\DataTables\DataTablesRequestProviderInterface;
use App\Request\DataTables\DataTablesType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestProvider implements DataTablesRequestProviderInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var FormFactoryInterface */
    private $formFactory;

    public function __construct(RequestStack $requestStack, FormFactoryInterface $formFactory)
    {
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
    }

    public function getDataTablesRequest(): ?DataTablesRequest
    {
        if (!$request = $this->requestStack->getMasterRequest()) {
            return null;
        }

        $dataTablesRequest = new DataTablesRequest();
        $form = $this->formFactory->create(DataTablesType::class, $dataTablesRequest);
        $form->submit($request->query->all());

        if (!$form->isSubmitted() or !$form->isValid()) {
            return null;
        }

        return $dataTablesRequest;
    }

    public function hasDataTablesRequest(): bool
    {
        return (bool) $this->getDataTablesRequest();
    }
}
