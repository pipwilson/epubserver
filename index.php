<?php

//
// Lists the .epub files in a directory in Atom format
//
require_once("include/feedcreator.class.php"); // generates atom files
require_once("include/ebookRead.php");         // parses .epub
require_once("include/preg_find.php");         // searches directories for files

// where are the files?
$rootpath = "C:/dropbox/ebooks";

// look in a subdirectory if requested
$param = $_GET["dir"];
$param = str_replace(".", "", $param);

$epubdir = $rootpath.$param."/";

// check it exists
$dir = @opendir($epubdir) or die("Unable to open $rootpath");

// the request url without this filename
$scriptpath = explode("/", $_SERVER["REQUEST_URI"]);

// delete the last element
$scriptpath[count($scriptpath) - 1] = "";
$scriptpath = implode("/", $scriptpath);
$url = "http://" . $_SERVER["SERVER_NAME"].$scriptpath;
$scripturl = "http://" . $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

header('Content-type: application/xml');

//define channel
$atom = new UniversalFeedCreator();
$atom->useCached();
$atom->title = "My epub files"; // todo: replace with a $catalogname field
$atom->description = "epub files on my server";
$atom->link = "http://mydomain.net/";
$atom->syndicationURL = $scripturl;

// sort the files by date so the most recent is first in the feed
// preg_find(pattern, starting dir, args)
$files = preg_find('/\.epub$/', $epubdir, PREG_FIND_RECURSIVE|PREG_FIND_RETURNASSOC|PREG_FIND_SORTBASENAME|PREG_FIND_SORTASC);

$files = array_keys($files);

// add the files to the feed
foreach($files as $file) {

    if (is_file($file)) { //todo: replace this with a change to the preg_find pattern above
        $ebook = new ebookRead($file);

        //channel items/entries
        $item = new FeedItem();
        $item->title = $ebook->getDcTitle();
        $item->linktype = "application/epub+zip";
        $item->link = $file;
        $item->description = $ebook->getDcDescription();
        $item->source = "http://mydomain.net";
        $item->author = $ebook->getDcContributor();

        $atom->addItem($item);
    }
}

$atom->outputFeed("ATOM1.0");

closedir($dir);
#echo "Done!";

?>
