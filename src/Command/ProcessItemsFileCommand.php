<?php

namespace App\Command;

use App\Exception\InvalidItemException;
use App\Exception\InvalidItemTypeException;
use App\Exception\ItemException;
use App\Exception\NoItemException;
use App\Service\StorageService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessItemsFileCommand extends Command
{
    const COMMAND_NAME = 'app:process-item-file';

    public function __construct(
        private StorageService $storageService
    )
    {
        parent::__construct();
    }

    /**
     * Configure custom command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Process the file.')
            ->addArgument('file', InputArgument::REQUIRED, 'The JSON file to process');
    }

    /**
     * Execute the added items command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidItemException
     * @throws InvalidItemTypeException
     * @throws ItemException
     * @throws NoItemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $file = $input->getArgument('file');
            $items = json_decode(file_get_contents($file), true);

            if ($items === null || count($items) == 0) {
                throw new NoItemException();
            }

            foreach ($items as $item) {
                $this->storageService->add($item);
            }

            $output->writeln('File processed successfully');
            return Command::SUCCESS;

        } catch (InvalidItemTypeException $e) {
            throw new InvalidItemTypeException();
        } catch (InvalidItemException $e) {
            throw new InvalidItemException();
        } catch (NoItemException $e) {
            throw new NoItemException();
        } catch (Exception $e) {
            throw new ItemException($e->getMessage());
        }
    }
}
