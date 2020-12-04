<?php
/*******************************************************
 * Copyright (C) 2018 La Poste.
 *
 * This file is part of La Poste - Colissimo module.
 *
 * La Poste - Colissimo module can not be copied and/or distributed without the express
 * permission of La Poste.
 *******************************************************/
namespace LaPoste\Colissimo\Console\Command;


use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeLabelFolder extends Command
{
    /**
     * @var
     */
    protected $state;
    /**
     * @var
     */
    protected $cronTask;

    /**
     * PurgeLabelFolder constructor.
     * @param \Magento\Framework\App\State             $state
     * @param \LaPoste\Colissimo\Cron\PurgeLabelFolder $cronTask
     */
    public function __construct(
        State $state,
        \LaPoste\Colissimo\Cron\PurgeLabelFolder $cronTask
    ) {
        $this->state = $state;
        $this->cronTask = $cronTask;

        return parent::__construct();
    }


    protected function configure()
    {
        $this->setName('lpc:purge:labelFolder')
            ->setDescription('Purge label folder');
        parent::configure();
    }


    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            // this is needed for a Command
            $this->state->setAreaCode(
                \Magento\Framework\App\Area::AREA_ADMINHTML
            );

            $this->cronTask->execute();
            $output->writeln('Purge successful');
        } catch (\Exception $exc) {
            $output->writeln($exc->getMessage());
        }
    }
}
