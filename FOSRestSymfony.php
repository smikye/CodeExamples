<?php

namespace App\Controller\Open;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use FOS\RestBundle\Controller\Annotations as FOSRest;

use App\Entity\User;
use App\Controller\AbstractController;


/**
 * Register controller.
 *
 * @Route("/public")
 */
class RegisterController extends AbstractController
{

    /**
     * Register User Action.
     * @FOSRest\Post("/register")
     *
     */
    public function handleAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        $em = $this->getDoctrine()->getManager();

        $options = array(
            'phone_number' => $request->get('phone_number'),
            'password' => $request->get('password')
        );
        $valid = $this->validate($options);

        if(!$valid){
            $this->getClientResponse()->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $this->buildResponse();
        }

        $user = new User();
        $user->setPhoneNumber($request->get('phone_number'))
             ->setPassword($passwordEncoder->encodePassword($user, $request->get('password')));

        $em->persist($user);
        $em->flush();

        $this->getClientResponse()->addInfo('REGISTERED_SUCCESSFULLY');

        return $this->buildResponse();
    }

    /**
     * @param $options array
     * @return  bool
     */
    public function validate($options)
    {

        $em = $this->getDoctrine()->getManager();

        if(!$options['phone_number']){
            $this->getClientResponse()->addError('MUST_SEND_PHONE_NUMBER');
            return false;
        }

        if(!$options['password']){
            $this->getClientResponse()->addError('MUST_SEND_PHONE_NUMBER');
            return false;
        }

        $user = $em->getRepository(User::class)->findOneBy(['phone_number' => $options['phone_number']]);
        if($user) {
            $this->getClientResponse()->addError('PHONE_NUMBER_ALREADY_REGISTERED');
            return false;
        }

        return true;
    }
}