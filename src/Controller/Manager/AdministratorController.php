<?php

namespace App\Controller\Manager;

use App\Entity\Administrator;
use App\Form\Administrator\AdministratorType;
use App\Repository\AdministratorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/manager/administrator")
 */
class AdministratorController extends AbstractController
{
    /**
     * index.
     * 
     * @Route("/", name="manager_administrator", methods={"GET"})
     */
    public function index(AdministratorRepository $administratorRepository): Response
    {       
        $request = Request::createFromGlobals();
        
        $page = ($request->query->get('page'))?$request->query->get('page'):1;

        $pageSize = 20;
        $administrators = $administratorRepository->findPaginated($pageSize, $page);
        $itemsCount = count($administrators);
        $pagesCount = ceil($itemsCount / $pageSize);

        return $this->render('manager/administrator/index.html.twig', [
            "administrators" => $administrators,
            "itemsCount" => $itemsCount,
            "pagesCount" => $pagesCount,
            "currentPage" => $page
        ]);
    }

    /**
     * create.
     * 
     * @Route("/create", name="manager_administrator_create", methods={"GET","POST"})
     */
    public function create(UserPasswordEncoderInterface $encoder, Request $request): Response
    {
        if ( null === $this->getUser() || false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ){
            $this->addFlash('error', 'No tienes permisos suficientes');
            return $this->redirectToRoute('manager_administrator');
        }
        
        $administrator = new Administrator();
        $administratorForm = $this->createForm(AdministratorType::class, $administrator);
        $administratorForm->handleRequest($request);

        if ($administratorForm->isSubmitted() && $administratorForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            
            $administrator->setPassword($encoder->encodePassword($administrator, $administrator->getPassword()));

            $entityManager->persist($administrator);
            $entityManager->flush();

            $this->addFlash('success', 'Registro agregado');
            return $this->redirectToRoute('manager_administrator');
        }

        return $this->render('manager/administrator/create.html.twig', [
            'administrator' => $administrator,
            'administratorForm' => $administratorForm->createView(),
        ]);
    }

    /**
     * update.
     * 
     * @Route("/{id}/update", name="manager_administrator_update", methods={"GET","POST"})
     */
    public function update(UserPasswordEncoderInterface $encoder, Request $request, Administrator $administrator): Response
    {
        if ( null === $this->getUser() || false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || $administrator->getRole() == 'ROLE_SUPER_ADMIN') {
            $this->addFlash('error', 'No tienes permisos suficientes');
            return $this->redirectToRoute('manager_administrator');
        }
        
        $administratorForm = $this->createForm(AdministratorType::class, $administrator);
        $administratorForm->handleRequest($request);

        if ($administratorForm->isSubmitted() && $administratorForm->isValid()) {
            
            if(!empty($request->request->get('administrator')['password'])){
                $administrator->setPassword($encoder->encodePassword($administrator, $administrator->getPassword()));
            }
            
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Registro guardado');
            return $this->redirectToRoute('manager_administrator', [
            ]);
        }

        return $this->render('manager/administrator/update.html.twig', [
            'administrator' => $administrator,
            'administratorForm' => $administratorForm->createView(),
        ]);
    }

    /**
     * delete.
     * 
     * @Route("/{id}", name="manager_administrator_delete", methods={"GET","DELETE"})
     */
    public function delete(Request $request, Administrator $administrator): Response
    {
        if ( null === $this->getUser() || false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || $administrator->getRole() == 'ROLE_SUPER_ADMIN') {
            $this->addFlash('error', 'No tienes permisos suficientes');
            return $this->redirectToRoute('manager_administrator');
        }
        
        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('manager_administrator_delete', array('id' => $administrator->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
        
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($administrator);
            $entityManager->flush();

            $this->addFlash('success', 'Registro eliminado');
            return $this->redirectToRoute('manager_administrator');
        }

        return $this->render('manager/administrator/delete.html.twig', array(
            'administrator' => $administrator,
            'deleteForm' => $deleteForm->createView(),
        ));
    }
}
