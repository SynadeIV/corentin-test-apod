<?php

namespace App\Services;

use App\Entity\Photo;
use App\Repository\PhotoRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NasaPhotoHandler
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PhotoRepository $photoRepository,
        private readonly HttpClientInterface    $httpClient
    ) {
    }

    public function storeData(array $nasaData): void
    {
        if (!empty($nasaData)) {
            $date = DateTime::createFromFormat('Y-m-d', $nasaData['date']);
            $date->setTime(0, 0, 0);

            $photo = new Photo();
            $photo
                ->setTitle($nasaData['title'])
                ->setExplanation($nasaData['explanation'])
                ->setDate(new \DateTime($nasaData['date']))
                ->setUrl($nasaData['url'])
                ->setMediaType($nasaData['media_type'])
            ;

            $this->em->persist($photo);
            $this->em->flush();
        }
    }

    public function fetchData(): array
    {
        $data = [];
        $date = new \DateTime();
        $date->setTime(0, 0, 0);

        if (empty($this->photoRepository->findOneByDate($date))) {
            $apikey = 'https://api.nasa.gov/planetary/apod?api_key=' . $_ENV['NASA_API_KEY'];
            try {
                $response = $this->httpClient->request(
                    'GET',
                    $apikey
                );

                if ($response->getStatusCode() === 200) {
                    $data = $response->toArray();
                } else {
                    throw new \RuntimeException('Something went wrong');
                }
            } catch (\RuntimeException $e) {
                throw new \RuntimeException('Something went wrong' . $e);
            }
        }
        return $data;

    }

    public function getNextImageAvailableData($date): ?Photo
    {
        $date->setTime(0, 0, 0);

        $photoData = $this->photoRepository->findOneByDate($date);
        if (!empty($photoData) && 'image' !== $photoData->getMediaType()) {
            $date->modify('-1 day');
            $photoData = $this->getNextImageAvailableData($date);
        }

        return $photoData;

    }
}