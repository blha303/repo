<?php
// Created by Steven Smith (blha303) on 20/08/2014 and released under the MIT license

// Open source
if (isset($_GET['print'])) {
highlight_file(__FILE__);
die();
}

// Utility function to render file sizes
function humanFileSize($size,$unit="") {
  if( (!$unit && $size >= 1<<30) || $unit == "G")
    return number_format($size/(1<<30),2)."G";
  if( (!$unit && $size >= 1<<20) || $unit == "M")
    return number_format($size/(1<<20),2)."M";
  if( (!$unit && $size >= 1<<10) || $unit == "K")
    return number_format($size/(1<<10),2)."K";
  return number_format($size);
}

// Generating results for formatting as json or html
$results = array("results" => array(), "error" => "");
$resultbool = false;
if (!isset($_GET["q"]) || empty($_GET["q"])) {
    $results["error"] = "Must provide search parameter (q)";
} else {
    $di = new RecursiveDirectoryIterator(".", FilesystemIterator::SKIP_DOTS);
    foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
        if (strpos(strtolower($filename), strtolower($_GET["q"])) !== false) {
            $resultbool = true;
            $results["results"][] = array(substr($filename, 2), date("Y-m-d H:i", $file->getMTime()), humanFileSize($file->getSize()));
        }
    }
    if (!$resultbool) $results["error"] = "No results.";
}

// Getting the accept header
foreach (apache_request_headers() as $header => $value) {
    if ($header == "Accept") {
        $accept = $value;
    }
}
// Formatting results
if (isset($_GET['json']) || strpos($accept, "application/json") !== false) {
    header("Content-Type: application/json");
    echo json_encode($results);
} else {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <title>Search results</title>
  <link rel="stylesheet" href="/theme/style.css" type="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1"> </head>
 <body>
<?php include("theme/header.html"); ?>
  <table id="indexlist">
    <tr class="indexhead"><th class="indexcolicon"><img src="/theme/icons/blank.png" alt="[ICO]" /></th><th class="indexcolname">Name</th><th class="indexcollastmod">Last modified</th><th class="indexcolsize">Size</th></tr>
<?php
if (!empty($results["error"])) {
    echo $results["error"];
} else {
    foreach ($results["results"] as $result) {
        echo "<tr><td class='indexcolicon'><img src='/theme/icons/unknown.png' alt='[SEARCH]' /><td class='indexcolname'><a href='./" . $result[0] . "'>" . $result[0] . "</a></td><td class='indexcollastmod'>" . $result[1] . "  </td><td class='indexcolsize'>" . $result[2] . "</td></tr>\n";
    }
}
?>
</table>
<?php
include("theme/footer.html");
if (isset($_GET['q'])) { ?>
<script>
document.getElementsByName('q')[0].value = "<?php echo $_GET['q']; ?>";
</script>
<?php }
?>
</body></html>
<?php }
