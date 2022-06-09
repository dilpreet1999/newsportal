<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Menu;
use App\Entity\Page;
use App\Entity\User;
use App\Entity\Story;
use App\Entity\Slider;
use App\Entity\Comment;
use App\Entity\Twitter;
use App\Entity\Youtube;
use App\Entity\Category;
use App\Entity\Feedback;
use ReCaptcha\ReCaptcha;
use App\Entity\NewsChannel;
use App\Entity\StoryImages;
use App\Entity\FacebookWatch;
use App\Entity\ChannelRefference;
use App\Controller\BaseController;
use function GuzzleHttp\json_decode;

use function GuzzleHttp\json_encode;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MainController extends BaseController
{

    /**
     * @Route("/", name="homepage", methods={"GET","POST"})
     */
    public function index(Request $request, PaginatorInterface $paginator,EntityManagerInterface $em)
    {

        //$em = $this->getDoctrine()->getManager();
        $searchQuery = $request->get('q');
        $mostPopularCat = $em->getRepository(Category::class)->findOneBy(['name'=>'Most Popular']);
        $mostPopular = $em->createQuery("SELECT S    FROM App:Story S
        WHERE S.isApproved = true AND S.category = '".$mostPopularCat->getId()."'
        ORDER BY S.hits DESC")->setMaxResults(7)->getResult();
        $scienceCategory = $em->getRepository(Category::class)->findOneBy(['name'=>'Science']);
        $foorPost =$em->createQuery("SELECT S    FROM App:Story S
        WHERE S.isApproved = true AND S.category = '".$scienceCategory->getId()."'
        ORDER BY S.hits DESC")->setMaxResults(4)->getResult();
        $slider = 'SELECT S    FROM App:Story S
        WHERE S.isApproved = true
        ORDER BY S.createdOn DESC';
        $slider = $em->createQuery($slider)->setMaxResults(3)->getResult();
        $getOneStory = $em->getRepository(Story::class)->findOneBy([],['id'=>'DESC']);
        
        $latestQuery = "SELECT story.id as id,story.title as title, category.name as category,user.name as user,story.slug as slug,story.body as body FROM story inner join category on category.id = story.category_id inner join user  on user.id = story.user_id WHERE created_on> now() -  interval 7 day AND is_approved = true Group By story.id LIMIT 2 ";
        $latestQuery = $em->getConnection()->prepare($latestQuery);
        $latestQuery->execute();
        $latestQuery->fetch();

        $latestQuerySmall = "SELECT * FROM story inner join category on category.id = story.category_id WHERE created_on> now() -  interval 7 day AND is_approved = true ORDER BY RAND() LIMIT 4";
        $latestQuerySmall = $this->getDoctrine()->getConnection()->prepare($latestQuerySmall);
        $latestQuerySmall->execute();
        $latestQuerySmall->fetch();
        $trendingCat = $em->getRepository(Category::class)->findOneBy(['name' => 'Trending']);
        $trendingStories = $em->createQueryBuilder()
            ->select('tr')
            ->from(Story::class, 'tr')->groupBy('tr.id')->where('tr.category =:category')->setParameter('category', $trendingCat)->setMaxResults(5)->getQuery()->getResult();

        $mostViewd= $em->createQuery("SELECT S    FROM App:Story S
        WHERE S.isApproved = true
        ORDER BY S.hits DESC")->setMaxResults(5)->getResult();
        $Arts_CultureCat=$em->getRepository(Category::class)->findOneBy(['name'=>'Arts & Culture']);
        $art_culture = $em->getRepository(Story::class)->findOneBy(['category'=>$Arts_CultureCat],['id'=>'DESC']);
        $art_cultureSmall = $this->getDoctrine()->getConnection()->prepare("SELECT * FROM story inner join category on  category.id = story.category_id WHERE is_approved = true AND story.category_id = '".$Arts_CultureCat->getId()."' ORDER BY RAND() LIMIT 3");
        $art_cultureSmall->execute();
        $art_cultureSmall->fetch();

        $natureCat=$em->getRepository(Category::class)->findOneBy(['name'=>'Nature']);
        $nature = $em->getRepository(Story::class)->findOneBy(['category'=>$natureCat],['id'=>'DESC']);
        $natureSmall = $this->getDoctrine()->getConnection()->prepare("SELECT * FROM story inner join category on  category.id = story.category_id WHERE is_approved = true AND story.category_id = '".$natureCat->getId()."' ORDER BY RAND()LIMIT 3");
       $natureSmall->execute();
       $natureSmall->fetch();
        $response = $this->render('main/index.html.twig', [
            // 'paginator' => $pagination,
            'slider' => $slider,
            'weekData' => $latestQuery,
            'trending' => $trendingStories,
            'mostViews'=>$mostViewd,
            'arts_culture'=>$art_culture,
            'nature'=>$nature,
            'natureSmall'=>$natureSmall,
            'arts_cultureSmall'=>$art_cultureSmall,
            'getOneStory'=>$getOneStory,
            'fourPost'=>$foorPost,
            'mostPopular'=>$mostPopular,
            'latestQuerySmall'=>$latestQuerySmall
        ]);
        return $this->sendEtagResponse($response, $request);
    }

    public function menu_show(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository(Menu::class)->findAll();
        $nav = [];
        foreach ($menu as $k => $v) {
            $arr = [];
            $page = $em->getRepository(Page::class)->findBy(['menu' => $v->getId()]);
            $nav[$k]['menu'] = $v;
            $pcount = count($page);
            $arr[$k]['count'] = $pcount;
            $arr[$k]['menu'] = $page;
            if ($pcount == 0) {
                $nav[$k]['pages'] = null;
                $nav[$k]['sub'] = null;
            } elseif ($pcount == 1) {
                $nav[$k]['pages'] = $page;
                $nav[$k]['sub'] = FALSE;
            } else {
                $nav[$k]['pages'] = $page;
                $nav[$k]['sub'] = true;
            }
        }
        return $this->render('main/footer.html.twig', ['nav' => $nav]);
    }

    /**
     * @Route("/page/{id}.{slug}", name="website_page_show")
     */
    public function page_show(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $page = $em->getRepository(Page::class)->findOneBy(['id' => $id]);
        if ($page->getUrl()) {
            return $this->redirect($page->getUrl(), 301);
        }
        return $this->render('main/pageShow.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/css/style.css", name="css")
     */
    public function css()
    {
        $css = $this->renderView('main/style.css');
        return $response = new Response($css, 200, ['content-type' => 'text/css']);
    }

    /**
     * @Route("/ajax/select", name="tag_ajax_select", methods={"GET","POST"})
     */
    public function tagAjaxSelect(Request $request,EntityManagerInterface $em): Response
    {
        $q = $request->get('q');
        $tags = $em->getRepository(Tag::class)->findByQuery($q);

        $array = array();

        foreach ($tags as $v) {
            $array[] = array('id' => $v->getName(), 'text' => $v->getName());
        }
        $result['results'] = $array;
        return new JsonResponse($result);
    }

    /**
     * @Route("/feedback/submit", name="feedback_submit", methods={"GET","POST"})
     */
    public function feedbackSubmit(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $_POST;
        $recaptcharesponse = $form['g-recaptcha-response'];
        $recaptcha = new ReCaptcha('6LdSP6EaAAAAAAEYn-iu4So-4_6vzTuNY1YKFAJ7');
        $resp = $recaptcha->verify($recaptcharesponse, $request->getClientIp());

        $feedback = new Feedback();

        if ($resp->isSuccess()) {
            print_r($resp);
            die;
            $feedback->setEmail($form['email'])
                ->setName($form['name'])
                ->setMessage($form['message']);
        } else {
            $errors = $resp->getErrorCodes();
        }

        $em->persist($feedback);
        $em->flush();
        $this->addFlash('success', 'Your  Feedback is Submitted!');
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/tag/{tag}", name="tag_search", options={"expose"=true})
     */
    public function tags(Request $request, PaginatorInterface $paginator,ManagerRegistry $mr)
    {
        $em = $mr->getManager();
        $q = $request->get('tag');
        $q = strtolower($q);
        $posts = $em->getRepository(Story::class)->findByQuery($q);
        if ($posts) {
            $pagination = $paginator->paginate(
                $posts, /* query NOT result */
                $request->query->getInt('page', 1), /* page number */
                10 /* limit per page */
            );
        } else {
            return $this->render('main/404.html.twig');
        }
        $response = $this->render('main/tagSearch.html.twig', [
            'posts' => $pagination,
            'val' => $q,
        ]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/search/", name="ajax_search", methods={"GET"})
     */
    public function searchAction(Request $request,EntityManagerInterface $em,PaginatorInterface $paginator)
    {

        $q = $request->get('q');
        $q = strtolower($q);

        $entities = $em->getRepository(Story::class)->findByQuery($q);

        $pagination = $paginator->paginate(
            $entities, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

               return $this->render('main/search.html.twig', [
                           'res' => $pagination,
                           'val' => $q]);
    }

    public function getRealEntities($entities)
    {

        foreach ($entities as $entity) {
            $realEntities[$entity->getId()] = $entity->getTitle();
        }

        return $realEntities;
    }

    /**
     * @Route("/ajax/search", name="ajax_live_search",methods={"GET"})
     */
    public function search(Request $request, PaginatorInterface $paginator,EntityManagerInterface $mr)
    {
        $em = $mr->em;
        $q = $request->get('q');

        $q = strtolower($q);

        $story = $em->getRepository(Story::class)->findByQuery($q);

        $pagination = $paginator->paginate(
            $story, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        $response = $this->render('main/search.html.twig', [
            'res' => $pagination,
            'val' => $q
        ]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/cat/{slug}", name="story_by_category", methods={"GET","POST"})
     */
    public function storyByCategory(Request $request, PaginatorInterface $paginator,EntityManagerInterface $em)
    {
        $id = $request->get('slug');


        $category = $em->getRepository(Category::class)->findOneBy(['slug' => $request->get('slug')]);
        $mostViewd= $em->createQueryBuilder()
        ->select('mv')
        ->From(Story::class,'mv')
        ->orderBy('mv.hits','DESC')
        ->setMaxResults(6)
        ->getQuery()
        ->getResult();

        $story = $em->getRepository(Story::class)->findBy(['category' => $category]);

        $pagination = $paginator->paginate(
            $story, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        $response = $this->render('main/storyList.html.twig', ['story' => $pagination, 'category' => $category,'mostViewed'=>$mostViewd]);

        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/story/image/{id}/{size}/{filename}", name="story_image")
     */
    public function storyImg(Request $request)
    {
        $id = $request->get('id');
        $size = $request->get('size');

        $em = $this->getEntityManager();

        $logo = $em->getRepository(StoryImages::class)->findOneBy(['id' => $id]);

        $fileData = $logo->getFileData();
        $fileType = $logo->getFileType();
        $data = '';
        while (!feof($logo->getFileData())) {
            $data .= fread($logo->getFileData(), 1024);
        }
        rewind($logo->getFileData());

        $data = $this->imageResize($data, $fileType, $size);

        $response = new Response($data, 200, ['content-type' => $fileType]);
        return $this->sendEtagResponse($response, $request, true);
    }

    /**
     * @Route("/news/channel/icon/image/{id}/{size}/{name}/", name="news_Channel_Image_show")
     */
    public function newschannelImage(Request $request)
    {
        $id = $request->get('id');
        $size = $request->get('size');

        $em = $this->getEntityManager();

        $logo = $em->getRepository(NewsChannel::class)->findOneBy(['id' => $id]);

        $fileData = $logo->getFileData();
        $fileType = $logo->getFileType();
        $data = '';
        while (!feof($logo->getFileData())) {
            $data .= fread($logo->getFileData(), 1024);
        }
        rewind($logo->getFileData());

        //        $data = $this->imageResizead($data, $fileType, $size);
        $response = new Response($data, 200, ['content-type' => $fileType]);
        return $this->sendEtagResponse($response, $request, true);
    }

    public function addHit($story)
    {
        //        print_r($story);
        $em = $this->getDoctrine()->getManager();
        $hit = intval($story->getHits() + 1);
        $story->setHits($hit);
        $em->persist($story);
        $em->flush();
        return true;
    }

    /**
     * @Route("/story/show/{slug}", name="story_view", methods={"GET","POST"})
     */
    public function storyShow(Request $request)
    {
        $id = $request->get('slug');

        $em = $this->getEntityManager();
        $story = $em->getRepository(Story::class)->findOneBy(['slug' => $id]);
        $storyImages = $em->getRepository(StoryImages::class)->findBy(['story' => $story, 'isPrimary' => false]);
        $youtube = $em->getRepository(Youtube::class)->findBy(['story' => $story]);
        $facebook = $em->getRepository(FacebookWatch::class)->findBy(['story' => $story]);
        $twitter = $em->getRepository(Twitter::class)->findBy(['story' => $story]);
        $hits = $this->addHit($story);
        $channelReference = $em->getRepository(ChannelRefference::class)->findBy(['story' => $story]);
        $comment = $em->getRepository(Comment::class)->findBy(['story' => $story,'isApprove'=>true]);
        $response = $this->render('main/storyShow.html.twig', [
            'facebook' => $facebook, 'youtube' => $youtube, 'story' => $story, 'comment' => $comment,
            'tags' => json_decode($story->getTags()), 'twitter' => $twitter,
            'channelReference' => $channelReference,
            'storyImages' => $storyImages
        ]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/recent/post/", name="recent", methods={"GET","POST"})
     */
    public function recentPost(Request $request)
    {
        $story = $this->getRandomStoryPage();
        $response = $this->render('main/recentPost.html.twig', ['story' => $story]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/image/{id}/{filename}", name="scroller_image",methods={"GET","POST"})
     */
    public function scrollerImage(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');

        $image = $em->getRepository(Slider::class)->findOneBy(['id' => $id]);

        $data = '';

        while (!feof($image->getFileData())) {
            $data .= fread($image->getFileData(), 1024);
        }
        rewind($image->getFileData());
        return new Response($data, 200, array('content-type' => $image->getFileType()));
    }

    /**
     * @Route("/story/images/{id}/{size}/{name}", name="story_images_view",methods={"GET","POST"})
     */
    public function storyImages(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $size = $request->get('size');

        $image = $em->getRepository(StoryImages::class)->findOneBy(['id' => $id]);

        $data = '';

        while (!feof($image->getFileData())) {
            $data .= fread($image->getFileData(), 1024);
        }
        $data = $this->imageResize($data, $image->getFileType(), $size);
        rewind($image->getFileData());
        return new Response($data, 200, array('content-type' => $image->getFileType()));
    }

    //    public function comment(Request $request, $story) {
    //        $comment = new Comment();
    //        $form = $this->createFormBuilder($comment)
    //                ->add('message', TextareaType::class)
    //                ->getForm();
    //        $form->handleRequest($request);
    //        if ($form->isSubmitted() && $form->isValid()) {
    //
    //            $em = $this->getDoctrine()->getManager();
    //            $comment->setMessage($form['message']->getData())
    //                    ->setUser($this->getUser())
    //                    ->setStory($story);
    //            $em->persist($comment);
    //            $em->flush();
    //        }
    //        return $this->render('main/comment.html.twig', ['form' => $form->createView()]);
    //    }4
    /**
     * @Route("/comment_submit", name="comment_submit" , methods={"POST","GET"})
     */
    public function comment_submit(): Response
    {
        $em= $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id'=>$_POST['story_id']]);
        $comment= new Comment();
        $comment->setCreatedOn($this->myDate())
        ->setIsApprove(false)
        ->setMessage($_POST['message'])
        ->setUser($this->getUser())
        ->setStory($story);
        $em->persist($comment);
        $em->flush();
        return new Response('success');
    }
    /**
     *@Route("/modal/login", name="modal_login" ,methods={"POST","GET"})
     */
    public function modal_login(AuthenticationUtils $auth){

        $error = $auth->getLastAuthenticationError();
        $username = $auth->getLastUsername();

        return $this->render('main/modal-login.html.twig',['error'=>$error,'lastUser'=>$username]);
    }
    /**
     * @Route("/hf_login/submit", name="hf_login_submit" , methods={"POST"})
     */
    public function hf_login(Request $request): Response
    {
        $em= $this->getDoctrine()->getManager();
        $password = sha1($_POST['_password']);
        $user = $em->getRepository(User::class)->findOneBy(['email'=>$_POST['_username'],'password'=>$password]); 
        
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));
            $path = $request->get('redirect');
           
            return new Response($path);     
        
    }
/**
 * @Route("/about-us", name="about_us" , methods={"GET"})
 */
public function about_us(Request $request): Response
{
    return $this->render('main/about-us.html.twig');
    
}
/**
 * @Route("/contact-us", name="contact_us" , methods={"GET","POST"})
 */
public function contact_us(Request $request): Response
{
    return $this->render('main/contact-us.html.twig');
}
/**
 * @Route("/contact_us_submit", name="contact_us_submit" , methods={"POST","GET"})
 */
public function contact_us_submit(EntityManagerInterface $em): Response
{
   $feedback = new Feedback();
   $feedback->setName($_POST['firstname'].$_POST['lastname']);
   $feedback->setEmail($_POST['email']);
   $feedback->setMessage($_POST['message']);
   $feedback->setPhone($_POST['phone']);
   $feedback->setCreatedOn($this->myDate());
   $em->persist($feedback);
   $em->flush();
return new Response('Thanks For Contacting Us',200);
}


}