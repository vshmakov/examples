<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class ApiRequestHandler implements RequestHandlerInterface
{
    /**
     * @param Request $request
     */
    public function handleRequest(FormInterface $form, $request = null): void
    {
        Assert::isInstanceOf($request, Request::class);

        if (!$request->isMethod($form->getConfig()->getMethod())) {
            return;
        }

        $form->submit($this->getSubmittedData($request), false);
    }

    public function isFileUpload($data): bool
    {
        return false;
    }

    private function getSubmittedData(Request $request): array
    {
        if ($request->isMethod('GET')) {
            return $request->query->all();
        }

        return $request->request->all();
    }
}
