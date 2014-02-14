function showLeague() {
  showLeagueForm.submit();
}

function editTeam(team_id) {
  var x = document.getElementsByClassName('edit_team_'+team_id);
  for (var i = 0; i <= x.length - 1; i++) {
    if (x.item(i).disabled) x.item(i).disabled = false;
      else x.item(i).disabled = true;
  }
  var x = document.getElementById('updateTeamsSubmit');
  x.disabled = false;
}

function editGame(game_id) {
  var x = document.getElementById('updateGamesSubmit');
  x.disabled = false;
  if (game_id < 0) return;
  var x = document.getElementsByClassName('edit_game_'+game_id);
  for (var i = 0; i <= x.length - 1; i++) {
    if (x.item(i).disabled) x.item(i).disabled = false;
      else x.item(i).disabled = true;
  }
}
