<?php
namespace App\Controller;

use App\Exception\Http\Http404;
use App\Model\PlayerAtRoster;

class Roster extends \App\Common
{
    public function create(\App\Request $request, $response, $args)
    {
        list($teamId, $tournamentBelongsToLeagueAndDivisionId) = $request->requireParams(['team_id', 'tournament_belongs_to_league_and_division_id']);
        $roster = \App\Model\Roster::create($teamId, $tournamentBelongsToLeagueAndDivisionId);
        $roster->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Roster created', 'data' => $roster->getData()],
            200
        );
    }

    public function remove(\App\Request $request, $response, $args)
    {
        list($rosterId) = $request->requireParams(['roster_id']);
        $roster = \App\Model\Roster::loadById($rosterId);
        if (!$roster) {
            throw new Http404("Wrong roster_id");
        }
        $roster->delete();
        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Team roster deleted'],
            200
        );
    }

    public function addPlayer(\App\Request $request, $response, $args)
    {
        list($rosterId, $playerId) = $request->requireParams(['roster_id', 'player_id']);
        $roster = \App\Model\Roster::loadById($rosterId);
        if (!$roster) {
            throw new Http404("Wrong roster_id");
        }

        if (PlayerAtRoster::exists(['player_id' => $playerId, 'tournament_id' => $roster->getTournamentId()], ["[>]roster" => ["roster_id" => "id"]])) {
            throw new Http400("Player is already on roster of another team");
        }

        $roster = PlayerAtRoster::create($playerId, $rosterId);
        $roster->save();

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player added', 'data' => $roster->getData()],
            200
        );
    }

    public function removePlayer(\App\Request $request, $response, $args)
    {
        list($rosterId, $playerId) = $request->requireParams(['roster_id', 'player_id']);
        if (!\App\Model\Roster::exists(["id" => $rosterId])) {
            throw new Http404("Wrong roster_id");
        }

        $playerRoster = PlayerAtRoster::load(['player_id' => $playerId, 'roster_id' => $rosterId]);
        if (empty($playerRoster)) {
            throw new Http400("Player is already on roster of another team");
        }

        foreach ($playerRoster as $roster) {
            $roster->delete();
        }

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'info' => 'Player removed from roster'],
            200
        );
    }
}