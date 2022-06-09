<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\StoryImages;
use App\Form\StoryImagesType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/story/images")
 */
class StoryImagesController extends BaseController {

    /**
     * @Route("/{storyId}", name="story_images_index", methods={"GET"})
     */
    public function index(Request$request): Response {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $storyImages = $em->getRepository(StoryImages::class)->findBy(['story' => $story]);
        return $this->render('story_images/index.html.twig', [
                    'story_images' => $storyImages,
                    'storyId' => $storyId
        ]);
    }

    /**
     * @Route("/new/{id}", name="story_images_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response {
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

            return $this->redirectToRoute('story_images_index', ['storyId' => $storyId]);
        }

        return $this->render('story_images/new.html.twig', [
                    'story_image' => $storyImage,
                    'form' => $form->createView(),
                    'storyId' => $storyId
        ]);
    }

//    /**
//     * @Route("/{id}", name="story_images_show", methods={"GET"})
//     */
//    public function show(StoryImages $storyImage): Response {
//        return $this->render('story_images/show.html.twig', [
//                    'story_image' => $storyImage,
//        ]);
//    }

    /**
     * @Route("/{id}/set/primary/{storyId}/", name="story_images_set_primary", methods={"GET","POST"})
     */
    public function setPrimary(Request $request): Response {
        $em = $this->getDoctrine()->getManager();
        $storyId = $request->get('storyId');
        $onePhoto=$em->getRepository(StoryImages::class)->findOneBy(['id'=>$request->get('id')]);
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
        return $this->redirectToRoute('story_images_index', ['storyId' => $storyId]);
    }

    /**
     * @Route("/{id}/edit/{storyId}/", name="story_images_edit", methods={"GET","POST"})
     */
    public function edit(Request $request): Response {
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

            return $this->redirectToRoute('story_images_index', ['storyId' => $storyId]);
        }

        return $this->render('story_images/edit.html.twig', [
                    'story_image' => $storyImage,
                    'form' => $form->createView(),
                    'storyId' => $storyId
        ]);
    }

    /**
     * @Route("/{id}/delete/{storyId}", name="story_images_delete", methods={"GET","POST"})
     */
    public function delete(Request $request ): Response {
       $id=$request->get('id');
       $em=$this->getDoctrine()->getManager();
       $storyImage=$em->getRepository(StoryImages::class)->findOneBy(['id'=>$id]);
       $em->remove($storyImage);
       $em->flush();

        return $this->redirectToRoute('story_images_index',['storyId'=>$request->get('storyId')]);
    }

}
