<?php
namespace App\Controller;

class Developer extends \App\Common
{
    public function create($request, $response, $args)
    {
        // Initialize
        $database = $this->container->db;

        $commands = explode(';', file_get_contents('../data/create.sql'));
        foreach ($commands as $command) {
            $command = trim($command);
            if (empty($command)) {
                continue;
            }
            $database->query($command);
            if ($database->error()[1] !== null) {
                return $this->container->view->render($response, $database->error(), 500);
            }
        }

        // Render index view
        return $this->container->view->render($response, ['status' => 'OK', 'info' => 'database created'], 200);
    }


    public function drop($request, $response, $args)
    {
        return $this->container->view->render($response, ['status' => 'NOPE', 'info' => 'ani prd'], 403);

        $database = $this->container->db;
        $database->query("
            SET FOREIGN_KEY_CHECKS = 0;
            DROP TABLE IF EXISTS fee;
            DROP TABLE IF EXISTS fee_needed_for_league;
            DROP TABLE IF EXISTS tournament_belongs_to_league;
            DROP TABLE IF EXISTS league_fee;
            DROP TABLE IF EXISTS fee_payments;
            DROP TABLE IF EXISTS highschool;
            DROP TABLE IF EXISTS tournament_belongs_to_league_and_division;
            DROP TABLE IF EXISTS league;
            DROP TABLE IF EXISTS division;
            DROP TABLE IF EXISTS player;
            DROP TABLE IF EXISTS player_at_highschool;
            DROP TABLE IF EXISTS player_at_roster;
            DROP TABLE IF EXISTS player_at_team;
            DROP TABLE IF EXISTS roster;
            DROP TABLE IF EXISTS season;
            DROP TABLE IF EXISTS team;
            DROP TABLE IF EXISTS team_representative;
            DROP TABLE IF EXISTS tournament;
            DROP TABLE IF EXISTS user;
            DROP TABLE IF EXISTS user_has_privilege;
            DROP TABLE IF EXISTS token;
            SET FOREIGN_KEY_CHECKS = 1;
        ");
        if ($database->error()[1] !== null) {
            return $this->container->view->render($response, $database->error(), 500);
        }

        // Render index view
        return $this->container->view->render($response, ['status' => 'OK', 'info' => 'database dropped'], 200);
    }

    public function info($request, $response, $args)
    {
        return $this->container->view->render($response, ['status' => 'OK', 'info' => 'We\'re here'], 200);
    }

    public function healthcheck($request, $response, $args)
    {
        $out['database'] = [
            'status' => 'OK',
        ];
        $ok = true;

        $this->container->db->action(function ($db) use (&$out, &$ok) {
            $tables = \App\Model::usedTables();
            foreach ($tables as $table) {
                $db->query('SELECT count(*) FROM ' . $table);
                $err = $db->error();
                if (!empty($err[2])) {
                    $ok = false;
                    $out['database']['status'] = 'DOWN';
                    $out['database'][$table] = [
                        'status' => 'DOWN',
                        'error' => $err[2],
                    ];
                }
            }
            return false;
        });

        return $this->container->view->render(
            $response,
            ['status' => $ok ? 'OK' : 'FAILED', 'data' => $out],
            $ok ? 200 : 500
        );
    }
}
