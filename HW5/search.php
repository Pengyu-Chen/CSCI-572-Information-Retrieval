<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
include('SpellCorrector.php');
include('sg.php');
ini_set('memory_limit', '-1');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$correct = isset($_REQUEST['cor']);
$results = false;
$rankm="Lucene";
$correctflag=false;

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
  // $new_query=$new_query.SpellCorrector::correct($query);
  $old_query=strtolower($query);
  $arr =  explode(" ", $query);
  $new_query="";
  
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  foreach($arr as $v){
    
    $new_query=$new_query.SpellCorrector::correct($v)." ";}

    $new_query=rtrim($new_query);

  if($new_query!=$old_query){
    $correctflag=true;
    
} 
  // if magic quotes is enabled then stripslashes will be needed
 

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
    if($correctflag){
    $results = $solr->search($new_query, 0, $limit, $param);}
    else{
    $results = $solr->search($query, 0, $limit, $param);
    }
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
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="http://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="suggester.js"></script>
  </head>
  <body>
    <form  align="center" accept-charset="utf-8" method="get" >
      <label for="q">Search:</label>
      <input style="height: 25px; width: 300px" id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input style="width: 50px" type="submit" value="Search"/>
      <br/>
      <input type="radio" name="rankmethod" value="Lucene" <?php if($rankm=="Lucene") echo "checked='checked'"; ?> >Lucene
      &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
      <input type="radio" name="rankmethod" value="PageRank" <?php if($rankm=="PageRank") echo "checked='checked'"; ?> >PageRank


    </form>
<?php

// $arrayFromCSV = array_map('str_getcsv', file('/Users/pengyuchen/NYD/NYDMap.csv'));
$csvMap = Array();
$fh= file('/Users/pengyuchen/NYD/NYDMap.csv');

foreach ($fh as $line) {
    $line= explode(',', $line);
    $csvMap[$line[0]] = $line[1];
    }
// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);

if ($correctflag) {
     

    ?>
    <th>Showing results of</th>
    <a href="http://localhost/search.php?q=<?php echo htmlentities($new_query); ?>"><?php echo $new_query; ?></a>
  </br>
<!--     <th>Search instead for</th>
    <a href="http://localhost/search.php?q=<?php echo htmlentities($query); ?>" name="cor"><?php echo $query; ?></a> -->
    
    <?php
    $total = (int) $results->response->numFound;
    $start = min(1, $total);
    $end = min($limit, $total);
}
    
 
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
    
    $id=explode("/", $doc->id);
    $id=$id[count($id)-1];

    $url=$doc->og_url;
    if(!$url){
      $url=$csvMap[$id];
    }

    $description=$doc->og_description;
    $url = urldecode($url);

?>
          <tr>
          	<th>Title&nbsp&nbsp&nbsp&nbsp</th>
            <td><a href="<?php echo $url; ?>" STYLE="text-decoration:none"><font size='4px'><?php echo htmlspecialchars($title, ENT_NOQUOTES, 'utf-8'); ?></font></a></td>
          </tr> 
          <tr>
          	<th>URL&nbsp&nbsp&nbsp&nbsp</th>
            <td><a href="<?php echo $url; ?>" STYLE="text-decoration:none"><?php echo $url ?></a></td>
          </tr>
          <tr>
          	<th>ID&nbsp&nbsp&nbsp&nbsp</th>
          	<td><?php echo $id; ?></td>
          </tr>
<!--           <tr>
          	<th>Description&nbsp&nbsp&nbsp&nbsp</th>
          	<td><?php echo $description? $description : "N/A"; ?></td>
          </tr> -->
          <tr>
            <th>Snippet&nbsp&nbsp&nbsp&nbsp</th>
            <td><?php echo generateSnippet($doc->id,$query)?></td>
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
