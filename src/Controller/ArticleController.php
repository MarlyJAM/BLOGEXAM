<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Repository\ArticleRepository;
use App\Entity\Article;
use App\Entity\Rating;
use App\Form\ArticleType;
use App\Form\RatingType;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArticleBlogRepository;
use Doctrine\Persistence\ManagerRegistry;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'app_articles', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/article_me', name: 'app_article_me')]
    #[isGranted('ROLE_USER')]
    public function myarticles(ArticleRepository $repository, PaginatorInterface $paginator,Request $request): Response
    {
        $articles = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('article/myarticles.html.twig',[
            'articles' => $articles
        ]);
       
    }

    #[Route('/{id}/content/', name: 'app_article_content')]
    public function content(Request $request, Article $article,ArticleRepository $articleRepository,ManagerRegistry $doctrine,FlashyNotifier $flashy): Response
     {
        $rate = new Rating();

        $rating_form = $this->createForm(RatingType::class, $rate);
        $em = $doctrine->getManager();

        $rating_form->handleRequest($request);
        if( $rating_form-> isSubmitted()  &&  $rating_form->isValid()){
            $rate= $rating_form->getData();
            $rate->setUserId($this->getUser());
            $rate->setArticleId($article);
            $em ->persist($rate);
            $em -> flush();
            $flashy->success('Votre note a bien été posté');

            return $this-> redirectToRoute('app_article_me');

        }


         return $this->render('article/content.html.twig',[
            'article' =>$article,
            'rating_form' => $rating_form->createView()
        ]);

    }

    #[Route('/article_new', name: 'app_article_new')]
    public function new(Request $request, ArticleRepository $articleRepository,ManagerRegistry $doctrine,FlashyNotifier $flashy): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $em = $doctrine->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $article=$form->getData();
            $article ->setUserId($this->getUser());
            $article->setCreatedAt(new DateTimeImmutable());
            $em ->persist($article);
            $em -> flush();
            $flashy->success('Votre article a bien été posté');
            
            return $this->redirectToRoute('app_article_me', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' =>  $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === article.getUserId()")]
    public function edit(Request $request, Article $article, ArticleRepository $articleRepository,ManagerRegistry $doctrine,FlashyNotifier $flashy): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $em = $doctrine->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('image')->getData();
            //dd($images);
            $article=$form->getData();
            $article ->setUserId($this->getUser());
            $em ->persist($article);
            $em -> flush();
            
            $flashy->success('Votre article a bien été modifié');
            
            return $this->redirectToRoute('app_article_me', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    #[isGranted('ROLE_USER')]
    #[Security("is_granted('ROLE_USER') and user === article.getUserId()")]
    public function delete(Request $request, Article $article, ArticleRepository $articleRepository,FlashyNotifier $flashy): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $articleRepository->remove($article, true);
        }

        $flashy->success('Votre article a bien été supprimé');
            
        return $this->redirectToRoute('app_article_blog_myarticles', [], Response::HTTP_SEE_OTHER);
    }

}
