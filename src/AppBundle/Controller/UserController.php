<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\User;

class UserController extends Controller
{

    public function indexAction()
    {
        $users = $this->getDoctrine()
                      ->getRepository(User::class)
                      ->findAll();

        $data = [];

        foreach ($users as $user) {
          $data[] = $this->userToArray($user);
        }

        return new JsonResponse($data, 200);
    }

    public function showAction($id)
    {
      $user = $this->getDoctrine()
                   ->getRepository(User::class)
                   ->find($id);
      return new JsonResponse($this->userToArray($user), 200);
    }

    private function userToArray($user)
    {
      return [
        'id' => $user->getId(),
        'name' => $user->getName(),
        'username' => $user->getUsername(),
        'password' => $user->getPassword(),
      ];
    }

    public function storeAction(Request $request)
    {
      $data = json_decode($request->getContent());

      $user = new User();
      $user->setName($data->name);
      $user->setUsername($data->username);
      $user->setPassword($data->password);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($user);
      $entityManager->flush();

      if($user->getId() === null){
        return new JsonResponse([
          'success' => false,
          'message' => "Failed to store data"
        ], 500);
      }else{
        return new JsonResponse([
          'success' => true,
          'message' => "Data successfully stored"
        ], 200);
      }
    }

    public function updateAction(Request $req, $id)
    {
      $em = $this->getDoctrine()->getManager();
      $user = $em->getRepository(User::class)->find($id);

      if(!$user){
        return new JsonResponse([
          'success' => false,
          'message' => "User not found"
        ], 404);
      }

      $data = json_decode($req->getContent());

      $user->setName($data->name ?? $user->getName());
      $user->setUsername($data->username ?? $user->getUsername());
      $user->setPassword($data->password ?? $user->getPassword());
      $em->flush();

      return new JsonResponse([
          'success' => true,
          'message' => "User successfully saved",
          'data' => $this->userToArray($user)
      ], 200);
    }

    public function destroyAction($id)
    {
      $em = $this->getDoctrine()->getManager();
      $user = $em->getRepository(User::class)->find($id);
      $em->remove($user);
      $em->flush();

      return new JsonResponse([
          'success' => true,
          'message' => "User successfully deleted",
          'data' => $this->userToArray($user)
      ], 200);
    }
}
