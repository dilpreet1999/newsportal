<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Story;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController {

    /**
     * @Route("/admin", name="admin")
     */
    public function index() {
        $storyCount = $this->storyCount();
        $userCount = $this->userCount();
        return $this->render('admin/index.html.twig', [
                    'story' => $storyCount,
                    'user' => $userCount,
        ]);
    }
    /**
     * @Route("/admin/user/create", name="admin_user_create", methods={"GET","POST"})
     */
    public function admin_user_create( Request $request) {
    $em=$this->getDoctrine()->getManager();
        $form=$this->createFormBuilder()
        ->add('email',EmailType::class)
        ->add('password',PasswordType::class)
        ->add('name',TextType::class)
        ->add('city')
        ->add('state')
        ->add('mobile')
        ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $password=$form['password']->getData();
            $password=sha1($password);
            $user= new User();
            $user
            ->setName($form['name']->getdata())
            ->setEmail($form['email']->getdata())
            ->setPassword($password)
            ->setCity($form['city']->getdata())
            ->setState($form['state']->getdata())
            ->setMobile($form['mobile']->getData())
            ->setRoles(['ROLE_USER']);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('admin');
            
        }
        return $this->render('admin/userNew.html.twig',['form'=>$form->createView()]);
    }

    /**
     * @Route("/admin/users", name="admin_users")
     */
    public function users(DataTableFactory $datatable, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $table = $datatable->create()
                ->add('id', NumberColumn::class, ['label' => 'Id'])
                ->add('name', TextColumn::class, ['label' => 'Name'])
                ->add('address', TextColumn::class, ['label' => 'Address'])
                ->add('mobile', TextColumn::class, ['label' => 'Mobile'])
                ->add('email', TextColumn::class, ['label' => 'Email'])
                ->createAdapter(ORMAdapter::class, [
                    'entity' => User::class,
                    'query' => function (QueryBuilder $builder) {
                        $builder
                        ->select('u')
                        ->from(User::class, 'u')

                        ;
                    },
                ])
                ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/users.html.twig', [
                    'datatable' => $table,
        ]);
    }

    /**
     * @Route("/admin/stories", name="admin_stories")
     */
    public function stories(DataTableFactory $datatable, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $table = $datatable->create()
                ->add('id', NumberColumn::class, ['label' => 'Id'])
                ->add('title', TextColumn::class, ['label' => 'Title'])
                ->add('hits', TextColumn::class, ['label' => 'Views'])
                ->add('categoryId', TextColumn::class, ['label' => 'category','render'=>function($c,$v){
                
                        return $v->getCategory()->getName();
                }
                ])
                ->add('tag', TextColumn::class, ['label' => 'Tag','render'=>function($c,$v){
                
                        return json_decode( $v->getTags());
                }])
                ->add('user', TextColumn::class, ['label' => 'User','render'=>function($c,$v){
                
                          if($v->getUser()){
                               return $v->getUser()->getName();
                        
                        }
                        else{
                            return 'admin';
                        }
                }])
                ->createAdapter(ORMAdapter::class, [
                    'entity' => Story::class,
                    'query' => function (QueryBuilder $builder) {
                        $builder
                        ->select('s')
                        ->from(Story::class, 's')
//                        ->where('s.category = :categoryId')
//                                ->setParameter('categoryId', $category->getId())

                        ;
                    },
                ])
                ->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/users.html.twig', [
                    'datatable' => $table,
        ]);
    }

    public function storyCount() {
        $storyCount = $this->getDoctrine()->getManager()->getRepository(Story::class)->findAll();
        $story = count($storyCount);
        return $story;
    }

    public function userCount() {
        $userCount = $this->getDoctrine()->getManager()->getRepository(User::class)->findAll();
        $userCount = count($userCount);
        return $userCount;
    }

    /**
     * @Route("/admin/comment/delete/{id}", name="comment_delete")
     */
    public function comment_delete(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository(Comment::class)->findOneBy(['id' => $id]);

        $em->remove($comment);
        $em->flush();
        return $this->redirectToRoute('admin_comment');
    }

    /**
     * @Route("/admin/comment/approve/{id}", name="comment_approve")
     */
    public function comment_approve(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository(Comment::class)->findOneBy(['id' => $id]);
        $comment->setisApprove(true);
        $em->persist($comment);
        $em->flush();
        return $this->redirectToRoute('admin_comment');
    }

    /**
     * @Route("/admin/comments", name="admin_comment")
     */
    public function comments() {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                'SELECT C    FROM App:Comment C
                            ORDER BY C.id DESC'
                )
        ;
        $comment = $query->getResult();
        return $this->render('admin/comment.html.twig', ['comments' => $comment]);
    }

}
