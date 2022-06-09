<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\FixtureService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture {

    private $fs;

    public function __construct(FixtureService $fs) {
        $this->fs = $fs;
    }

    public function load(ObjectManager $manager) {
        $admin = new Admin();
        $admin->setEmail('admin@admin.com')
                ->setRoles(['ROLE_ADMIN'])
                ->setPassword(sha1('admin'));
        $manager->persist($admin);

        $user = new User();
        $email='user@user.com';
        $user->setName('user')
        ->setCity('Ludhiana')
        ->setEmail($email)
        ->setRoles(['ROLE_USER'])
        ->setState('Punjab')
        ->setMobile('00000000')
        ->setPassword(sha1('user'))
        ->setEmailVerificationCode($email . rand(100000, 999999) . time())
        ->setFacebookId(null)
        ->setGoogleId(null);
        $manager->persist($user);

        $category = new Category();
        $category->setName('Health')
                ->setIsTop(false);
        $manager->persist($category);

        $tags = new Tag();
        $tags->setName('Food');
        $manager->persist($tags);
        $manager->flush();

        $this->fs->StoryFixture($user);
    }

}
