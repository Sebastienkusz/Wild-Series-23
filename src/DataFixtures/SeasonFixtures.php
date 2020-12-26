<?php


namespace App\DataFixtures;


use App\Entity\Program;
use App\Entity\Season;
use App\Repository\ProgramRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {
            $season = new Season();
            $season->setNumber(rand(1,3));
            $season->setYear(rand(1990, 2020));
            $season->setDescription($faker->realText());
            $season->setProgram($this->getReference('program_' . rand(0,5)));

            $manager->persist($season);

            $this->addReference('season_' . ($i), $season);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [ProgramFixtures::class];
    }
}
