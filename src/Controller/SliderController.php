<?php

namespace App\Controller;

use App\Entity\Slider;
use App\Repository\SliderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\SliderType;

/**
 * @Route("/admin/slider")
 */
class SliderController extends BaseController {

    /**
     * @Route("/", name="slider_index", methods={"GET"})
     */
    public function index(SliderRepository $sliderRepository): Response {
        return $this->render('slider/index.html.twig', [
                    'sliders' => $sliderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/image/show/{id}.{size}.{filename}", name="slider_image", methods={"GET"})
     */
    public function imageShow(Request $request) {
        $id = $request->get('id');
        $size = $request->get('size');
        $em = $this->getDoctrine()->getManager();
        $oneImage = $em->getRepository(Slider::class)->findOneBy(['id' => $id]);

        $content = '';
        while (!feof($oneImage->getFileData())) {
            $content .= fread($oneImage->getFileData(), 1024);
        }
        $content2 = $this->imageResize($content, $oneImage->getFileType(), $size);

        rewind($oneImage->getFileData());
        return new Response($content2, 200, ['content-type' => $oneImage->getFileType()]);
    }

    /**
     * @Route("/new", name="slider_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response {
        $slider = new Slider();
        $form = $this->createForm(SliderType::class, $slider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $file = $this->getUploadedFileInfo($form['fileData']->getData());

            $fileData = file_get_contents($file['realPath']);
            $fileData2 = $this->imageResize($fileData, $file['mimetype'], 800);
            $slider->setFileData($fileData2)
                    ->setFileName(strtolower($file['filename']))
                    ->setFileType($file['mimetype'])
                    ->setDescription($form['description']->getData())
                    ->setHeader($form['header']->getData())
                    ->setTextColor($form['textColor']->getData())
                    ->setUrl("#");

            $entityManager->persist($slider);
            $entityManager->flush();

            return $this->redirectToRoute('slider_index');
        }

        return $this->render('slider/new.html.twig', [
                    'slider' => $slider,
                    'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="slider_show", methods={"GET"})
     */
    public function show(Slider $slider): Response {
        return $this->render('slider/show.html.twig', [
                    'slider' => $slider,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="slider_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Slider $slider): Response {
        $form = $this->createForm(SliderType::class, $slider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $file = $this->getUploadedFileInfo($form['fileData']->getData());

            $fileData = file_get_contents($file['realPath']);
            $fileData2 = $this->imageResize($fileData, $file['mimetype'], 800);
            $slider->setFileData($fileData2)
            ->setFileName(strtolower($file['filename']))
            ->setFileType($file['mimetype'])
            ->setDescription($form['description']->getData())
            ->setHeader($form['header']->getData())
            ->setTextColor($form['textColor']->getData())
            ->setUrl("#");
            $em->persist($slider);
            $em->flush();

            return $this->redirectToRoute('slider_index');
        }

        return $this->render('slider/edit.html.twig', [
                    'slider' => $slider,
                    'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="slider_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Slider $slider): Response {
        if ($this->isCsrfTokenValid('delete' . $slider->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($slider);
            $entityManager->flush();
        }

        return $this->redirectToRoute('slider_index');
    }

}
