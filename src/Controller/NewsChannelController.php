<?php

namespace App\Controller;

use App\Entity\NewsChannel;
use App\Form\NewsChannelType;
use App\Repository\NewsChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/news/channel")
 */
class NewsChannelController extends BaseController {

    /**
     * @Route("/", name="news_channel_index", methods={"GET"})
     */
    public function index(NewsChannelRepository $newsChannelRepository): Response {
        return $this->render('news_channel/index.html.twig', [
                    'news_channels' => $newsChannelRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="news_channel_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response {
        $newsChannel = new NewsChannel();
        $form = $this->createForm(NewsChannelType::class, $newsChannel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $file = $form['upload']->getData();
            $file = $this->getUploadedFileInfo($file);
            $fileData = file_get_contents($file['realPath']);
            $newsChannel->setFileData($fileData)
                    ->setFileName($file['filename'])
                    ->setFileType($file['mimetype'])
                    ->setName($form['name']->getData());
            $entityManager->persist($newsChannel);
            $entityManager->flush();

            return $this->redirectToRoute('news_channel_index');
        }

        return $this->render('news_channel/new.html.twig', [
                    'news_channel' => $newsChannel,
                    'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="news_channel_show", methods={"GET"})
     */
    public function show(NewsChannel $newsChannel): Response {
        return $this->render('news_channel/show.html.twig', [
                    'news_channel' => $newsChannel,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="news_channel_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, NewsChannel $newsChannel): Response {
        $em = $this->getDoctrine()->getManager();

        $newsChannel = $em->getRepository(NewsChannel::class)->findOneBy(['id' => $request->get('id')]);
        $form = $this->createForm(NewsChannelType::class, $newsChannel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['upload']->getData();
            $file = $this->getUploadedFileInfo($file);
            $fileData = file_get_contents($file['realPath']);
            $newsChannel->setFileData($fileData)
                    ->setFileName($file['filename'])
                    ->setFileType($file['mimetype'])
                    ->setName($form['name']->getData());
            $em->persist($newsChannel);
            $em->flush();

            return $this->redirectToRoute('news_channel_index');
        }


        return $this->render('news_channel/edit.html.twig', [
                    'news_channel' => $newsChannel,
                    'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="news_channel_delete", methods={"DELETE"})
     */
    public function delete(Request $request, NewsChannel $newsChannel): Response {
        if ($this->isCsrfTokenValid('delete' . $newsChannel->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($newsChannel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('news_channel_index');
    }

}
