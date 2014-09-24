<?php

namespace RafaelSantiago\SpotifyBundle\Controller;

use Lsw\ApiCallerBundle\Call\HttpGetJson;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SpotifyController extends Controller
{

    const SPOTIFY_SESSION_CODE = 'spotify_autentication_code';
    const SPOTIFY_API_URI = 'http://api.spotify.com/v1/';

    public function loginAction()
    {

        if ($this->getSession()->get(self::SPOTIFY_SESSION_CODE, null) == null){
            return $this->render('RafaelSantiagoSpotifyBundle:Spotify:login.html.twig');
        }
        else {
            return $this->redirect($this->generateUrl('rafael_santiago_spotify_playlists'));
        }

    }

    public function loginReturnAction(Request $request)
    {
        $code = $request->get('code');

        $session = $this->getSession();
        $session->set(self::SPOTIFY_SESSION_CODE, $code);

        return $this->redirect($this->generateUrl('rafael_santiago_spotify_login'));

    }

    public function playlistsAction(Request $request)
    {

        /*$parameters = array(
            'Authorization' => 'Bearer ' . $this->getSession()->get(self::SPOTIFY_SESSION_CODE)
        );*/

        $header = array();
        $header[] = 'Authorization: Bearer '.$this->getSession()->get(self::SPOTIFY_SESSION_CODE);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::SPOTIFY_API_URI . 'me');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        //var_dump(curl_getinfo($ch));
        $out = curl_exec($ch);

        print "error:" . curl_error($ch) . "<br />";
        print "output:" . $out . "<br /><br />";

        curl_close($ch);

        return $this->render('RafaelSantiagoSpotifyBundle:Spotify:playlists.html.twig');

    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    private function getSession()
    {
        return $this->container->get('session');
    }

}
