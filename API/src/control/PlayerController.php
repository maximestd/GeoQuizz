<?php

namespace geo\control;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \geo\model\Photo;
use \geo\model\Partie;
use \geo\model\User;
use \geo\model\Serie;
use Ramsey\Uuid\Uuid;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException ;
use Firebase\JWT\BeforeValidException;

class PlayerController {

    public function getSeries(Request $req, Response $resp, $args){
        
        $series = Serie::all();
        $t = count($series);
        $tabseries = [
            "type"=>'collection',
            "meta"=>[$date=date('d/m/y'),"count"=>$t],
            "series"=>$series
        ];
        $resp = $resp->withStatus(200);
        $resp = $resp->withJson($tabseries);
        return $resp;
    }

    public function getSerieId(Request $req, Response $resp, $args){
        try {
            $serie = Serie::findorFail($args['id']);
        } catch (ModelNotFoundException $e) {
            $resp = $resp->withStatus(404);
            $resp = $resp->withJson(array('type' => 'error', 'error' => 404, 'message' => 'Ressource non disponible : /series/'.$args['id']));
            return $resp;
        }
        $tabserie=[
            "type"=>"ressource",
            "meta"=>[$date=date('d/m/y')],
            "serie"=>$serie,
        ];
        $resp = $resp->withStatus(200);
        $resp = $resp->withJson($tabserie);
        return $resp;
    }

    public function getPhotos(Request $req, Response $resp, $args){
        try {
            $photos = Photo::where('id_ville', $args['id'])->orderByRaw('RAND()')->take(10)->get();
        } catch (ModelNotFoundException $e) {
            $resp = $resp->withStatus(404);
            $resp = $resp->withJson(array('type' => 'error', 'error' => 404, 'message' => 'Ressource non disponible : /series/'.$args['id']));
            return $resp;
        }
        $t = count($photos);
        $tabphotos = [
            "type"=>'collection',
            "meta"=>[$date=date('d/m/y'),"count"=>$t],
            "photos"=>$photos
        ];
        $resp = $resp->withStatus(200);
        $resp = $resp->withJson($tabphotos);        
        return $resp;
    }

    public function getParties(Request $req, Response $resp, $args){
        $parties = Partie::all();
        $t = count($parties);
        $tabpartie = [
            "type"=>'collection',
            "meta"=>[$date=date('d/m/y'),"count"=>$t],
            "parties"=>$parties
        ];
        $resp = $resp->withStatus(200);
        $resp = $resp->withJson($tabpartie);
        return $resp;
    }

    public function getPartieId(Request $req, Response $resp, $args){
        try {
            $partie = Partie::findorFail($args['id']);
        } catch (ModelNotFoundException $e) {
            $resp = $resp->withStatus(404);
            $resp = $resp->withJson(array('type' => 'error', 'error' => 404, 'message' => 'Ressource non disponible : /parties/'.$args['id']));
            return $resp;
        }
        $tabpartie=[
            "type"=>"ressource",
            "meta"=>[$date=date('d/m/y')],
            "partie"=>$partie
        ];
        $resp = $resp->withStatus(200);
        $resp = $resp->withJson($tabpartie);
        return $resp;
    }

    public function postPartie(Request $req, Response $resp, $args) {
        $parsedBody = $req->getParsedBody();
        $partie = new Partie;
        $uuid4 = Uuid::uuid4();
        $partie->id = $uuid4;
        $secret = 'geoquizz';
        $token =JWT::encode( ['iss'=>'http://api.geoquizz.local:10101/parties/'.$partie->id,
            'aud'=>'http://api.geoquizz.local:10101/',
            'iat'=>time(),
            'exp'=>time()+3600,
            'id'=>(string) $partie->id],
            $secret,'HS512');
        $partie->token = $token;
        $partie->pseudo = filter_var($parsedBody['pseudo'], FILTER_SANITIZE_SPECIAL_CHARS);
        $partie->statut = 0;
        $partie->id_serie = filter_var($parsedBody['id_serie'],FILTER_SANITIZE_SPECIAL_CHARS);
        $partie->save();
        $resp = $resp->withStatus(201);
        $resp = $resp->withJson(array('id' => $partie->id, 'pseudo' => $partie->pseudo, 'id_serie' => $partie->id_serie, 'token' => $partie->token));
        return $resp;
    }

    public function putPartie(Request $req, Response $resp, $args) {

        $parsedBody = $req->getParsedBody();
        $partie = Partie::find($parsedBody['id']);
        $partie->score = filter_var($parsedBody['score'],FILTER_SANITIZE_SPECIAL_CHARS);
        $partie->statut = filter_var($parsedBody['statut'],FILTER_SANITIZE_SPECIAL_CHARS);
        $partie->save();
        $resp = $resp->withStatus(201);
        $resp = $resp->withJson(array('id' => $partie->id, 'score' => $partie->score, 'statut' => $partie->statut));
        return $resp;
    }
}