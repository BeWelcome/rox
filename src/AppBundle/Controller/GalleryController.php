<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Gallery;
use AppBundle\Entity\GalleryImage;
use AppBundle\Form\CustomDataClass\GalleryImageEditRequest;
use AppBundle\Form\GalleryEditImageFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends Controller
{
    /**
     * @Route("/gallery/show/image/{id}/edit", name="gallery_edit_image",
     *     requirements = {"id": "\d+"}
     * )
     *
     * @param Request      $request
     * @param GalleryImage $image
     *
     * @return Response
     */
    public function editImageAction(Request $request, GalleryImage $image)
    {
        $user = $this->getUser();
        if ($user !== $image->getOwner()) {
            throw $this->createAccessDeniedException('Not your image!');
        }

        $editImageRequest = new GalleryImageEditRequest($image);
        $editImageForm = $this->createForm(GalleryEditImageFormType::class, $editImageRequest);
        $editImageForm->handleRequest($request);
        if ($editImageForm->isValid() && $editImageForm->isSubmitted()) {
            $data = $editImageForm->getData();
            $image->setTitle($data->title);
            $image->setDescription($data->description);
            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();

            $this->addFlash('notice', 'Updated image information');

            return $this->redirectToRoute('gallery_show_image', ['imageId' => $image->getId()]);
        }

        return $this->render(':gallery:edit.image.html.twig', [
            'image' => $image,
            'form' => $editImageForm->createView(),
        ]);
    }
}
