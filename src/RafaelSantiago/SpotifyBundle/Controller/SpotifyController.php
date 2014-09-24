<?php

namespace RafaelSantiago\SpotifyBundle\Controller;

use Lsw\ApiCallerBundle\Call\HttpGetJson;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SpotifyController extends Controller
{

    const SPOTIFY_SESSION_TOKEN = 'spotify_autentication_code';
    const SPOTIFY_USER_ID = 'spotify_user_id';

    const SPOTIFY_API_URI = 'http://api.spotify.com/v1/';
    const SPOTIFY_API_ACCOUNT = 'https://accounts.spotify.com/api/';

    public function loginAction()
    {

        //if ($this->getSession()->get(self::SPOTIFY_SESSION_TOKEN, null) == null){
            return $this->render('RafaelSantiagoSpotifyBundle:Spotify:login.html.twig');
        //}
        //else {
        //   return $this->redirect($this->generateUrl('rafael_santiago_spotify_playlists'));
        //}

    }

    public function loginReturnAction(Request $request)
    {

        $code = $request->get('code');

        // Get Token
        $parameters =
            "grant_type=authorization_code" .
            "&code=".$code .
            "&redirect_uri=http%3A%2F%2Flocalhost%2Fspotify%2Fweb%2Fapp_dev.php%2Flogin%2Freturn" .
            "&client_id=eb6abf4bdf8f4699bc8d185e15279589" .
            "&client_secret=1fec670f85c44d808fba74546045f560";
        $out = $this->doRequest(self::SPOTIFY_API_ACCOUNT . 'token', $parameters);
        $arrReturn = json_decode($out, true);

        // Save token in session
        $this->getSession()->set(self::SPOTIFY_SESSION_TOKEN, $arrReturn['access_token']);

        // Get user id
        $out = $this->doRequest(self::SPOTIFY_API_URI . 'me', '', true, 'GET');
        $arrReturn = json_decode($out, true);

        // Save user id
        $this->getSession()->set(self::SPOTIFY_USER_ID, $arrReturn['id']);

        return $this->redirect($this->generateUrl('rafael_santiago_spotify_playlists'));
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     */
    private function doRequest($uri, $data, $auth = false, $method = 'POST')
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, (($method == 'POST') ? 1 : 0));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($auth){

            $header = array();
            $header[] = 'Authorization: Bearer '.$this->getSession()->get(self::SPOTIFY_SESSION_TOKEN);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        }

        $out = curl_exec($ch);

        curl_close($ch);

        return $out;
    }

    public function playlistsAction(Request $request)
    {

        $url = self::SPOTIFY_API_URI . 'users/'.$this->getSession()->get(self::SPOTIFY_USER_ID).'/playlists';
        $out = $this->doRequest($url, '', true, 'GET');
        $arrReturn = json_decode($out, true);

        return $this->render('RafaelSantiagoSpotifyBundle:Spotify:playlists.html.twig',array(
            'listas' => $arrReturn['items']
        ));

    }

    public function playlistDetailAction(Request $request, $owner_id, $list_id)
    {

        $url = self::SPOTIFY_API_URI . 'users/'.$owner_id.'/playlists/'.$list_id.'/tracks';
        $out = $this->doRequest($url, 'limit=10', true, 'GET');
        $arrReturn = json_decode($out, true);

        /*echo "<pre>";
        var_dump($arrReturn['items']);
        echo "</pre>";*/

        return $this->render('@RafaelSantiagoSpotify/Spotify/playlistDetail.html.twig',array(
            'tracks' => $arrReturn['items']
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    private function getSession()
    {
        return $this->container->get('session');
    }

}
