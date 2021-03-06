<?php
  /*
   * Github api interface
   */
  chdir(dirname(__FILE__));
  date_default_timezone_set('America/Los_Angeles');

  $gUrl = 'https://api.github.com';
  $repoUrl = "{$gUrl}/repos/photo/frontend";
  $ch = curl_init($repoUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $repoJson = curl_exec($ch);
  curl_close($ch);

  $commitsUrl = "{$repoUrl}/commits";
  $ch = curl_init($commitsUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $commitsJson = curl_exec($ch);

  $repo = json_decode($repoJson, 1);
  $commits = json_decode($commitsJson, 1);

  if(empty($repo) || empty($commits))
    die();

  $out = array();
  $out['info'] = array(
    'watchers' => $repo['watchers'],
    'issues' => $repo['open_issues'],
    'forks' => $repo['forks']
  );
  $out['commits'] = array();
  foreach($commits as $cnt => $commit)
  {
    if($cnt > 4)
      break;

    $time = strtotime($commit['commit']['author']['date']);
    if($time < strtotime('Today'))
      $timefmt = date('m/d/y', $time);
    else
      $timefmt = date('g:ia', $time);

    $out['commits'][] = array(
      'message' => htmlspecialchars($commit['commit']['message']),
      'messagefmt' => htmlspecialchars(substr($commit['commit']['message'], 0, 15)).'...',
      'name' => $commit['author']['login'],
      'email' => $commit['commit']['author']['email'],
      'time' => $time,
      'timefmt' => $timefmt,
      'url' => sprintf('https://github.com/photo/frontend/commit/%s', $commit['sha'])
    );
  }

  if(count($out['commits']) > 0)
    file_put_contents('output/github.json', json_encode($out));
?>
