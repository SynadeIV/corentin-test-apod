<?php

namespace App\Command;

use App\Repository\PhotoRepository;
use App\Services\NasaPhotoHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

#[AsCommand(
    name: 'nasa:fetch-today',
    description: "Fetch today's date.",
    aliases: ['nasa:fetch-today'],
    hidden: false
)]
class NasaFetchTodayCommand extends Command
{

    public function __construct(
        private readonly NasaPhotoHandler $nasaPhotoHandler,
        private readonly PhotoRepository $photoRepository
    )
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        if (empty($this->photoRepository->findByDate($today))) {
            $this->nasaPhotoHandler->storeData($this->nasaPhotoHandler->fetchData());
            $output->writeln('Today\'s photo has been saved');
            return Command::SUCCESS;
        }

        $output->writeln('Nothing to fetch :)');
        return Command::SUCCESS;
    }

}