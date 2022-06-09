<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\Story;
use App\Entity\Tag;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Description of FixtureService
 *
 * @author dev
 */
class FixtureService {
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }
    public function StoryFixture($user) {
        $em= $this->em;
               $arr = [
            ['title' => 'Lorem IPsum',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'createdOn' =>new DateTime('now', new DateTimeZone('Asia/Kolkata'))
                
       ],
            ['title' => 'Lorem IPsum1',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'createdOn' =>new DateTime('now', new DateTimeZone('Asia/Kolkata'))
                
       ],
            ['title' => 'Lorem IPsum2',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'createdOn' =>new DateTime('now', new DateTimeZone('Asia/Kolkata'))
                
       ],
            ['title' => 'Lorem IPsum3',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'createdOn' =>new DateTime('now', new DateTimeZone('Asia/Kolkata'))
                
       ],
            ['title' => 'Lorem IPsum4',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'createdOn' =>new DateTime('now', new DateTimeZone('Asia/Kolkata'))
                
       ],
            ['title' => 'Lorem IPsum5',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'createdOn' =>new DateTime('now', new DateTimeZone('Asia/Kolkata'))
                
       ],
            ['title' => 'Lorem IPsum6',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'createdOn' =>new DateTime('now', new DateTimeZone('Asia/Kolkata'))
                
       ],
            ['title' => 'Lorem IPsum7',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'createdOn' =>new DateTime('now', new DateTimeZone('Asia/Kolkata'))
                
       ],
                ['title' => 'Lorem IPsum8',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
            'createdOn' =>new DateTime('now', new DateTimeZone('Asia/Kolkata'))
                
       ]];
            $noImgPath = __DIR__ . '/../Resources/no.jpg';
            $data= file_get_contents($noImgPath);
            $category=$em->getRepository(Category::class)->findOneBy(['name'=>'Health']);
            $tags=$em->getRepository(Tag::class)->findOneBy(['name'=>'Food']);
            $tags= json_encode([$tags->getName()]);
        foreach ($arr as $k=>$v) {
            $story = new Story();
            $story->setTitle($v['title'])
                    ->setBody($v['body'])
                    ->setCreatedOn($v['createdOn'])
                    ->setHits(25)
                    ->setUser(null)
                    ->setIsApproved(true)
                    ->setInsta('https://www.instagram.com/p/CKfklrOpPGl/?utm_source=ig_web_copy_link')
                    ->setCategory($category)
                    ->setTags($tags)
                    ->setUser($user)
                    ->setInstagramTitle('instagram Title')
                    ->setIsSaved(false)
                    ->setStatus('a');
            $em->persist($story);
             $storyImages = new \App\Entity\StoryImages();
            $storyImages->setFileData($data)
                    ->setFileName('no.jpg')
                    ->setFileType('image/jpg')
                    ->setIsPrimary(true)
                    ->setStory($story);
            $em->persist($storyImages);
        }
        $em->flush();
    }
}
