<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('/Users/pengyuchen/Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  $param = [];
  $rankm= $_GET['rankmethod'];
  if ($rankm == "PageRank") {
        $param["sort"] ="pageRankFile desc";
    }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $results = $solr->search($query, 0, $limit, $param);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client</title>
  </head>
  <body>
    <form  align="center" accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input style="height: 25px; width: 300px" id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input style="width: 40px" type="submit"/>
      <br/>
      <input type="radio" name="rankmethod" value="Lucene" >Lucene
      &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
      <input type="radio" name="rankmethod" value="PageRank">PageRank


    </form>
<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="border: 1px solid black; text-align: left">
<?php
    // iterate document fields / values
    $title=$doc->title;
    $url=$doc->og_url;
    $id=$doc->id;
    $description=$doc->og_description;
    $url = urldecode($url);

?>
          <tr>
          	<th>Title&nbsp&nbsp&nbsp&nbsp</th>
            <td><a href="<?php echo $url; ?>"><?php echo htmlspecialchars($title, ENT_NOQUOTES, 'utf-8'); ?></a></td>
          </tr> 
          <tr>
          	<th>URL&nbsp&nbsp&nbsp&nbsp</th>
            <td><a href="<?php echo $url; ?>"><?php echo $url? $url : "N/A"; ?></a></td>
          </tr>
          <tr>
          	<th>ID&nbsp&nbsp&nbsp&nbsp</th>
          	<td><?php echo $id; ?></td>
          </tr>
          <tr>
          	<th>Description&nbsp&nbsp&nbsp&nbsp</th>
          	<td><?php echo $description? $description : "N/A"; ?></td>
          </tr>

        </table>
      </li>
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>
