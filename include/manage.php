<?php

  include "include/database.php";
  include "include/functions.php";

  if (isset($_SESSION["user_id"])) {
    $user_id   = $_SESSION["user_id"];
    $user_name = $_SESSION["user_name"];
  } else {
    header("Location: index.html");
  }

  $result = queryMysql("SELECT username, name
                        FROM users
                        WHERE id = $user_id");
  $row = mysql_fetch_assoc($result);
  $username = $row["username"];
  $user_name = $row["name"];

  $msg = $errMsg = "";

  if (isset($_POST["create_new_league"])) {
    $new_league_name = sanitizeString($_POST["new_league_name"]);
    if ($new_league_name) {
      $query = "SELECT name FROM leagues
                WHERE user_id = $user_id AND name = '$new_league_name'";
      $result = queryMysql($query);
      $row = mysql_fetch_assoc($result);
      if ($row["name"]) {
        $errMsg .= " $new_league_name already exists.";
      } else {
        $query = "INSERT INTO leagues (user_id, name)
                  VALUES ($user_id, '$new_league_name')";
        queryMysql($query);
        $msg .= " New league, $new_league_name, created.";
        $result = queryMysql("SELECT LAST_INSERT_ID()");
        $row = mysql_fetch_assoc($result);
        //$_POST["league_id"] = $row["LAST_INSERT_ID()"];
        $_SESSION["league_id"] = $row["LAST_INSERT_ID()"];
        header("Location:".$_SERVER['PHP_SELF']);
      }
    } else {
      $errMsg .= " Need a name for your new league.";
    }
  }

  if (isset($_POST["delete_league"])) {
    $league_id = $_POST["delete_league"];
    $league_name = $_POST["league_name"];
    queryMysql("DELETE FROM leagues WHERE id = $league_id");
    queryMysql("DELETE FROM teams WHERE league_id = $league_id");
    queryMysql("DELETE FROM games WHERE league_id = $league_id");
    queryMysql("DELETE FROM tables WHERE league_id = $league_id");
    $msg .= "$league_name deleted.";
    unset($_SESSION["league_id"], $_SESSION["league_to_show"]);
    header("Location:".$_SERVER['PHP_SELF']);
  }

  $result = queryMysql("SELECT id, name FROM leagues
                        WHERE user_id = $user_id ORDER BY name");
  while ($row = mysql_fetch_assoc($result)) {
    $league_ids[] = $row["id"];
    $leagues[] = $row["name"];
  }

  if (isset($_POST["add_team"])) {
    $league_id = $_POST["league_id"];
    $new_team_name = $_POST["new_team_name"];
    if ($new_team_name) {
      $query = "INSERT INTO teams (league_id, name)
                VALUES ($league_id, '$new_team_name')";
      queryMysql($query);
      $query = "SELECT id FROM teams
                WHERE league_id = $league_id AND name = '$new_team_name'";
      $result = queryMysql($query);
      $row = mysql_fetch_assoc($result);
      $team_id = $row["id"];
      $query = "INSERT INTO tables (league_id, team_id)
                VALUES ($league_id, $team_id)";
      queryMysql($query);
      update_table($league_id);
      $msg = "New team, $new_team_name, added to the league.";
      header("Location:".$_SERVER['PHP_SELF']);
    } else {
      $errMsg = "Need a name for your new team.";
    }
  }

  if (isset($_POST["update_teams"])) {
    $league_id = $_POST["league_id"];
    if (isset($_POST["team_id"])) {
      foreach ($_POST["team_id"] as $index => $team_id) {
        $team_name = $_POST["team_name"][$index];
        $team_group = $_POST["team_group"][$index];
        $query = "UPDATE teams
                  SET name = '$team_name', _group = '$team_group'
                  WHERE id = $team_id";
        queryMysql($query);
      }
      $msg .= "Team(s) updated. ";
    }
    if (isset($_POST["delete_team"])) {
      foreach ($_POST["delete_team"] as $team_id) {
        $query = "DELETE FROM games
                  WHERE home_id = $team_id OR away_id = $team_id";
        queryMysql($query);
        $query = "DELETE FROM teams
                  WHERE id = $team_id";
        queryMysql($query);
        update_table($league_id);
      }
      $msg .= "Team(s) deleted. ";
    }
    header("Location:".$_SERVER['PHP_SELF']);
  }

  do if (isset($_POST["add_game"]) or isset($_POST["update_games"])) {
    if (!isset($_POST["home_id"]) or !isset($_POST["away_id"])) {
      $errMsg = "Team(s) not given.";
      break;
    }
    $league_id = $_POST["league_id"];
    if ($_POST["round"] == '') $round = 0;
      else $round = $_POST["round"];
    $home_id = $_POST["home_id"];
    $away_id = $_POST["away_id"];
    if ($home_id == $away_id) {
      $errMsg = "A team cannot play against itself.";
      break;
    }
    $home_goals = $_POST["home_goals"];
    $away_goals = $_POST["away_goals"];
    if (isset($_POST["add_game"])) {
      if (!is_numeric($home_goals) or !is_numeric($away_goals)) {
        if (!is_numeric($home_goals)) $home_goals = "NULL";
        if (!is_numeric($away_goals)) $away_goals = "NULL";
        $msg .= " Not all goals were provided. Game scheduled.";
      }
      $date = $_POST["date"];
      $query = "INSERT INTO games (league_id, round,
                                   home_id, away_id,
                                   home_goals, away_goals,
                                   date)
                VALUES ($league_id, $round,
                        $home_id, $away_id,
                        $home_goals, $away_goals,
                        '$date')";
      queryMysql($query);
      $msg .= " New game added.";
    }
    if (isset($_POST["update_games"])) {
      $round = $_POST["round"];
      $date = $_POST["date"];
      foreach ($_POST["game_id"] as $index => $game_id) {
        if (isset($_POST["delete_game"][$index])) {
          $query = "DELETE FROM games WHERE id = $game_id";
        } else {
          $hgi = $home_goals[$index]; if (!is_numeric($hgi)) $hgi = "NULL";
          $agi = $away_goals[$index]; if (!is_numeric($agi)) $agi = "NULL";
          $query = "UPDATE games SET round = $round[$index],
                                     home_goals = $hgi,
                                     away_goals = $agi,
                                     date = '$date[$index]'
                    WHERE id = $game_id";
        }
        queryMysql($query);
      }
      $msg = "Game(s) updated.";
    }
    update_table($league_id);
    header("Location:".$_SERVER['PHP_SELF']);
  } while(false);

  function update_table($league_id) {
    $result = queryMysql("SELECT id, name FROM teams
                          WHERE league_id = $league_id");
    while ($row = mysql_fetch_assoc($result)) {
      $team_ids[] = $row["id"];
      $wins[$row["id"]] = 0;
      $losses[$row["id"]] = 0;
      $draws[$row["id"]] = 0;
      $goals_scored[$row["id"]] = 0;
      $goals_against[$row["id"]] = 0;
    }
    $query = "SELECT team_home.name, home_goals,
                     team_away.name, away_goals,
                     team_home.id, team_away.id
              FROM games
              INNER JOIN teams team_home ON (games.home_id = team_home.id)
              INNER JOIN teams team_away ON (games.away_id = team_away.id)
              WHERE games.league_id = $league_id
              ";
    $result = queryMysql($query);
    while ($row = mysql_fetch_row($result)) {
      if ($row[1] == "" or $row[3] == "") continue;
      $goals_scored[$row[4]] += $row[1];
      $goals_scored[$row[5]] += $row[3];
      $goals_against[$row[4]] += $row[3];
      $goals_against[$row[5]] += $row[1];
      if ($row[1] >  $row[3]) {
        $wins[$row[4]] += 1;
        $losses[$row[5]] += 1;
      }
      if ($row[3] >  $row[1]) {
        $wins[$row[5]] += 1;
        $losses[$row[4]] += 1;
      }
      if ($row[1] ==  $row[3]) {
        $draws[$row[4]] += 1;
        $draws[$row[5]] += 1;
      }
    }
    if (isset($team_ids)) {
      foreach ($team_ids as $team_id) {
        $points = 3*$wins[$team_id]+$draws[$team_id];
        $goal_difference = $goals_scored[$team_id]-$goals_against[$team_id];
        $query = "UPDATE tables SET ";
        $query .= "wins=$wins[$team_id], ";
        $query .= "draws=$draws[$team_id], ";
        $query .= "losses=$losses[$team_id], ";
        $query .= "goals_scored=$goals_scored[$team_id], ";
        $query .= "goals_against=$goals_against[$team_id], ";
        $query .= "points=$points, goal_difference=$goal_difference ";
        $query .= "WHERE league_id = $league_id AND team_id = $team_id";
        queryMysql($query);
      }
      $msg .= " Table updated.";
    }
  }

  # If a league is requested, load its info and teams

  //if (isset($_POST["league_id"]))
  if (isset($_SESSION["league_id"]))
    $_SESSION["league_to_show"] = $_SESSION["league_id"];

  if (isset($_POST["league_to_show"])) {
    $_SESSION["league_to_show"] = sanitizeString($_POST["league_to_show"]);
    header("Location:".$_SERVER['PHP_SELF']);
  }

  if (isset($_SESSION["league_to_show"])) {
  //if (isset($_SESSION["league_to_show"])) {
    $league_id = $_SESSION["league_to_show"];
    $_SESSION["league_id"] = $league_id;
    $result = queryMysql("SELECT name FROM leagues WHERE id = $league_id");
    $row = mysql_fetch_assoc($result);
    $league_name = $row["name"];
    $result = queryMysql("SELECT id, name, _group FROM teams
                          WHERE league_id = $league_id ORDER BY name");
    $team_ids = array();
    $team_names = array();
    $team_groups = array();
    while ($row = mysql_fetch_assoc($result)) {
      $team_ids[] = $row["id"];
      $team_names[] = $row["name"];
      $team_groups[] = $row["_group"];
    }
  }

  //print_r($_POST);
  //print_r($_SESSION);

?>
