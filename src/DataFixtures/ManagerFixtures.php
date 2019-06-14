<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\Administrator;

class ManagerFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $administrator = new Administrator();
        $administrator->setUsername('master');
        $administrator->setEmail('');
        $administrator->setPassword($this->passwordEncoder->encodePassword($administrator,'master234356'));
        $administrator->setName('Administrador Maestro');
        $administrator->setRoles(['ROLE_SUPER_ADMIN']);
        
        $manager->persist($administrator);

        $manager->flush();
    }
}
