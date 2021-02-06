<?php


namespace App\Controller;

use App\Entity\BlogPost;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

/**
 * Class BlogController
 * @Route("/blog") App\Controller
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/{page}",name="blog_posts", defaults={"page"=1}, requirements={"page"="\d+"}) JsonResponse
     */
    public function list($page=1,Request $request) {
        $limit = $request->get('limit',10);
        $repository = $this->getDoctrine()->getRepository(BlogPost::class);
        $items = $repository->findAll();
        return $this->json(["page"=>$page,"limit"=>$limit,"data"=>$items]);
    }

    /**
     * @Route("/post/{id}",name="blog_by_id", requirements={"id"="\d+"}, methods={"GET"}) JsonResponse
     * @ParamConverter("post",class="App:BlogPost")
     */
    public function postbyid($post) {
        return $this->json($post);
    }
    /**
     * @Route("/post/{slug}",name="blog_by_slug", methods={"GET"}) JsonResponse
     * @ParamConverter("post",class="App:BlogPost",options={"mapping" : {"slug" : "slug"}})
     */
    public function postbyslug(BlogPost $post) {
        // the param name must match the field database
        return $this->json($post);
        //return $this->json($this->getDoctrine()->getRepository(BlogPost::class)->findOneBy(["slug"=>$slug]));
    }

    /**
     * @Route("/add", name="blog_add", methods={"POST"}) Request $request
     */
    public function add(Request $request) {
        /**
         * @var Serializer $serializer
         */
        $serializer = $this->get('serializer');

        $blogPost = $serializer->deserialize($request->getContent(),BlogPost::class,"json");
        $entityManger = $this->getDoctrine()->getManager();
        $entityManger->persist($blogPost);
        $entityManger->flush();

        return $this->json($blogPost);
    }

    /**
     * @Route("/post/{id}",name="blog_delete", methods={"DELETE"})
     */
    public function delete(BlogPost $post) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }
}