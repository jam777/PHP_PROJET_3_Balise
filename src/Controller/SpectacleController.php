<?php

namespace App\Controller;

use App\Entity\ShowDate;
use App\Entity\Spectacle;
use App\Form\SpectacleType;
use App\Repository\ShowDateRepository;
use App\Repository\SpectacleRepository;
use App\Repository\TheaterRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/spectacle")
 */
class SpectacleController extends AbstractController
{
    /**
     * @Route("/", name="spectacle_index", methods={"GET"})
     * @param SpectacleRepository $spectacleRepository
     * @param TheaterRepository $theaterRepository
     * @return Response
     */
    public function index(SpectacleRepository $spectacleRepository, TheaterRepository $theaterRepository): Response
    {
        $user = $this->getUser();
        $theater = $theaterRepository->findOneBy(['user' => $user]);
        $spectacle = $spectacleRepository->findBy(['theater'=>$theater]);

        return $this->render('spectacle/index.html.twig', [
            'spectacles' => $spectacle, 'user' => $user, 'theater' => $theater ]);
        /*
        return $this->render('spectacle/index.html.twig', [
            'spectacles' => $spectacleRepository->findAll(),
        ]);*/
    }

    /**
     * @Route("/new/Spectacle", name="spectacle_new", methods={"GET","POST"})
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function new(Request $request, ObjectManager $manager, TheaterRepository $theaterRepository): Response
    {
        $spectacle = new Spectacle();

        $user = $this->getUser();
        $theater = $theaterRepository->findOneBy(['user' => $user]);

        $spectacle ->setBaseRate($theater->getBaseRate());
        $form = $this->createForm(SpectacleType::class, $spectacle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $fileImage = $request->files->get('spectacle')['image'];

            if ($fileImage) {
                $fileName = md5(uniqid()) . '.' . $fileImage->guessExtension();
                try {
                    $fileImage->move($this->getParameter('logo_directory'), $fileName);
                } catch (FileException $e) {
                    throw new FileException($e);
                }
                $spectacle->setImage($fileName);
            }


            $spectacle->setTheater($theater);

            $manager->persist($spectacle);
            $manager->flush();

            return $this->redirectToRoute('spectacle_index');
        }

        return $this->render('spectacle/new.html.twig', [
            'spectacle' => $spectacle,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="spectacle_show", methods={"GET"})
     * @param Spectacle $spectacle
     * @return Response
     */
    public function show(Spectacle $spectacle): Response
    {
        return $this->render('spectacle/show.html.twig', [
            'spectacle' => $spectacle,

        ]);
    }

    /**
     * @Route("/{id}/edit", name="spectacle_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Spectacle $spectacle
     * @return Response
     */
    public function edit(Request $request, Spectacle $spectacle): Response
    {
        $form = $this->createForm(SpectacleType::class, $spectacle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $fileImage = $request->files->get('spectacle')['image'];

            if ($fileImage) {
                $fileName = md5(uniqid()) . '.' . $fileImage->guessExtension();
                try {
                    $fileImage->move($this->getParameter('logo_directory'), $fileName);
                } catch (FileException $e) {
                    throw new FileException($e);
                }
                $spectacle->setImage($fileName);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('spectacle_index', [
                'id' => $spectacle->getId(),
            ]);
        }

        return $this->render('spectacle/edit.html.twig', [
            'spectacle' => $spectacle,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="spectacle_delete", methods={"DELETE"})
     * @param Request $request
     * @param Spectacle $spectacle
     * @return Response
     */
    public function delete(Request $request, Spectacle $spectacle): Response
    {
        if ($this->isCsrfTokenValid('delete'.$spectacle->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($spectacle);
            $entityManager->flush();
        }

        return $this->redirectToRoute('spectacle_index');
    }

    /**
     * @Route("/show/{id}", name="detailSpectacle", methods={"GET"})
     * @param Spectacle $spectacle
     * @return Response
     */
    public function detailSpectacle(Spectacle $spectacle, ShowDateRepository $showDateRepository): Response
    {
        $today = new \DateTime();

        $showDate = $showDateRepository->dateList($spectacle->getId(), $today);

        return $this->render('spectacle/spectacle.html.twig', [
            'spectacle' => $spectacle,
            'showDate' => $showDate
        ]);
    }

    /**
     * @Route("/show/more/{id}", name="moreSpectacle", methods={"GET"})
     * @param Spectacle $spectacle
     * @return Response
     */
    public function moreSpectacleDates(Spectacle $spectacle, ShowDateRepository $showDateRepository) : Response
    {
        $today = new \DateTime();

        $showDate = $showDateRepository->moreDateList($spectacle->getId(), $today);

        return $this->render('spectacle/moreSpectacle.html.twig', [
            'spectacle' => $spectacle,
            'showDate' => $showDate
        ]);
    }
}
