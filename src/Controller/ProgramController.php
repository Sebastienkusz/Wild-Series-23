<?php


namespace App\Controller;


use App\Entity\Category;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\ProgramType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/programs", name="program_")
 */
class ProgramController extends AbstractController
{
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
    public function new(Request $request): Response
    {
        // Create a new Program Object
        $program = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);
        // Get date form HTTP request
        $form->handleRequest($request);
        // was the form submitted ?
        if ($form->isSubmitted()) {
            // Deal with the submitted data
            // Get the Entity Manager
            $entityManager = $this->getDoctrine()->getManager();
            // Persist Category Object
            $entityManager->persist($program);
            // Flush the persisted object
            $entityManager->flush();
            // Finally redirect to categories list
            return $this->redirectToRoute('program_index');
        }
        // Render the form
        return $this->render('program/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Getting a program by id
     *
     * @Route("/show/{id<^[0-9]+$>}", methods={"GET"}, requirements={"page"="\d+"}, name="show")
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
     * @Route("/{program_id}/seasons/{season_id}", methods={"GET"}, requirements={"page"="\d+"}, name="season_show")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_id": "id"}})
     * @ParamConverter ("season", class="App\Entity\Season", options={"mapping": {"season_id": "id"}})
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
     * @Route ("/{program_id}/seasons/{season_id}/episodes/{episode_id}", methods={"GET"}, requirements={"page"="\d+"}, name="episode_show")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_id": "id"}})
     * @ParamConverter ("season", class="App\Entity\Season", options={"mapping": {"season_id": "id"}})
     * @ParamConverter ("episode", class="App\Entity\Episode", options={"mapping": {"episode_id": "id"}})
     * @return Response
     */
    public function showEpisode(Program $program, Season $season, Episode $episode): Response
    {
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
        ]);
    }

}
