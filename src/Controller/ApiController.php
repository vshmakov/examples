<?php

namespace App\Controller;

use App\Security\UloginAuthenticator;
use App\Repository\TransferRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    use BaseTrait;

    /**
     * @Route("/request/yandex", name="api_request_yandex", methods="POST")
     */
    public function request(Request $request, UserRepository $userRepository, TransferRepository $transferRepository)
    {
        $label = $request->request->get('label');
        $withdraw_amount = $request->request->get('withdraw_amount');
        $unaccepted = $request->request->get('unaccepted');

        $statusCode = 400;
        $answer = ['error' => "No transfer with $label label"];
        $transfer = $transferRepository->findOneBy(['label' => $label, 'held' => false]);
        $user = $transfer ? $transfer->getUser() : null;

        if ($user && 'true' != $unaccepted) {
            $user->addMoney($withdraw_amount);
            $transfer->setMoney($withdraw_amount)
                ->setHeldTime(new \DateTime())
                ->setHeld(true);
            $this->getEntityManager()->flush();
            $statusCode = 200;
            $answer['error'] = false;
        }

        return $this->json($answer, $statusCode);
    }

    /**
     * @Route("/ulogin/login", name="api_ulogin_login")
     */
    public function ulogin(Request $request)
    {
        throw new \Exception('Ulogin login method must not be opened');
    }

    /**
     * @Route("/ulogin/register", name="api_ulogin_register", methods="POST")
     */
    public function uloginRegister(Request $request, UloginAuthenticator $uloginAuthenticator, UserRepository $userRepository)
    {
        $credentials = $uloginAuthenticator->getCredentials($request);

        if ($uloginAuthenticator->checkCredentials($credentials)) {
            $userRepository->findOneByUloginCredentialsOrNew($credentials);
        }

        return $this->redirectToRoute('api_ulogin_login', [
            'token' => $request->request->get('token'),
        ]);
    }
}
