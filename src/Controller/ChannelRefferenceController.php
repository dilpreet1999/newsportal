<?php

namespace App\Controller;

use App\Entity\ChannelRefference;
use App\Entity\NewsChannel;
use App\Entity\Story;
use App\Form\ChannelRefferenceType;
use DateTime;
use DateTimeZone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/story/channel/refference")
 */
class ChannelRefferenceController extends AbstractController {

    /**
     * @Route("/{storyId}", name="channel_refference_index", methods={"GET"})
     */
    public function index(Request $request): Response {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $channelRefference = $em->getRepository(ChannelRefference::class)->findBy(['story' => $story]);
        return $this->render('channel_refference/index.html.twig', [
                    'channel_refferences' => $channelRefference,
                    'storyId' => $storyId,
                    'story' => $story
        ]);
    }

    /**
     * @Route("/new/{storyId}", name="channel_refference_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $channelRefference = $em->getRepository(ChannelRefference::class)->findBy(['story' => $story]);

        $form = $this->createFormBuilder()
                ->add('title')
                ->add('name', EntityType::class,['class'=> NewsChannel::class,'choice_label'=>'name'])
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

            return $this->redirectToRoute('channel_refference_index', ['storyId' => $storyId]);
        }

        return $this->render('channel_refference/new.html.twig', [
                    'channel_refference' => $channelRefference,
                    'form' => $form->createView(),
                    'storyId' => $storyId,
                    'story' => $story
        ]);
    }

    /**
     * @Route("/{id}/{storyId}/show", name="channel_refference_show", methods={"GET"})
     */
    public function show(Request $request): Response {
        $storyId = $request->get('storyId');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $channelRefference = $em->getRepository(ChannelRefference::class)->findOneBy(['id'=>$request->get('id'),'story' => $story]);
        return $this->render('channel_refference/show.html.twig', [
                    'channel_refference' => $channelRefference,
                    'storyId' => $storyId,
                    'story' => $story
        ]);
    }

    /**
     * @Route("/{id}/{storyId}/edit", name="channel_refference_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ChannelRefference $channelRefference): Response {
        $storyId = $request->get('storyId');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $story = $em->getRepository(Story::class)->findOneBy(['id' => $storyId]);
        $channelRefference = $em->getRepository(ChannelRefference::class)->findOneBy(['id' => $id]);
       $form = $this->createFormBuilder($channelRefference)
                ->add('title')
                ->add('name', EntityType::class,['class'=> NewsChannel::class,'choice_label'=>'name'])
                ->add('website')
                ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('channel_refference_index', ['storyId' => $storyId]);
        }

        return $this->render('channel_refference/edit.html.twig', [
                    'channel_refference' => $channelRefference,
                    'form' => $form->createView(),
                    'storyId' => $storyId,
                    'story' => $story
        ]);
    }

    /**
     * @Route("/{id}", name="channel_refference_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ChannelRefference $channelRefference): Response {
        if ($this->isCsrfTokenValid('delete' . $channelRefference->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($channelRefference);
            $entityManager->flush();
        }

        return $this->redirectToRoute('channel_refference_index');
    }

}
