<?php
namespace max_api\model;

use max_api\database\query;

class matches extends query
{
    /**
     * @return mixed| Load all League
     */
    public function getLeague()
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select(
                '*'
            )
            ->from(
                $query->quoteName("matches_league")
            )
            ->where(
                $query->quoteName("is_active") . " = " . $query->quote(1)
            );

        $query->setInsert();

        return $query->loadObjects();
    }

    /**
     * @param $league_id
     * @param $limit
     * @param $offset
     * @return mixed
     */
    public function getMatchesByLeague($league_id, $limit, $offset)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select([
                $query->quoteName("matches.id"),
                $query->quoteName("matches.title"),
                $query->quoteName("matches.time"),
                $query->quoteName("matches.stadium"),
                $query->quoteName("matches.score"),
                $query->quoteName("matches.league_id"),
                $query->quoteName("matches.team_guest_id"),
                $query->quoteName("matches.team_home_id"),
                $query->quoteName("matches.round_name"),
                $query->quoteName("matches.table_name"),
                $query->quoteName("matches.is_hot"),
                $query->quoteName("matches.is_end"),
            ])
            ->from(
                $query->quoteName("matches_match", "matches")
            )
            //Join team home
            ->select([
                $query->quoteName("team_home.name", "team_home_name"),
                $query->quoteName("team_home.avatar", "team_home_avatar"),
            ])
            ->join("LEFT", "`matches_team` AS `team_home` ON `team_home`.`id` = `matches`.`team_home_id`")
            //Join team guest
            ->select([
                $query->quoteName("team_guest.name", "team_guest_name"),
                $query->quoteName("team_guest.avatar", "team_guest_avatar"),
            ])
            ->join("LEFT", "`matches_team` AS `team_guest` ON `team_guest`.`id` = `matches`.`team_guest_id`")
            ->where(
                $query->quoteName("league_id") . " = " . $query->quote($league_id)
            )
            ->setLimit($limit, $offset)
            ->order(
                "time DESC"
            );


        $query->setQuery();

        return $query->loadObjects();
    }

    /**
     * @param $date
     * @param $limit
     * @param $offset
     * @param null $league_id
     * @return mixed
     */
    public function getMatchesByDate($date, $limit, $offset, $league_id = null)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select([
                $query->quoteName("matches.id"),
                $query->quoteName("matches.title"),
                $query->quoteName("matches.time"),
                $query->quoteName("matches.stadium"),
                $query->quoteName("matches.score"),
                $query->quoteName("matches.league_id"),
                $query->quoteName("matches.team_guest_id"),
                $query->quoteName("matches.team_home_id"),
                $query->quoteName("matches.round_name"),
                $query->quoteName("matches.table_name"),
                $query->quoteName("matches.is_hot"),
                $query->quoteName("matches.is_end"),
            ])
            ->from(
                $query->quoteName("matches_match", "matches")
            )
            //Join team home
            ->select([
                $query->quoteName("team_home.name", "team_home_name"),
                $query->quoteName("team_home.avatar", "team_home_avatar"),
            ])
            ->join("LEFT", "`matches_team` AS `team_home` ON `team_home`.`id` = `matches`.`team_home_id`")
            //Join team guest
            ->select([
                $query->quoteName("team_guest.name", "team_guest_name"),
                $query->quoteName("team_guest.avatar", "team_guest_avatar"),
            ])
            ->join("LEFT", "`matches_team` AS `team_guest` ON `team_guest`.`id` = `matches`.`team_guest_id`")
//            ->where(
//                $query->quoteName("time") . " LIKE " . $query->quote("$date%")
//            )

            ->where(
                $query->quoteName("time") . " BETWEEN DATE_SUB(" . $query->quote($date) . ",  INTERVAL 5 DAY ) AND DATE_ADD(" . $query->quote($date) . ",  INTERVAL 5 DAY )"
            )
            ->setLimit($limit, $offset)
            ->order(
                "time DESC"
            );

        if ($league_id) {
            $query->where(
                $query->quoteName("league_id") . " = " . $query->quote($league_id)
            );
        }

        $query->setQuery();

        return $query->loadObjects();
    }

    /**
     * @param $limit
     * @param $offset
     * @param null $league_id
     * @return mixed
     */
    public function getMatchesHot($limit, $offset, $league_id = null)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select([
                $query->quoteName("matches.id"),
                $query->quoteName("matches.title"),
                $query->quoteName("matches.time"),
                $query->quoteName("matches.stadium"),
                $query->quoteName("matches.score"),
                $query->quoteName("matches.league_id"),
                $query->quoteName("matches.team_guest_id"),
                $query->quoteName("matches.team_home_id"),
                $query->quoteName("matches.round_name"),
                $query->quoteName("matches.table_name"),
                $query->quoteName("matches.is_hot"),
                $query->quoteName("matches.is_end"),
            ])
            ->from(
                $query->quoteName("matches_match", "matches")
            )
            //Join team home
            ->select([
                $query->quoteName("team_home.name", "team_home_name"),
                $query->quoteName("team_home.avatar", "team_home_avatar"),
            ])
            ->join("LEFT", "`matches_team` AS `team_home` ON `team_home`.`id` = `matches`.`team_home_id`")
            //Join team guest
            ->select([
                $query->quoteName("team_guest.name", "team_guest_name"),
                $query->quoteName("team_guest.avatar", "team_guest_avatar"),
            ])
            ->join("LEFT", "`matches_team` AS `team_guest` ON `team_guest`.`id` = `matches`.`team_guest_id`")
            ->where(
                $query->quoteName("is_hot") . " = " . $query->quote(1)
            )
            ->setLimit($limit, $offset)
            ->order(
                "time DESC"
            );

        if ($league_id) {
            $query->where(
                $query->quoteName("league_id") . " = " . $query->quote($league_id)
            );
        }

        $query->setQuery();

        return $query->loadObjects();
    }

    /**
     * @param $limit
     * @param $offset
     * @param null $league_id
     * @return mixed
     */
    public function getMatchesEnd($limit, $offset, $league_id = null)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select([
                $query->quoteName("matches.id"),
                $query->quoteName("matches.title"),
                $query->quoteName("matches.time"),
                $query->quoteName("matches.stadium"),
                $query->quoteName("matches.score"),
                $query->quoteName("matches.league_id"),
                $query->quoteName("matches.team_guest_id"),
                $query->quoteName("matches.team_home_id"),
                $query->quoteName("matches.round_name"),
                $query->quoteName("matches.table_name"),
                $query->quoteName("matches.is_hot"),
                $query->quoteName("matches.is_end"),
            ])
            ->from(
                $query->quoteName("matches_match", "matches")
            )
            //Join team home
            ->select([
                $query->quoteName("team_home.name", "team_home_name"),
                $query->quoteName("team_home.avatar", "team_home_avatar"),
            ])
            ->join("LEFT", "`matches_team` AS `team_home` ON `team_home`.`id` = `matches`.`team_home_id`")
            //Join team guest
            ->select([
                $query->quoteName("team_guest.name", "team_guest_name"),
                $query->quoteName("team_guest.avatar", "team_guest_avatar"),
            ])
            ->join("LEFT", "`matches_team` AS `team_guest` ON `team_guest`.`id` = `matches`.`team_guest_id`")
            ->where(
                $query->quoteName("is_end") . " = " . $query->quote(1)
            )
            ->setLimit($limit, $offset)
            ->order(
                "time DESC"
            );

        if ($league_id) {
            $query->where(
                $query->quoteName("league_id") . " = " . $query->quote($league_id)
            );
        }

        $query->setQuery();

        return $query->loadObjects();
    }

    /**
     * @param $user_id
     * @param $match_id
     * @param $gcm_regid
     * @return bool
     */
    public function setUserFollow($user_id, $match_id, $gcm_regid)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->insert(
                $query->quoteName("matches_userfollow")
            )
            ->set([
                $query->quoteName("user_follow_id") . " = " . $query->quote($user_id),
                $query->quoteName("match_id") . " = " . $query->quote($match_id),
                $query->quoteName("gcm_regid") . " = " . $query->quote($gcm_regid)
            ]);

        return $query->setInsert();
    }

    /**
     * @param $league_id
     * @return mixed
     */
    public function getScoreBoardByLeague($league_id)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select([
                $query->quoteName("scoreboard.id"),
                '`scoreboard`.*'
            ])
            ->from(
                $query->quoteName("matches_scoreboard", "scoreboard")
            )
            //Join team home
            ->select([
                $query->quoteName("team.name", "team_name"),
                $query->quoteName("team.avatar", "team_avatar"),
            ])
            ->join("LEFT", "`matches_team` AS `team` ON `team`.`id` = `team`.`team_id`")
            //Join team guest
            ->select([
                $query->quoteName("league.name", "league_name"),
                $query->quoteName("league.avatar", "league_avatar"),
            ])
            ->join("LEFT", "`matches_league` AS `league` ON `league`.`id` = `scoreboard`.`league_id`")
            ->where(
                $query->quoteName("league_id") . " = " . $query->quote($league_id)
            )
            ->order(
                "mark DESC"
            );

        $query->setQuery();

        return $query->loadObjects();
    }

    /**
     * @param $user_id
     * @param $match_id
     * @return $this
     */
    public function deleteUserFollow($user_id, $match_id)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->delete(
                $query->quoteName("matches_userfollow")
            )
            ->where([
                $query->quoteName("user_follow_id") . " = " . $query->quote($user_id),
                $query->quoteName("match_id") . " = " . $query->quote($match_id),
            ]);

        if (!$query->setQuery()) {
            return false;
        }
        return true;
    }

    /**
     * @param $user_id
     * @param $match_id
     * @return bool
     */
    public function checkFollow($user_id, $match_id)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select("COUNT(id)")
            ->from("matches_userfollow")
            ->where([
                $query->quoteName("match_id") . " = " . $query->quote($match_id),
                $query->quoteName("user_follow_id") . " = " . $query->quote($user_id)
            ]);

        $query->setQuery();

        $check = $query->loadResult();

        if (!$check) {
            return false;
        }

        return true;
    }

    /**
     * @param $match_id
     * @return mixed
     */
    public function getUsersFollowByMatch($match_id)
    {
        $query = $this->_query;

        $query->getQuery();

        $query
            ->select("gcm_regid")
            ->from(
                $query->quoteName("matches_userfollow")
            )
            ->where(
                $query->quoteName("match_id") . " = " . $query->quote($match_id)
            );

        $query->setQuery();

        return $query->loadObjects();
    }
}
