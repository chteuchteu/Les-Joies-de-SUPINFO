<?php

namespace LjdsBundle\Controller;

use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use LjdsBundle\Entity\ReportState;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GifsController extends Controller
{
    /**
     * @Route("/", name="index")
     * @Route("/page/{page}", name="page")
     */
    public function pageAction($page=1)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        // Pagination
        $page = intval($page);

        $params = [
            'gifs' => $gifsRepo->findByGifState(GifState::PUBLISHED, $page),
            'homePage' => $page == 1,
            'pagination' => [
                'page' => $page,
                'pageCount' => $gifsRepo->getPaginationPagesCount(GifState::PUBLISHED)
            ]
        ];
        return $this->render('LjdsBundle:Gifs:index.html.twig', $params);
    }

    /**
     * @Route("/submit", name="submit")
     */
    public function submitAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        $response = new Response();

        $gifSubmitted = false;
        $gifSubmittedError = false;

        // Form is submitted
        if ($request->request->has('catchPhrase')) {
            $post = $request->request;

            $gifSubmitted = true;
            $submittedBy = $post->get('submittedBy');
            $catchPhrase = $post->get('catchPhrase');
            $source = $post->get('source');

            // Create cookie with submittedBy value
            $cookie = new Cookie('submittedBy', $submittedBy, time()+60*60*24*30);
            $response->headers->setCookie($cookie);

            if ($gifSubmittedError === false) {
                $gif = new Gif();
                $gif->setCatchPhrase($catchPhrase);
                $gif->setFileName($post->get('giphy_url'));
                $gif->setReportStatus(ReportState::NONE);
                $gif->setGifStatus(GifState::SUBMITTED);
                $gif->generateUrlReadyPermalink();
                $gif->setSubmissionDate(new \DateTime());
                $gif->setSubmittedBy($submittedBy);
                $gif->setSource($source);

                $em->persist($gif);
                $em->flush();
            } else {
                $params['submitError'] = $gifSubmittedError;
            }
        }

        $params['submittedBy'] = $request->cookies->has('submittedBy')
            ? $request->cookies->get('submittedBy')
            : '';
        $params['submitted'] = $gifSubmitted;


        $response->setContent(
            $this->renderView('LjdsBundle:Gifs:submit.html.twig', $params)
        );

        return $response;
    }

    /**
     * @Route("/top", name="top")
     */
    public function topGifsAction()
    {

    }

    /**
     * @Route("/feed", name="feed")
     */
    public function feedAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        $params = [
            'gifs' => $gifsRepo->getForFeed()
        ];

        $response = new Response(
            $this->renderView('LjdsBundle:Default:feed.html.twig', $params)
        );
        $response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');

        return $response;
    }

    /**
     * @Route("/giphyProxy/", name="giphyProxy")
     */
    public function giphyApiProxyAction(Request $request)
    {
        $post = $request->request;

        $valid_actions = [ 'getTrendingGifs' ];

        if (!$post->has('action')
            || !in_array($post->get('action'), $valid_actions)) {
            return new JsonResponse([ 'error' => 'Invalid action' ], 300);
        }

        $giphy_api_key = $this->getParameter('giphy_api_key');
        $giphy_gifs_limit = $this->getParameter('giphy_gifs_limit');
        $url = 'http://api.giphy.com/v1/gifs/trending?api_key=' . $giphy_api_key . '&limit=' . $giphy_gifs_limit;
        $apiResult = file_get_contents($url);

        if ($apiResult === false) {
            return new JsonResponse([ 'error' => 'Invalid Giphy response' ], 300);
        }

        $json = json_decode($apiResult, true);
        $gifs = [];

        foreach ($json['data'] as $giphyGif) {
            $gifs[] = [
                'image' => $giphyGif['images']['downsized']['url'],
                'url' => $giphyGif['bitly_url']
            ];
        }

        return new JsonResponse([
            'gifs' => $gifs,
            'success' => true
        ]);
    }
}
