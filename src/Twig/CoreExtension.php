<?php

namespace App\Twig;

use App\Entity\Category;
use App\Entity\Story;
use App\Entity\StoryImages;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function GuzzleHttp\json_decode;

class CoreExtension extends AbstractExtension
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
            new TwigFilter('slug', [$this, 'slug']),
            new TwigFilter('getInstaId', [$this, 'getInstaId']),
            new TwigFilter('getYoutubeId', [$this, 'getYoutubeId']),
            new TwigFilter('getTwitterId', [$this, 'getTwitterId']),
            new TwigFilter('getfacebookWatchId', [$this, 'getfacebookWatchId']),
            new TwigFilter('addbr', [$this, 'addbr']),
            new TwigFilter('getStoryImg', [$this, 'getStoryImg']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [$this, 'doSomething']),
            new TwigFunction('getCategory', [$this, 'getCategory']),
            new TwigFunction('getTopCategory', [$this, 'getTopCategory']),
            new TwigFunction('sideStory', [$this, 'sideStory']),
            new TwigFunction('getTags', [$this, 'getTags']),
            new TwigFunction('getAllTags', [$this, 'getAllTags']),
            new TwigFunction('wordWrap', [$this, 'wordWrap']),
            new TwigFunction('tags', [$this, 'tags']),
            new TwigFunction('getStoryImg', [$this, 'getStoryImg']),
            new TwigFunction('getStories', [$this, 'getStories']),
            new TwigFunction('mostPopular', [$this, 'mostPopular']),
            new TwigFunction('featured', [$this, 'featured']),
            new TwigFunction('cat', [$this, 'cat']),
            new TwigFunction('getLastCat', [$this, 'getLastCat']),
            new TwigFunction('getIsTopCat', [$this, 'getIsTopCat']),
            

         
            
            

            
            

        ];
    }

    public function doSomething($value)
    {
        // ...
    }

  
public function featured(){
    $em = $this->em;
    $sql = "SELECT * FROM story Group By story.id ORDER BY story.id = 'DESC' LIMIT 3;
    ";
    $query =$em->getConnection()->prepare($sql);
    $query->execute();
    return $query->fetch();
}
    public function getStoryImg($id)
    {
        $em = $this->em;
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $storyImage = $em->getRepository(StoryImages::class)->findOneBy(['story' => $story]);
        return $storyImage;
    }

    public function addbr($value)
    {
        $processed_data = explode('.', $value);
        return $processed_data[0];
    }
    public function tags($story)
    {
        return json_decode($story->getTags());
    }
    public function wordWrap($string)
    {


        $text = str_word_count($string) > 80 ? substr($string, 0, 200) . "..." : $string;
        return $text;
    }

    public function getTags($story)
    {
        $tags = '';
        foreach ($story as $s) {
            $tags = json_decode($s->getTags());
        }
        return $tags;
    }
    public function getAllTags()
    {
        $em = $this->em;
        $tags = $em->getRepository(Tag::class)->findAll();
        return $tags;
    }
    public function getIsTopCat(){
        $em = $this->em;
       return $em->getRepository(Category::class)->findBy(['isTop' => true]);
    }

    public function getTopCategory()
    {
        $category = $this->em->getRepository(Category::class)->findBy(['isTop' => true]);
        return $category;
    }
    public function getCategory()
    {
        $category = $this->em->getRepository(Category::class)->findBy(['isTop' => false]);
        return $category;
    }


    public static function slug($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function getInstaId($insta)
    {
        $insta = explode('p/', $insta);
        return $insta[1];
    }

    public function getYoutubeId($youtubeUrl)
    {
        $youtubeId = explode('?v=', $youtubeUrl);
        return $youtubeId[1];
    }

    public function getfacebookWatchId($facebookUrl)
    {
        $facebookId = explode('?v=', $facebookUrl);
        return $facebookId[1];
    }

    public function getTwitterId($twitter)
    {
        $twitter = explode('status/', $twitter);
        return $twitter[1];
    }

    public function sideStory()
    {
        $em = $this->em;
        $limit = 5;
        $query = $em->createQuery("SELECT S
                            FROM App:Story S
                            ORDER BY S.hits DESC ")->setMaxResults($limit);

        $profilis = $query->getResult();
        return $profilis;
    }
    public function getStories(){
        $em = $this->em;
        $story = $em->createQueryBuilder()
        ->select('s')
        ->from(Story::class,'s')
        ->orderBy('s.id','DESC')
        ->setMaxResults(4)
        ->getQuery()
        ->getResult();
        return $story;
    }

    public function cat(){
        $em= $this->em;

        $cat = "SELECT * FROM category  LIMIT 0,7";
        $cat = $this->em->getConnection()->prepare($cat);
        $cat->execute();
        return $cat->fetchAll();
        }
        public function getLastCat(){
            
        $em= $this->em;

        $cat = "SELECT * FROM category LIMIT 5,100";
        $cat = $this->em->getConnection()->prepare($cat);
        $cat->execute();
        return $cat->fetchAll();
        }
    public function mostPopular(){
        $em = $this->em;
        $category = $em->getRepository(Category::class)->findOneBy(['name'=>'Most Popular']);
        $story = $em->createQueryBuilder()
        ->select('s')
        ->from(Story::class,'s')
        ->where('s.category = :category')
        ->setParameter('category',$category)
        ->orderBy('s.id','DESC')
        ->setMaxResults(4)
        ->getQuery()
        ->getResult();
        return $story;
    }
}
