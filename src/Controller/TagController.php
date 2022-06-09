<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/tag")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/", name="tag_index", methods={"GET"})
     */
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('tag/index.html.twig', [
            'tags' => $tagRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="tag_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
             $entityManager = $this->getDoctrine()->getManager();
                $tagname = $form['name']->getData();
                $tagch = $entityManager->getRepository(Tag::class)->findBy(['name' => $tagname]);
                if ($tagch) {
                    $form['name']->addError(new FormError('Tag '.$tagname.' is already exist in database'));
                }
            if ($form->isValid()) {
               $entityManager->persist($tag);
               $entityManager->flush();
                
            
            $entityManager->persist($tag);
            $entityManager->flush();
            return $this->redirectToRoute('tag_index');
        }}
        return $this->render('tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tag_show", methods={"GET"})
     */
    public function show(Tag $tag): Response
    {
        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
        ]);
    }
    /**
     * @Route("/tag/submit/{id}", name="tag_submit", methods={"GET","POST"})
     */
    public function tagSubmit(Request $request){
        $form=$_POST;
        $tags=$form['tags'];
        $x = json_encode($tags);
        $id=$request->get('id');
        $em=$this->getdoctrine()->getManager();
        $story=$em->getRepository(Story::Class)->findOneBy(['id'=>$id]);
        $story->setTags($x);
        $em->persist($story);
        $em->flush();
        return $this->redirectToRoute('story_index',['type'=>'all']);
        
    }
    /**
     * @Route("/add/tag/{id}", name="story_tag_add", methods={"GET","POST"})
     */
    public function addTag(Request $request) {
        $id=$request->get('id');
        return $this->render('tag/addTag.html.twig',['id'=>$id]);
    }

    /**
     * @Route("/{id}/edit", name="tag_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Tag $tag): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tag_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Tag $tag): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tag);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tag_index');
    }
}
