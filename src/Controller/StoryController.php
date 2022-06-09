<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Story;
use App\Entity\Twitter;
use App\Entity\Youtube;
use App\Form\StoryType;
use App\Entity\Category;
use App\Entity\FacebookWatch;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Symfony\Component\HttpFoundation\JsonResponse;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;

/**
 * @Route("admin/story")
 */
class StoryController extends BaseController
{
     /**
     * @Route("/create_slug", name="create_slug")
     */
    public function create_slug(){
        $em=$this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findAll();
        foreach($story as $s){
            $s->setSlug($this->getSlug($s->getTitle()));
            $em->persist($s);
        }
        $em->flush();
        die;
    }

    /**
     * @Route("/{type}.html", name="story_index")
     */
    public function storyIndex(DataTableFactory $dtf, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $type = $request->get('type');

        $table = $dtf->create()
            ->add('id', NumberColumn::class, ['label' => 'id'])
            ->add('createdOn', DateTimeColumn::class, ['label' => 'Date', 'format' => 'd-m-Y'])
            ->add('title', TextColumn::class, ['label' => 'Title'])
            ->add('isApprove', BoolColumn::class, ['label' => 'status', 'render' => function ($c, $v) {
                if ($v->getStatus() == 'a' && $v->getIsApproved() == true) {
                    $disApproveUrl = $this->generateUrl('story_set_approve', ['id' => $v->getId()]);

                    return "<a class='badge badge-success' href='$disApproveUrl'>DisApprove</a>";
                } else {
                    $html = '';
                    $approveUrl = $this->generateUrl('story_set_approve', ['id' => $v->getId()]);
                    $html .= "<a class='badge badge-danger' href='$approveUrl'>Not Approve</a>";
                    return $html;
                }
            }])
            ->add('Action', TextColumn::class, [
                'label' => 'Action', 'render' => function ($c, $v) {
                    $showUrl = $this->generateUrl('story_show', ['id' => $v->getid()]);
                    $referenceUrl = $this->generateUrl('channel_refference_index', ['storyId' => $v->getid()]);
                    $editUrl = $this->generateUrl('story_edit', ['id' => $v->getId()]);
                    $youtubeUrl = $this->generateUrl('story_youtube_index', ['storyId' => $v->getId()]);
                    $twitterUrl = $this->generateUrl('story_twitter_index', ['storyId' => $v->getId()]);
                    $facebookUrl = $this->generateUrl('story_facebook_index', ['storyId' => $v->getId()]);
                    $storyImageUrl = $this->generateUrl('story_images_index', ['storyId' => $v->getId()]);
                    $html = '<div class="btn btn-group">';
                    $html .= "<a style='margin:2px;' class='btn btn-success btn-sm' href='$showUrl'><i class='fa fa-eye'></i></a>";
                    $html .= "<a style='margin:2px;' class='btn btn-primary btn-sm' href='$editUrl'><i class='fa fa-pencil-alt'></i></a>";
                    $html .= "<a style='margin:2px;' class='btn btn-primary btn-sm' href='$referenceUrl'><i class='fas fa-address-book'></i></a>";
                    $html .= "<a style='margin:2px;' class='btn btn-primary btn-sm' href='$youtubeUrl'><i class='fab fa-youtube'></i></a>";
                    $html .= "<a style='margin:2px;' class='btn btn-primary btn-sm' href='$facebookUrl'><i class='fab fa-facebook'></i></a>";
                    $html .= "<a style='margin:2px;' class='btn btn-primary btn-sm' href='$twitterUrl'><i class='fab fa-twitter-square'></i></a>";
                    $html .= "<a style='margin:2px;' class='btn btn-primary btn-sm' href='$storyImageUrl'><i class='fas fa-image'></i></a>";
                    $html .= '</div>';
                    return $html;
                }
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Story::class,
                'query' => function (QueryBuilder $builder) use ($type) {
                    if ($type == 'all') {
                        $builder
                            ->select('s')
                            ->from(Story::class, 's');
                    } elseif ($type == 'a') {
                        $builder
                            ->select('s')
                            ->from(Story::class, 's')
                            ->where('s.status = :status')
                            ->setParameter('status', $type);
                    } else {
                        $builder
                            ->select('s')
                            ->from(Story::class, 's')
                            ->where('s.status = :status')
                            ->setParameter('status', $type);
                    }
                },
            ])
            ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('story/index.html.twig', ['datatable' => $table]);
    }

    /**
     * @Route("/set/approve/{id}", name="story_set_approve", methods={"GET","POST"})
     */
    public function story_set_approve(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        if ($story->getIsApproved() == false) {
            $story->setStatus('a')
                ->setIsApproved(true);
        } else {
            $story->setStatus('p')
                ->setIsApproved(false);
        }
        $em->persist($story);
        $em->flush();
        return $this->redirectToRoute('story_index', ['type' => $story->getStatus()]);
    }

    /**
     * @Route("/new", name="story_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(Category::class)->findAll();
        return $this->render('story/new.html.twig', ['category' => $category]);
    }
    /**
     * @Route("/story_submit", name="story_submit", methods={"GET","POST"})
     */
    public function story_submit()
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
        $em->persist($story);
        $em->flush();

        return $this->redirectToRoute('story_index', ['type' => 'all']);
    }

    /**
     * @Route("/story_edit_submit/{id}", name="story_edit_submit", methods={"GET","POST"})
     */
    public function story_edit_submit(Request $request,EntityManagerInterface $em)
    {
        $id=$request->get('id');
        $story = $em->getRepository(Story::class)->findOneBy(['id'=>$id]);
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
        $em->persist($story);
        $em->flush();

        return $this->redirectToRoute('story_index', ['type' => 'all']);
    }
    /**
     * @Route("/twitter/section/reorder", name="twitter_section_reorder", methods={"GET","POST"})
     */
    public function twitterSectionReorder(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $request->get('data');
        print_r($data);
        foreach ($data as $d) {
            if ($d) {
                if ($d['new']) {
                    $row = $em->getRepository(Twitter::class)->findOneBy(['story' => $request->get('storyId'), 'rank' => $d['old']]);
                    if ($row) {

                        $row->setRank($d['new']);
                        $em->persist($row);
                    }
                }
            }
        }
        $em->flush();
        return new JsonResponse(['true']);
    }

    /**
     * @Route("/facebook/section/reorder", name="facebook_section_reorder", methods={"GET","POST"})
     */
    public function facebookSectionReorder(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $request->get('data');
        print_r($data);
        foreach ($data as $d) {
            if ($d) {
                if ($d['new']) {
                    $row = $em->getRepository(FacebookWatch::class)->findOneBy(['story' => $request->get('storyId'), 'rank' => $d['old']]);
                    if ($row) {

                        $row->setRank($d['new']);
                        $em->persist($row);
                    }
                }
            }
        }
        $em->flush();
        return new JsonResponse(['true']);
    }

    /**
     * @Route("/twitter/index/{storyId}", name="story_twitter_index", methods={"GET","POST"})
     */
    public function story_twitter_section(Request $request, DataTableFactory $dtf)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $table = $dtf->create()
            ->add('id', NumberColumn::class, ['label' => 'id'])
            ->add('name', TextColumn::class, ['label' => 'Name',])
            ->add('url', TextColumn::class, ['label' => 'Url'])
            ->add('rank', NumberColumn::class, ['label' => 'Rank'])
            ->addOrderBy('rank', DataTable::SORT_ASCENDING)
            ->createAdapter(ORMAdapter::class, [
                'entity' => Twitter::class,
                'query' => function (QueryBuilder $builder) use ($story) {
                    $builder
                        ->select('s')
                        ->from(Twitter::class, 's')
                        ->where('s.story = :story')
                        ->setParameter('story', $story->getId());
                },
            ])
            ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        };

        return $this->render('story/twitter/index.html.twig', ['datatable' => $table, 'storyId' => $id, 'story' => $story]);
    }

    /**
     * @Route("/twitter/{storyId}/new", name="story_twitter_new", methods={"GET","POST"})
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
                ->setStory($story);
            $em->persist($twitter);
            $em->flush();
            return $this->redirectToRoute('story_twitter_index', ['storyId' => $id, 'story' => $story]);
        }
        return $this->render('story/twitter/new.html.twig', ['form' => $form->createView(), 'storyId' => $id, 'story' => $story]);
    }

    /**
     * @Route("/facebook/watch/index/{storyId}", name="story_facebook_index", methods={"GET","POST"})
     */
    public function story_facebook_watch_section(Request $request, DataTableFactory $dtf)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $table = $dtf->create()
            ->add('id', NumberColumn::class, ['label' => 'id'])
            ->add('name', TextColumn::class, ['label' => 'Name',])
            ->add('url', TextColumn::class, ['label' => 'Url'])
            ->add('rank', NumberColumn::class)
            ->addOrderBy('rank', DataTable::SORT_ASCENDING)
            ->createAdapter(ORMAdapter::class, [
                'entity' => FacebookWatch::class,
                'query' => function (QueryBuilder $builder) use ($story) {
                    $builder
                        ->select('s')
                        ->from(FacebookWatch::class, 's')
                        ->where('s.story = :story')
                        ->setParameter('story', $story->getId());
                },
            ])
            ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        };
        return $this->render('story/facebook/index.html.twig', [
            'datatable' => $table, 'storyId' => $id,
            'story' => $story
        ]);
    }

    /**
     * @Route("/facebook/watch/{storyId}/new", name="story_facebook_new", methods={"GET","POST"})
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
                ->setStory($story);
            $em->persist($facebook);
            $em->flush();
            return $this->redirectToRoute('story_facebook_index', ['storyId' => $id]);
        }
        return $this->render('story/facebook/new.html.twig', ['form' => $form->createView(), 'storyId' => $id, 'story' => $story]);
    }

    /**
     * @Route("/{id}", name="story_show", methods={"GET"})
     */
    public function show(Story $story): Response
    {
        return $this->render('story/show.html.twig', [
            'story' => $story,
        ]);
    }

    /**
     * @Route("/{id}/edit/", name="story_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Story $story): Response
    {
        $id = $request->get('id');
        $em = $this->getEntityManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $category = $em->getRepository(Category::class)->findAll();

        return $this->render('story/edit.html.twig', [
            'story' => $story,'category'=>$category,'tags'=>$em->getRepository(Tag::class)->findAll(),'storyId'=>$id

        ]);
    }

    /**
     * @Route("/{id}", name="story_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Story $story): Response
    {
        if ($this->isCsrfTokenValid('delete' . $story->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($story);
            $entityManager->flush();
        }

        return $this->redirectToRoute('story_index', ['type' => 'all']);
    }
}
