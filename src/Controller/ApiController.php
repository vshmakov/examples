<?php

namespace App\Controller;

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
     * @Route("/ulogin/register", name="api_ulogin_register", methods="POST")
     */
    public function uloginRegister(Request $request, UserRepository $userRepository)
    {
        $token = $request->request->get('token');
        $json = file_get_contents(sprintf(
            'http://ulogin.ru/token.php?token=%s&host=%s',
            $token,
            $request->server->get('HTTP_HOST')
        ));

        $credentials = json_decode($json, true);

        if ($credentials) {
            $credentials += [
                'token' => $token,
                'username' => '^'.$credentials['network'].'-'.$credentials['uid'],
            ];
        } else {
            $this->denyAccessUnlessGranted(null);
        }

        $user = $userRepository->findOneByUloginCredentialsOrNew($credentials);
        $this->addFlash('login', $user->getId());

        return $this->redirectToRoute('api_login');
    }

    /**
     * @Route("/login", name="api_login", methods="GET")
Added logout confirming     */

    public function login()
    {
        //$this->denyAccessUnlessGranted(null);
    }
}
