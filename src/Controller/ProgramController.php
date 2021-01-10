<?php


namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\ProgramType;
use App\Service\Slugify;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/programs", name="program_")
 */
class ProgramController extends AbstractController
{
    protected $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    /**
     * Show all rows from Program's entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render('program/index.html.twig', [
            'programs' => $programs,
        ]);
    }

    /**
     * The controller for the category add form
     * @Route ("/new", name="new")
     * @return Response
     */
    public function new(Request $request,
                        Slugify $slugify,
                        MailerInterface $mailer
                        ): Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $program->setOwner($this->getUser());
            $entityManager->persist($program);
            $entityManager->flush();

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('5929398673-baa061@inbox.mailtrap.io')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('/email/_newProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);

            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{program_slug}/edit", name="edit", methods={"GET", "POST"})
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_slug": "slug"}})
     */
    public function edit(Request $request,Slugify $slugify, MailerInterface $mailer, Program $program): Response
    {
        if(!($this->getUser() == $program->getOwner())) {
            throw new AccessDeniedException('Only the owner can edit the program!');
        }
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $entityManager->persist($program);
            $entityManager->flush();

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('5929398673-baa061@inbox.mailtrap.io')
                ->subject('Une nouvelle série vient d\'être modifiée !')
                ->html($this->renderView('/email/_editProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);

            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Getting a program by id
     *
     * @Route("/{program_slug}", methods={"GET"}, name="show")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_slug": "slug"}})
     * @return Response
     */
    public function show(Program $program): Response
    {


        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $program->getSeasons(),
        ]);
    }

    /**
     * Getting a season by id
     *
     * @Route("/{program_slug}/seasons/{season_number}", methods={"GET"}, name="season_show")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_slug": "slug"}})
     * @ParamConverter ("season", class="App\Entity\Season", options={"mapping": {"season_number": "number"}})
     * @return Response
     */
    public function showSeason(Program $program, Season $season): Response
    {
        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes'=> $season->getEpisodes(),
        ]);
    }

    /**
     * Getting an episode by id
     *
     * @Route ("/{program_slug}/seasons/{season_number}/episodes/{episode_slug}", methods={"GET", "POST"}, name="episode_show")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_slug": "slug"}})
     * @ParamConverter ("season", class="App\Entity\Season", options={"mapping": {"season_number": "number"}})
     * @ParamConverter ("episode", class="App\Entity\Episode", options={"mapping": {"episode_slug": "slug"}})
     * @return Response
     */
    public function showEpisode(Program $program,
                                Season $season,
                                Episode $episode,
                                Request $request
                                ): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $author = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setAuthor($author);
            $comment->setEpisode($episode);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('program_episode_show',[
                'program_slug' => $program->getSlug(),
                'season_number' => $season->getNumber(),
                'episode_slug' => $episode->getSlug(),
            ]);
        }

        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(['episode' => $episode]);

        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            'comments' => $comments,
            'form' => $form->createView(),
        ]);
    }

}
