<?php

namespace Spitchee\Controller;

use Container;

/**
 * @Prefix /api
 */
class NamiController extends BaseController
{
    /**
     * Tous les events recu par NAMI passeront par ici pour être persistés ou ignorés
     *
     * @Path /internal/nami/event
     * @Method POST
     * @param Container $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerEventAction(Container $app) {
        $this->ipRestrict(array_map(function ($ip) {
            if (false === $pos = strpos($ip, ':')) {
                return $ip;
            }
            return substr($ip, 0, $pos);
        }, $app['config']['services']));

        $mbEvent = $this->getNamiEventService()->handleNewEvent(
            $this->parsePostArgs()
        );

        $this->getContainer()->getAsteriskEventConsequencesService()
             ->setCause($mbEvent)->log();

        return $this->status($mbEvent ? 201 : 200);
    }

    /**
     * @Path /events
     */
    public function fetchEventsAction()
    {
        $args = [
            'userId'    => $this->getQueryArg('userId'),
            'sipId'     => $this->getQueryArg('sipId'),
            'conferenceId'  => $this->getQueryArg('conferenceId'),
            'type'      => $this->getQueryArg('type'),
            'limit'     => $this->getQueryArg('limit', 30),
            'created%'  => $this->getQueryArg('created', null),
            'sort_created'  => $this->getQueryArg('sortByCreated', 'ASC'),
        ];

        return $this->json($this
                ->getRepositoryService()->getNamiEventRepository()
                ->findNamiEventsAsArray($args)
        );
    }
}