<?php

namespace SpitcheeDocumentation\Controller;

use Container;
use Spitchee\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiDocController
 * @package SpitcheeDocumentation\Controller
 * 
 * @Prefix /api
 */
class ApiDocController extends BaseController
{
    /**
     * @Path /
     * @param Container $app
     * @return mixed
     */
    public function homeAction(Container $app)
    {
        return $app->renderView('doc/home.html.twig');
    }

    /**
     * @Path /go
     * @Method POST
     * @param Container $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function goAction(Container $app)
    {
        $args = $this->parsePostArgs(['bal', 'null']);

        if ($args['bal'] === 'derbal_y' and 'jeremy' === strtolower($args['null'])) {
            return $app->redirect('/api/doc/top/secret/spitchee/spitcheer_seulement/va/trouver/cette/url/en/brute/force/fd');
        }

        return $app->redirect('/api');
    }

    /**
     * @Path /doc/top/secret/spitchee/spitcheer_seulement/va/trouver/cette/url/en/brute/force/fd
     * @param Container $app
     * @return Response
     */
    public function docAction(Container $app) {
        return $app['twig']->render('doc/doc.html.twig', [
            'documentation' => $app['documentation'],
            'shallTroll'    => ! in_array($_SERVER['REMOTE_ADDR'], [
                '10.0.2.2',
                '82.235.234.50',
                '127.0.0.1',
                'localhost'
            ]),
        ]);
    }
}