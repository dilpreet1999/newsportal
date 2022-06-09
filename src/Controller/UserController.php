<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Story;
use App\Form\TagType;
use App\Entity\Twitter;
use App\Entity\Youtube;
use App\Form\StoryType;
use App\Entity\Category;
use App\Entity\NewsChannel;
use App\Entity\StoryImages;
use App\Entity\FacebookWatch;
use App\Service\CommonService;
use Doctrine\ORM\QueryBuilder;
use App\Entity\ChannelRefference;
use App\Controller\BaseController;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use Symfony\Component\Form\FormError;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Column\NumberColumn;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Symfony\Component\Routing\Generator\UrlGenerator;
use function ReCaptcha\RequestMethod\file_get_contents;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserController extends BaseController
{

    /**
     * @Route("/user", name="user_index",methods={"GET","POST"})
     */
    public function index(Request $request, DataTableFactory $dtf)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $table = $dtf->create()
            ->add('id', NumberColumn::class, ['label' => 'id'])
            ->add('title', TextColumn::class, ['label' => 'Title',])
            ->add('tags', TextColumn::class, [
                'label' => 'Tags',
                'render' => function ($c, $v) {
                    return json_decode($v->getTags());
                }
            ])
            ->add('category', DateTimeColumn::class, ['label' => 'Category', 'render' => function ($c, $v) {

                return $v->getCategory()->getName();
            }])
            ->add('Action', TextColumn::class, ['label' => 'Action', 'render' => function ($c, $v) {

                $viewUrl = $this->generateUrl('story_view', ['id' => $v->getId(), 'slug' => $this->getSlug($v->getTitle())]);
                $tagUrl = $this->generateUrl('user_story_tag_add', ['id' => $v->getId()]);
                $editUrl = $this->generateUrl('user_story_edit', ['storyId' => $v->getId()]);
                $imagesUrl = $this->generateUrl('user_story_images_index', ['storyId' => $v->getId()]);
                $facebookUrl = $this->generateUrl('user_story_facebook_index', ['storyId' => $v->getId()]);
                $twitter = $this->generateUrl('user_story_twitter_index', ['storyId' => $v->getId()]);
                $youtube = $this->generateUrl('user_story_youtube_index', ['storyId' => $v->getId()]);
                $channelReference = $this->generateUrl('user_channel_refference_index', ['storyId' => $v->getId()]);

                $html = '';
                $html .= "<a class='ui button' href='$viewUrl'>show</a>";
                $html .= "<a class='ui button' href='$tagUrl'>Add Tag</a>";
                $html .= "<a class='ui button' href='$editUrl'>Edit</a>";
                $html .= "<a class='ui button' href='$imagesUrl'>Images</a>";
                $html .= "<a class='ui button' href='$facebookUrl'>Facebook</a>";
                $html .= "<a class='ui button' href='$twitter'>Twitter</a>";
                $html .= "<a class='ui button' href='$youtube'>Youtube</a>";
                $html .= "<a class='ui button' href='$channelReference'>Channel Referece</a>";
                return $html;
            }])
            //                ->addOrderBy('createdOn', DataTable::SORT_ASCENDING)
            ->createAdapter(ORMAdapter::class, [
                'entity' => Story::class,
                'query' => function (QueryBuilder $builder) use ($user) {
                    $builder
                        ->select('s')
                        ->from(Story::class, 's')
                        ->where('s.user = :user')
                        ->setParameter('user', $user->getId());
                },
            ])
            ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        };
        return $this->render('user/index.html.twig', [
            'story' => $table,
        ]);
        //        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/story/image/{id}/{size}/{filename}", name="user_story_image")
     */
    public function UserStoryImg(Request $request)
    {
        $id = $request->get('id');
        $size = $request->get('size');

        $em = $this->getEntityManager();

        $logo = $em->getRepository(Story::class)->findOneBy(['id' => $id]);

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
     * @Route("/user/tag/submit/{id}", name="user_tag_submit", methods={"GET","POST"})
     */
    public function usertagSubmit(Request $request)
    {
        $form = $_POST;
        $tags = $form['tags'];
        $x = json_encode($tags);
        $id = $request->get('id');
        $em = $this->getdoctrine()->getManager();
        $story = $em->getRepository(Story::Class)->findOneBy(['id' => $id]);
        $story->setTags($x);
        $em->persist($story);
        $em->flush();
        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/user/tag/index", name="user_story_tag_index", methods={"GET","POST"})
     */
    public function user_story_tag_index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tags = $em->getRepository(Tag::class)->findAll();
        return $this->render('user/tag/index.html.twig', ['tags' => $tags]);
    }

    /**
     * @Route("/user/tag/create", name="user_story_tag_create", methods={"GET","POST"})
     */
    public function user_story_tag_create(Request $request)
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $tagname = $form['name']->getData();
            $tagch = $entityManager->getRepository(Tag::class)->findBy(['name' => $tagname]);
            if ($tagch) {
                $form['name']->addError(new FormError('Tag ' . $tagname . ' is already exist in database'));
            }
            if ($form->isValid()) {
                $entityManager->persist($tag);
                $entityManager->flush();


                $entityManager->persist($tag);
                $entityManager->flush();
                return $this->redirectToRoute('user_story_tag_index');
            }
        }
        return $this->render('user/tag/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/user/add/tag/{id}", name="user_story_tag_add", methods={"GET","POST"})
     */
    public function useraddTag(Request $request)
    {
        $id = $request->get('id');
        $response = $this->render('user/tagAdd.html.twig', ['id' => $id]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/story/edit/{storyId}", name="user_story_edit")
     */
    public function user_story_edit(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $request->get('storyId')]);
        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $story->setTitle($form['title']->getData())
                ->setBody($form['body']->getData())
                ->setCategory($form['category']->getData())
                ->setInsta($form['insta']->getData())
                ->setCreatedOn(new DateTime('now', new DateTimeZone('Asia/Kolkata')));
            return $this->redirectToRoute('user_index');
        }
        $response = $this->render('user/edit.html.twig', ['form' => $form->createView()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/profile", name="user_profile", methods={"GET","POST"})
     */
    public function user_profile(Request $request, DataTableFactory $dtf)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $form = $this->createFormBuilder($user)
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('city')
            ->add('state')
            ->add('mobile')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmail($form['email']->getData())
                ->setName($form['name']->getData())
                ->setMobile($form['mobile']->getData())
                ->setCity($form['city']->getData())
                ->setState($form['state']->getData());
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('user_profile');
        }
        $response = $this->render('main/user_profile.html.twig', ['form' => $form->createView()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/youtube/video/{id}", name="youtube_video_show", methods={"GET","POST"})
     */
    public function youtube_video_show(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $youtube = $em->getRepository(Youtube::class)->findOneBy(['id' => $id]);
        return $this->render('main/youtubeShow.html.twig', ['yt' => $youtube]);
    }
    /**
     * @Route("/user/story/saved", name="user_saved_story", methods={"GET","POST"})
     */
    public function SavedStory(Request $request)
    {

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findBy(['user' => $user, 'isSaved' => true]);
        $storyImage = $em->getRepository(StoryImages::class)->findOneBy(['story' => $story, 'isPrimary' => true]);
        $response = $this->render('user/savedStory.html.twig', ['story' => $story, 'storyImage' => $storyImage]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/saved/story/{id}", name="user_set_saved_story", methods={"GET","POST"})
     */
    public function setSavedStory(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        if ($user) {
            $story->setIsSaved(true);
        } else {
            return $this->redirectToRoute('user_login', ['redirect' => $this->generateUrl('user_set_saved_story', ['id' => $id])]);
        }
        $em->persist($story);
        $em->flush();
        $this->addFlash('success', 'Story Saved in User Panel');
        $response = $this->redirectToRoute('story_view', ['id' => $id, 'slug' => $this->getSlug($story->getTitle())]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/twitter/index/{storyId}", name="user_story_twitter_index", methods={"GET","POST"})
     */
    public function story_twitter_section(Request $request, DataTableFactory $dtf)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $user = $this->getUser();
        $table = $dtf->create()
            ->add('id', NumberColumn::class, ['label' => 'id'])
            ->add('name', TextColumn::class, ['label' => 'Name',])
            ->add('url', TextColumn::class, ['label' => 'Url'])
            ->add('rank', NumberColumn::class, ['label' => 'Rank'])
            ->addOrderBy('rank', DataTable::SORT_ASCENDING)
            ->createAdapter(ORMAdapter::class, [
                'entity' => Twitter::class,
                'query' => function (QueryBuilder $builder) use ($story, $user) {
                    $builder
                        ->select('s')
                        ->from(Twitter::class, 's')
                        ->where('s.story = :story')
                        ->andWhere('s.user = :user')
                        ->setParameter('story', $story->getId())
                        ->setParameter('user', $user->getId());
                },
            ])
            ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        };
        $response = $this->render('user/twitter/index.html.twig', ['datatable' => $table, 'storyId' => $id]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/twitter/{storyId}/new", name="user_story_twitter_new", methods={"GET","POST"})
     */
    public function story_twitter_new(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $twitter = new Twitter();
        $form = $this->createFormBuilder($twitter)
            ->add('name')
            ->add('url')
            ->add('rank')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $twitter->setName($form['name']->getData())
                ->setUrl($form['url']->getData())
                ->setRank($form['rank']->getData())
                ->setStory($story)
                ->setUser($this->getUser());
            $em->persist($twitter);
            $em->flush();
            return $this->redirectToRoute('user_story_twitter_index', ['storyId' => $id]);
        }
        $response = $this->render('user/twitter/new.html.twig', ['form' => $form->createView(), 'storyId' => $id]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/youtube/index/{storyId}", name="user_story_youtube_index", methods={"GET","POST"})
     */
    public function story_youtube_section(Request $request, DataTableFactory $dtf)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $user = $this->getUser();
        $table = $dtf->create()
            ->add('id', NumberColumn::class, ['label' => 'id'])
            ->add('name', TextColumn::class, ['label' => 'Name',])
            ->add('url', TextColumn::class, ['label' => 'Url'])
            ->add('rank', NumberColumn::class, ['label' => 'Rank'])
            ->add('Action', TextColumn::class, ['label' => 'Action', 'render' => function ($c, $v) {
                $html = '';
                $deleteUrl = $this->generateUrl('user_story_delete_youtube_url', ['id' => $v->getId()]);
                $html .= "<a class='btn btn-primary' href='$deleteUrl'>Delete</a>";
                return $html;
            }])
            ->addOrderBy('rank', DataTable::SORT_ASCENDING)
            ->createAdapter(ORMAdapter::class, [
                'entity' => Youtube::class,
                'query' => function (QueryBuilder $builder) use ($story, $user) {
                    $builder
                        ->select('s')
                        ->from(Youtube::class, 's')
                        ->where('s.story = :story')
                        ->andWhere('s.user = :user')
                        ->setParameter('user', $user->getId())
                        ->setParameter('story', $story->getId());
                },
            ])
            ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        };
        $response = $this->render('user/youtube.html.twig', ['datatable' => $table, 'storyId' => $id]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/delete/youtube/{id}/", name="user_story_delete_youtube_url", methods={"GET","POST"})
     */
    public function delete_youtube_url(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $youtube = $em->getRepository(Youtube::class)->findOneBy(['id' => $id]);
        $em->remove($youtube);
        $em->flush();
        $response = $this->redirectToRoute('user_story_youtube_index', ['storyId' => $youtube->getStory()->getId()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/add/youtube/{storyId}", name="user_story_add_youtube_url", methods={"GET","POST"})
     */
    public function add_youtube_url(Request $request)
    {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $youtube = new Youtube();
        $form = $this->createFormBuilder($youtube)
            ->add('name')
            ->add('url')
            ->add('rank')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $youtube->setStory($story)
                ->setName($form['name']->getData())
                ->setUrl($form['url']->getData())
                ->setRank($form['rank']->getData())
                ->setUser($this->getUser());
            $em->persist($youtube);
            $em->flush();
            return $this->redirectToRoute('user_story_youtube_index', ['storyId' => $storyId]);
        }
        $response = $this->render('user/youtubeNew.html.twig', ['form' => $form->createView()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/story/youtube/delete/{id}", name="user_story_youtube_delete")
     */
    public function user_story_youtube_delete(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $youtube = $em->getRepository(Youtube::class)->findOneBy(['id' => $id]);
        $em->remove($youtube);
        $em->flush();
        $response = $this->redirectToRoute('user_story_youtube_index', ['storyId' => $youtube->getStory()->getId()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/story/new", name="user_story_new")
     */
    public function user_new_story(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
           
        
        $response = $this->render('user/newStory.html.twig', ['category' => $em->getRepository(Category::class)->findAll()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/story_submit_user", name="story_submit_user", methods={"GET","POST"})
     */
    public function story_submit_user()
    {
        $story = new Story();
        $form = $_POST;

        $em = $this->getDoctrine()->getManager();
        $tags = json_encode($form['tags']);
        $category = $em->getRepository(Category::class)->findOneBy(['name' => $form['category']]);
        ///  $fileData = $this->imageResize($fileData, $file['mimetype'], 600);
        $story
            ->setCategory($category)
            ->setBody($form['body'])
            ->setCreatedOn($this->myDate('now'))
            ->setStatus('a')
            ->setHits(25)
            ->setTitle($form['title'])
            //->setInstagramTitle($form['instagram_title'])
            //->setInsta($form['insta'])
            ->setIsSaved(false)
            ->setIsApproved(true)
            ->setSlug($this->getSlug($form['title']))
            ->setTags($tags);
            if ($form['insta'] != '') {
                $story->setInsta($form['insta']);
            }
            if ($form['insta_title'] != '') {
                $story->setInstagramTitle($form['insta_title']);
            }
        $em->persist($story);
        $em->flush();
        $this->addFlash('success', 'Story submitted successfully');

        return $this->redirectToRoute('user_index', ['type' => 'all']);
    }
    /**
     * @Route("/user/facebook/watch/index/{storyId}", name="user_story_facebook_index", methods={"GET","POST"})
     */
    public function story_facebook_watch_section(Request $request, DataTableFactory $dtf)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $user = $this->getUser();
        $table = $dtf->create()
            ->add('id', NumberColumn::class, ['label' => 'id'])
            ->add('name', TextColumn::class, ['label' => 'Name',])
            ->add('url', TextColumn::class, ['label' => 'Url'])
            ->add('rank', NumberColumn::class, ['label' => 'Rank'])
            ->add('Action', TextColumn::class, ['label' => 'Action', 'render' => function ($c, $v) {
                $html = '';
                $deleteUrl = $this->generateUrl('user_story_facebook_delete', ['id' => $v->getId()]);
                $html .= "<a class='btn btn-danger' href='$deleteUrl'>Delete</a>";
                return $html;
            }])
            ->addOrderBy('rank', DataTable::SORT_ASCENDING)
            ->createAdapter(ORMAdapter::class, [
                'entity' => FacebookWatch::class,
                'query' => function (QueryBuilder $builder) use ($story, $user) {
                    $builder
                        ->select('s')
                        ->from(FacebookWatch::class, 's')
                        ->where('s.story = :story')
                        ->andWhere('s.user = :user')
                        ->setParameter('story', $story->getId())
                        ->setParameter('user', $user->getId());
                },
            ])
            ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        };
        $response = $this->render('user/facebook/index.html.twig', ['datatable' => $table, 'storyId' => $id]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/facebook/watch/delete/{id}/new", name="user_story_facebook_delete", methods={"GET","POST"})
     */
    public function story_facebook_watch_delete(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $facebook = $em->getRepository(FacebookWatch::class)->findOneBy(['id' => $id]);
        $em->remove($facebook);
        $em->flush();
        $response = $this->redirectToRoute('user_story_facebook_index', ['storyId' => $facebook->getStory()->getId()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/facebook/watch/index/{storyId}/new", name="user_story_facebook_new", methods={"GET","POST"})
     */
    public function story_facebook_watch_new(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $facebook = new FacebookWatch();
        $form = $this->createFormBuilder($facebook)
            ->add('name')
            ->add('url')
            ->add('rank')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $facebook->setName($form['name']->getData())
                ->setUrl($form['url']->getData())
                ->setRank($form['rank']->getData())
                ->setStory($story)
                ->setUser($this->getUser());
            $em->persist($facebook);
            $em->flush();
            return $this->redirectToRoute('user_story_facebook_index', ['storyId' => $id]);
        }
        $response = $this->render('user/facebook/new.html.twig', ['form' => $form->createView(), 'storyId' => $id]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/register", name="user_new")
     */
    public function userAdd(Request $request, CommonService $cs)
    {
        $user = new User();
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, ['attr' => ['placeholder' => 'name']])
            ->add('email', EmailType::class, ['attr' => ['placeholder' => 'email']])
            ->add('state', TextType::class, ['attr' => ['placeholder' => 'state']])
            ->add('city', TextType::class, ['attr' => ['placeholder' => 'city']])
            ->add('mobile', TextType::class, ['attr' => ['placeholder' => 'mobile']])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $user
                ->setName($form['name']->getData())
                ->setCity($form['city']->getData())
                ->setState($form['state']->getData())
                ->setEmail($form['email']->getData())
                ->setPassword(sha1(rand(100000, 999999) . $form['email']->getData()))
                ->setEmailVerificationCode($form['email']->getData() . rand(100000, 999999) . time())
                ->setRoles(['ROLE_USER'])
                ->setMobile($form['mobile']->getData());

            $em->persist($user);
            $em->flush();
            $link = $this->generateUrl('user_verification', ['id' => $user->getId(), 'key' => $user->getEmailVerificationCode()], UrlGenerator::ABSOLUTE_URL);
            $html = $this->renderView('user/new_user.html.twig', ['user' => $user, 'link' => $link]);
            $cs->sendMail($user->getEmail(), 'Verification Email', $html);
            $token = new UsernamePasswordToken($user, null, 'user_area', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $this->container->get('session')->set('_security_main', serialize($token));
            return $this->redirectToRoute('user_new_success', ['id' => $user->getId()]);
        }
        $response = $this->render('user/userAdd.html.twig', ['form' => $form->createView()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/success/", name="user_new_success")
     */
    public function newUserSuccess(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $user = $em->getRepository(User::class)->findOneBy(['id' => $user->getId()]);
        $response = $this->render('user/success.html.twig', ['user' => $user]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/verification/{id}/{key}", name="user_verification")
     */
    public function newVerification(Request $request, CommonService $cs)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $key = $request->get('key');
        //$user = $em->getRepository(User::class)->findOneBy(['id' => $id]);
        $user = $em->getRepository(User::class)->findOneBy(['id' => $id, 'emailVerificationCode' => $key]);
        //$user = new User();
        $pass = $id . rand(100000, 999999) . '@';
        if ($user) {
            $user
                ->setPassword(sha1($pass))
                ->setEmailVerificationCode(sha1($user->getId() . rand(1000000, 9999999) . time()));
            $em->persist($user);
            $em->flush();
            $cs->sendMail($user->getEmail(), 'Your Password Here', $pass);

            // $user = //Handle getting or creating the user entity likely with a posted form
            $token = new UsernamePasswordToken($user, null, 'user_area', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $this->container->get('session')->set('_security_main', serialize($token));
            return $this->redirectToRoute('homepage');
        }
        $response = $this->render('user/verification_failed.html.twig');
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/password/reset", name="user_password_reset", methods={"GET","POST"})
     */
    public function user_passwordreset(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userEntity = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if ($userEntity) {
            $form = $this->createFormBuilder()
                ->add('password', PasswordType::class, ['label' => 'New Password'])
                ->add('conformPassword', PasswordType::class, ['label' => 'Repeat Password'])
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $pass1 = $form['password']->getData();
                $pass2 = $form['conformPassword']->getData();
                if ($pass1 == $pass2) {
                    $userEntity->setPassword(sha1($pass1));
                    $em->persist($userEntity);
                    $em->flush();
                } else {
                    $form->addError('Password Not Match');
                }
                $token = new UsernamePasswordToken($user, null, 'user_area', $user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));
                return $this->redirectToRoute('homepage');
            }
        } else {
            $this->addFlash('danger', 'user Not Exist');
        }
        $response = $this->render('user/passwordReset.html.twig', ['form' => $form->createView()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/reference/{storyId}", name="user_channel_refference_index", methods={"GET"})
     */
    public function user_channel_reference(Request $request): Response
    {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $channelRefference = $em->getRepository(ChannelRefference::class)->findBy(['story' => $story]);
        $response = $this->render('user/channel_reference/index.html.twig', [
            'channel_refferences' => $channelRefference,
            'storyId' => $storyId
        ]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/reference/delete/{id}", name="user_channel_refference_delete", methods={"GET","POST"})
     */
    public function user_channel_reference_delete(Request $request): Response
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $channelRefference = $em->getRepository(ChannelRefference::class)->findOneBy(['id' => $id]);
        $em->remove($channelRefference);
        $em->flush();
        $response = $this->redirectToRoute('user_channel_refference_index', ['storyId' => $channelRefference->getStory()->getId()]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/reference/new/{storyId}", name="user_channel_refference_new", methods={"GET","POST"})
     */
    public function user_channel_reference_new(Request $request): Response
    {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $channelRefference = $em->getRepository(ChannelRefference::class)->findBy(['story' => $story]);

        $form = $this->createFormBuilder()
            ->add('title')
            ->add('name', EntityType::class, ['class' => NewsChannel::class, 'choice_label' => 'name'])
            ->add('website')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $referenceChannel = new ChannelRefference();

            $entityManager = $this->getDoctrine()->getManager();
            $referenceChannel
                ->setTitle($form['title']->getData())
                ->setName($form['name']->getData())
                ->setWebsite($form['website']->getData())
                ->setPublishedOn(new DateTime('now', new DateTimeZone('Asia/Kolkata')))
                ->setStory($story);
            $entityManager->persist($referenceChannel);
            $entityManager->flush();

            return $this->redirectToRoute('user_channel_refference_index', ['storyId' => $storyId]);
        }

        $response = $this->render('user/channel_reference/new.html.twig', [
            'channel_refference' => $channelRefference,
            'form' => $form->createView(),
            'storyId' => $storyId
        ]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/story/images/{storyId}", name="user_story_images_index", methods={"GET"})
     */
    public function userStoryImages(Request $request): Response
    {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $storyImages = $em->getRepository(StoryImages::class)->findBy(['story' => $story]);
        $response = $this->render('user/storyImages/index.html.twig', [
            'story_images' => $storyImages,
            'storyId' => $storyId
        ]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("user/{id}/set/primary/{storyId}/", name="user_story_images_set_primary", methods={"GET","POST"})
     */
    public function setPrimary(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $storyId = $request->get('storyId');
        $onePhoto = $em->getRepository(StoryImages::class)->findOneBy(['id' => $request->get('id')]);
        $story = $onePhoto->getStory();

        $storyImages = $em->getRepository(StoryImages::class)->findBy(['story' => $story]);
        foreach ($storyImages as $si) {
            $oneImage = $em->getRepository(StoryImages::class)->findOneBy(['id' => $si->getId()]);
            $oneImage->setIsPrimary(false);
            $em->persist($oneImage);
        }
        $storyImage = $em->getRepository(StoryImages::class)->findOneBy(['id' => $request->get('id')]);
        $storyImage->setIsPrimary(true);
        $em->persist($storyImage);
        $em->flush();
        return $this->redirectToRoute('user_story_images_index', ['storyId' => $storyId]);
    }

    /**
     * @Route("/user/story/images/new/{id}", name="user_story_images_new", methods={"GET","POST"})
     */
    public function userStoriesImagesNew(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $storyImage = new StoryImages();
        $storyId = $request->get('id');
        $story = $entityManager->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $form = $this->createFormBuilder()
            ->add('upload', FileType::class, ['mapped' => false])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $file = $form['upload']->getData();
            $file = $this->getUploadedFileInfo($file);
            $fileData = file_get_contents($file['realPath']);
            $storyImage->setFileData($fileData)
                ->setFileName($file['filename'])
                ->setFileType($file['mimetype'])
                ->setStory($story)
                ->setIsPrimary(false);
            $entityManager->persist($storyImage);
            $entityManager->flush();

            return $this->redirectToRoute('user_story_images_index', ['storyId' => $storyId]);
        }

        $response = $this->render('user/storyImages/new.html.twig', [
            'story_image' => $storyImage,
            'form' => $form->createView(),
            'storyId' => $storyId
        ]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/story/images/{id}/edit/{storyId}/", name="user_story_images_edit", methods={"GET","POST"})
     */
    public function userStoryImageEdit(Request $request, StoryImages $storyImage): Response
    {
        $em = $this->getDoctrine()->getManager();
        $storyId = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $storyImage = $em->getRepository(StoryImages::class)->findOneBy(['id' => $request->get('id')]);
        $form = $this->createFormBuilder($storyImage)
            ->add('upload', FileType::class, ['mapped' => false])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['upload']->getData();
            $file = $this->getUploadedFileInfo($file);
            $fileData = file_get_contents($file['realPath']);
            $storyImage->setFileData($fileData)
                ->setFileName($file['filename'])
                ->setFileType($file['mimetype'])
                ->setStory($story);
            $em->flush();

            return $this->redirectToRoute('user_story_images_index', ['storyId' => $storyId]);
        }

        $response = $this->render('user/storyImages/edit.html.twig', [
            'story_image' => $storyImage,
            'form' => $form->createView(),
            'storyId' => $storyId
        ]);
        return $this->sendEtagResponse($response, $request);
    }

    /**
     * @Route("/user/story/{id}/delete/{storyId}", name="user_story_images_delete", methods={"GET","POST"})
     */
    public function delete(Request $request): Response
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $storyImage = $em->getRepository(StoryImages::class)->findOneBy(['id' => $id]);
        $em->remove($storyImage);
        $em->flush();

        $response = $this->redirectToRoute('user_story_images_index', ['storyId' => $request->get('storyId')]);
        return $this->sendEtagResponse($response, $request);
    }
}
