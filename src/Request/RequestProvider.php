<?php

namespace App\Request;

use App\ApiPlatform\Attribute;
use App\ApiPlatform\Format;
use App\Request\DataTables\DataTablesRequest;
use App\Request\DataTables\DataTablesRequestProviderInterface;
use App\Request\DataTables\DataTablesRequestType;
use App\Request\Http\ContentType;
use App\Request\Pagination\PaginationRequest;
use App\Request\Pagination\PaginationRequestProviderInterface;
use App\Request\Pagination\PaginationRequestType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final class RequestProvider implements DataTablesRequestProviderInterface, PaginationRequestProviderInterface
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
        $form = $this->formFactory->create(DataTablesRequestType::class, $dataTablesRequest);
        $form->handleRequest($request);

        if (!$this->isDataTablesRequest() or !$form->isSubmitted() or !$form->isValid()) {
            return null;
        }

        return $dataTablesRequest;
    }

    public function isDataTablesRequest(): bool
    {
        if (!$request = $this->requestStack->getMasterRequest()) {
            return false;
        }

        return Format::JSONDT === $request->attributes->get(Attribute::FORMAT)
            or false !== mb_strpos($request->headers->get(ContentType::ACCEPT_HEADER), ContentType::JSONDT);
    }

    public function isDataTablesRequestValid(): ?bool
    {
        return $this->isDataTablesRequest() ? (bool) $this->getDataTablesRequest() : null;
    }

    public function getPaginationRequest(): ?PaginationRequest
    {
        $request = $this->requestStack->getMasterRequest();
        Assert::notNull($request);
        $paginationRequest = new PaginationRequest();
        $form = $this->formFactory->create(PaginationRequestType::class, $paginationRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return null;
        }

        return $paginationRequest;
    }

    public function isPaginationRequestValid(): bool
    {
        return (bool) $this->getPaginationRequest();
    }
}
