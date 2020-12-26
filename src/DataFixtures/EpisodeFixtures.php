<?php


namespace App\DataFixtures;


use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker  =  Faker\Factory::create('fr_FR');
        for ($i=0; $i<200; $i++) {
            $episode = new Episode();
            $episode->setNumber(rand(1,5));
            $episode->setTitle($faker->title);
            $episode->setSynopsis($faker->realText());
            $episode->setSeason($this->getReference('season_' . rand(1,19)));
            $manager->persist($episode);
            }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }
}
