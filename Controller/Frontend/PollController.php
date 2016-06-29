<?php

namespace Prism\PollBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PollController extends Controller
{
    const RESULTS_ACTION = 'results';
    const CONFIRM_ACTION = 'confirm';
    const HIDE_ACTION = 'hide';

    /**
     * Init
     */
    public function init()
    {
        $this->pollEntity = $this->container->getParameter('prism_poll.poll_entity');
        $this->opinionEntity = $this->container->getParameter('prism_poll.opinion_entity');
        $this->pollEntityRepository = $this->getDoctrine()->getManager()->getRepository($this->pollEntity);
        $this->opinionEntityRepository = $this->getDoctrine()->getManager()->getRepository($this->opinionEntity);
        $this->voteForm = $this->container->getParameter('prism_poll.vote_form');
    }

    /**
     * List all published and opened polls
     *
     * @return Response
     */
    public function listAction()
    {
        $this->init(); // TODO: create a controller listener to call it automatically

        $polls = $this->pollEntityRepository->findBy(
            array('published' => true, 'closed' => false),
            array('createdAt' => 'DESC')
        );

        return $this->render($this->container->getParameter('prism_poll.templates_frontend.list'), array(
            'polls' => $polls
        ));
    }

    /**
     * Display and process a form to vote on a poll
     *
     * @param Request $request
     * @param int     $pollId
     *
     * @throws NotFoundHttpException
     * @return Response|RedirectResponse
                        
     */
    public function voteAction(Request $request, $pollId)
    {
        $this->init();

        $response = new Response();
        $response->setPrivate();
        $response->setMaxAge('5');

        $poll = $this->pollEntityRepository->findOneBy(array('id' => $pollId, 'published' => true, 'closed' => false));

        if (!$poll) {
            throw $this->createNotFoundException("This poll doesn't exist or has been closed.");
        }

        $pollVotedAction = $this->container->getParameter('prism_poll.actions.poll_voted');
        $pollSubmittedAction = $this->container->getParameter('prism_poll.actions.poll_submitted');

        // If the user has already voted, show the results
        if ($this->hasVoted($request, $pollId)) {
            switch( $pollVotedAction )
            {
                case self::HIDE_ACTION:
                    $response = new Response('', 204);
                    $response->setPrivate();
                    return $response;
                default:
                    $actionConfig = $this->getPollActionConfig( $pollId, $pollVotedAction );
                    return $this->forward($actionConfig['controller'], $actionConfig['params']);
            }
        }

        $opinionsChoices = array();
        foreach ($poll->getOpinions() as $opinion) {
            $opinionsChoices[$opinion->getId()] = $opinion->getName();
        }

        $form = $this->container->get('form.factory')->createNamed('poll' . $pollId, new $this->voteForm, null, array('opinionsChoices' => $opinionsChoices));

        if ('POST' == $request->getMethod()) {

            $form->submit($request);

            if ($form->isValid()) {

                $data = $form->getData();
                $opinion = $this->opinionEntityRepository->find($data['opinions']);
                $opinion->setVotes($opinion->getVotes() + 1);

                $em = $this->getDoctrine()->getManager();
                $em->persist($opinion);
                $em->flush();

                switch( $pollSubmittedAction )
                {
                    case self::CONFIRM_ACTION:
                        $response = $request->isXmlHttpRequest() ?
                            // If the form has been sent via ajax, we show the confirmation
                            new JsonResponse(
                                array(
                                    'successMessage' => $this->renderView(
                                        $this->container->getParameter('prism_poll.templates_frontend.confirm_ajax')
                                    )
                                )
                            ) :
                            // else, we redirect to the confirmation page
                            new RedirectResponse($this->generateUrl('PrismPollBundle_frontend_poll_confirm'));
                        break;
                    default:
                        $response = $request->isXmlHttpRequest() ?
                            // If the form has been sent via ajax, we show the results
                            $this->forward('PrismPollBundle:Frontend\Poll:results', array('pollId' => $pollId, 'hasVoted' => true)) :
                            // else, we redirect to the list page
                            new RedirectResponse($this->generateUrl('PrismPollBundle_frontend_poll_list'));
                }

                $this->addVotingProtection($pollId, $response);
                $response->setPrivate();
                return $response;
            }
        }

        return $this->render(
            $this->container->getParameter('prism_poll.templates_frontend.vote'),
            array(
                'poll' => $poll,
                'form' => $form->createView()
            ),
            $response
        );
    }

    /**
     * Show the results of a poll
     *
     * @param int $pollId
     *
     * @return Response
     */
    public function resultsAction($pollId, $hasVoted = false)
    {
        $this->init();

        $poll = $this->pollEntityRepository->findOneBy(array('id' => $pollId, 'published' => true));

        if (!$poll) {
            throw $this->createNotFoundException("This poll doesn't exist.");
        }

        return $this->render($this->container->getParameter('prism_poll.templates_frontend.results'), array(
            'poll' => $poll,
            'hasVoted' => $hasVoted
        ));
    }

    /**
     * Show the confirmation of a vote
     *
     * @return Response
     */
    public function confirmAction()
    {
        return $this->render($this->container->getParameter('prism_poll.templates_frontend.confirm'));
    }

    /**
     * Show the confirmation of a vote, for an Ajax Request
     *
     * @return Response
     */
    public function confirmAjaxAction()
    {
        return $this->render($this->container->getParameter('prism_poll.templates_frontend.confirm_ajax'));
    }

    /**
     * Add a cookie to prevent a user from voting multiple times on the same poll
     *
     * @param int                       $pollId
     * @param Response|RedirectResponse $response
     */
    protected function addVotingProtection($pollId, $response)
    {
        return $response->headers->setCookie(new Cookie('prism_poll_' . $pollId, true, time()+3600*24*365));
    }

    /**
     * Check if the user has voted on a poll
     *
     * @param Request $request
     * @param         $pollId
     *
     * @return bool
     */
    protected function hasVoted(Request $request, $pollId)
    {
        $cookies = $request->cookies;
        if ($cookies->has('prism_poll_' . $pollId)) {
            return true;
        }

        return false;
    }

    /**
     * @param $pollId
     * @param $pollAction
     *
     * @return array
     */
    private function getPollActionConfig( $pollId, $pollAction )
    {
        $actionConfig = array();

        switch( $pollAction )
        {
            case self::CONFIRM_ACTION:
                $actionConfig['controller'] = 'PrismPollBundle:Frontend\Poll:confirm';
                $actionConfig['params'] = array();
                break;
            default:
                $actionConfig['controller'] = 'PrismPollBundle:Frontend\Poll:results';
                $actionConfig['params'] = array( 'pollId' => $pollId, 'hasVoted' => true );
        }

        return $actionConfig;
    }
}
