<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\Youtube;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class YoutubeController extends AbstractController {

    /**
     * @Route("/admin/story/youtube/index/{storyId}", name="story_youtube_index", methods={"GET","POST"})
     */
    public function story_youtube_section(Request $request, DataTableFactory $dtf) {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $id]);
        $table = $dtf->create()
                ->add('id', NumberColumn::class, ['label' => 'id'])
                ->add('name', TextColumn::class, ['label' => 'Name',])
                ->add('url', TextColumn::class, ['label' => 'Url'])
                ->add('rank', NumberColumn::class)
                ->add('Action', TextColumn::class, ['render' => function($c, $v) {
                        $editUrl = $this->generateUrl('story_youtube_edit', ['id' => $v->getId(), 'storyId' => $v->getStory()->getId()]);
                        $deleteUrl = $this->generateUrl('story_youtube_delete', ['id' => $v->getId(), 'storyId' => $v->getStory()->getId()]);
                        $html = "";
                        $html .= "<a class='btn btn-primary'  href='$editUrl'><i class='fa fa-edit ' aria-hidden='true'></i></a>";
                        $html .= "<a class='btn btn-danger'  href='$deleteUrl'><i class='fa fa-trash ' ></i></a>";
                        return $html;
                    }])
                ->addOrderBy('rank', DataTable::SORT_ASCENDING)
                ->createAdapter(ORMAdapter::class, [
                    'entity' => Youtube::class,
                    'query' => function (QueryBuilder $builder)use($story) {
                        $builder
                        ->select('s')
                        ->from(Youtube::class, 's')
                        ->where('s.story = :story')
                        ->setParameter('story', $story->getId())

                        ;
                    },
                ])
                ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        };

        return $this->render('story/youtube/index.html.twig', ['datatable' => $table, 'storyId' => $id, 'story' => $story]);
    }

    /**
     * @Route("/admin/story/add/youtube/{storyId}", name="story_add_youtube_url", methods={"GET","POST"})
     */
    public function add_youtube_url(Request $request) {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $youtube = new Youtube();
        $form = $this->createFormBuilder($youtube)
                ->add('name')
                ->add('url')
                ->add('description')
                ->add('rank')
                ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $youtube->setStory($story)
                    ->setName($form['name']->getData())
                    ->setUrl($form['url']->getData())
                    ->setDescription($form['description']->getData())

                    ->setRank($form['rank']->getData());
            $em->persist($youtube);
            $em->flush();
            return $this->redirectToRoute('story_youtube_index', ['storyId' => $storyId]);
        }
        return $this->render('story/youtube/addYoutube.html.twig', ['form' => $form->createView(), 'story' => $story]);
    }

    /**
     * @Route("/admin/story/youtube/delete/{storyId}", name="story_youtube_delete", methods={"GET","POST"})
     */
    public function deleteYoutube(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $storyId = $request->get('storyId');
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $youtube = $em->getRepository(Youtube::class)->findOneBy(['story' => $story]);
        $em->remove($youtube);
        $em->flush();
        return $this->redirectToRoute('story_youtube_index',['storyId'=>$storyId]);
    }

    /**
     * @Route("/admin/story/youtube/edit/{storyId}", name="story_youtube_edit", methods={"GET","POST"})
     */
    public function editYoutube(Request $request) {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $youtube = $em->getRepository(Youtube::class)->findOneBy(['story' => $story]);
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

                    ->setRank($form['rank']->getData());
            $em->persist($youtube);
            $em->flush();
            return $this->redirectToRoute('story_youtube_index', ['storyId' => $storyId]);
        }
        return $this->render('story/youtube/edit.html.twig', ['form' => $form->createView(), 'story' => $story]);
    }

    /**
     * @Route("/admin/story/youtube/section/reorder", name="youtube_section_reorder", methods={"GET","POST"})
     */
    public function youtubeSectionReorder(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $data = $request->get('data');
        print_r($data);
        foreach ($data as $d) {
            if ($d) {
                if ($d['new']) {
                    $row = $em->getRepository(Youtube::class)->findOneBy(['story' => $request->get('storyId'), 'rank' => $d['old']]);
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

}
