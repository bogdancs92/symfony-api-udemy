<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\Security\TokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var \Faker\Factory
     */
    private $faker;
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder,
                    TokenGenerator $tokenGenerator)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create();
        $this->tokenGenerator = $tokenGenerator;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $this->loadUsers($manager);
        $this->loadPosts($manager);
        $this->loadComments($manager);

        $manager->flush();
    }

    public function loadPosts($manager) {
        $user = $this->getReference("bog");

        for ($i=0;$i<100;$i++) {
            $blogPost = new BlogPost();
            $blogPost->setTitle($this->faker->realText(30));
            $blogPost->setPublished($this->faker->dateTimeThisYear);
            $blogPost->setAuthor($user);
            $blogPost->setContent($this->faker->realText(255));
            $blogPost->setSlug($this->faker->slug());
            $this->setReference("blog_post_$i",$blogPost);
            $manager->persist($blogPost);
        }
        $blogPost = new BlogPost();
        $blogPost->setTitle("Post n°2");
        $blogPost->setPublished(new \DateTime("2020-01-05 10:52:00"));
        $blogPost->setAuthor($user);
        $blogPost->setContent("demo content 2");
        $blogPost->setSlug("demo-content-2");
        $manager->persist($blogPost);
        $manager->flush();
    }

    public function loadComments($manager) {

        $user = $this->getReference("bog");
        for ($i=0;$i<100;$i++) {
            $post = $this->getReference("blog_post_$i");
            for ($j=0;$j<rand(1,10);$j++) {
                $c = new Comment();
                $c->setPost($post);
                $c->setContent($this->faker->realText());
                $c->setAuthor($user);
                $c->setPublished($this->faker->dateTimeThisYear);
                $manager->persist($c);
            }
        }
        $manager->flush();
    }

    public function loadUsers($manager) {
        $user = new User();
        $user->setEmail("bogdans@gmail.com");
        $user->setName("Bogdan");
        $user->setPassword($this->passwordEncoder->encodePassword($user,"Secret1."));
        //$user->setPassword("Secret1.");
        $user->setUsername("Bog");
        $user->setEnabled(true);
        $user->setRoles([User::ROLE_SUPERADMIN]);
        $this->addReference('bog',$user);
        $manager->persist($user);

        $user = new User();
        $user->setEmail("bogdans2@gmail.com");
        $user->setName("Bogdan comentator");
        $user->setPassword($this->passwordEncoder->encodePassword($user,"Secret1."));
        //$user->setPassword("Secret1.");
        $user->setUsername("Bog2");
        $user->setEnabled(false);
        $user->setConfirmationToken($this->tokenGenerator->getRandomSecureToken(40));
        $user->setRoles([User::ROLE_COMMENTATOR]);
        $this->addReference('bog_comentator',$user);

        $manager->persist($user);
        $manager->flush();
    }
}
