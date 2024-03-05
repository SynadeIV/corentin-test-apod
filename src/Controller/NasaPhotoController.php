<?php

namespace App\Controller;

use App\Repository\PhotoRepository;
use App\Services\NasaPhotoHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/photo', name: 'app_photo')]
#[IsGranted('ROLE_GOOGLE')]
class NasaPhotoController extends AbstractController
{
    public function __construct(
        private readonly NasaPhotoHandler $nasaPhotoHandler,
        private readonly PhotoRepository $photoRepository
    ) {

    }
    #[Route('/today', name: '_today')]
    public function fetchToday(): Response
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0, 0);

        if (empty($this->photoRepository->findByDate($today))) {
            return $this->render('photo.html.twig', ['message' => 'No photo available for today (don\'t worry, it\'s not your fault :D, let\'s try again after fetch the today\'s photo)']);
        }

        $data = $this->nasaPhotoHandler->getNextImageAvailableData($today);

        return $this->render('photo.html.twig', ['data' => $data]);
    }

}