<?php

//
// Lists the .epub files in a directory in Atom format
//
require_once("include/feedcreator.class.php"); // generates atom files
require_once("include/ebookRead.php");         // parses .epub
require_once("include/preg_find.php");         // searches directories for files

// where are the files?
// note you will only be able to download them if this dir is available to your web server!
// this can be an absolute or relative location
$rootpath = "books";

// look in a subdirectory of the rootpath if requested
$param = $_GET["dir"];
$param = str_replace(".", "", $param);

# if the rootpath doesn't end in a slash, add it
if ('/' != $rootpath[strlen($rootpath)]) {
    $rootpath .= '/';
}

$epubdir = $rootpath.$param;

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
$atom->title = "Books in $epubdir"; // todo: replace with a $catalogname field
$atom->description = "epub files on my server";
$atom->link = "http://mydomain.net/";
$atom->syndicationURL = $scripturl;

// sort the files by date so the most recent is first in the feed
// preg_find(pattern, starting dir, args)
$files = preg_find('/\.epub$/', $epubdir, PREG_FIND_RECURSIVE|PREG_FIND_RETURNASSOC|PREG_FIND_SORTBASENAME|PREG_FIND_SORTDESC);

$files = array_keys($files);

// add the files to the feed
foreach($files as $file) {

    if (is_file($file)) {
        $ebook = new ebookRead($file);

        // entries
        $item = new FeedItem();
        $item->title = $ebook->getDcTitle();
        $item->linktype = "application/epub+zip";
        $item->link = $file;

        // <link rel="x-stanza-cover-image" type="image/jpeg" href="cover.php?filename=$file"/>

        $coverLink = new LinkItem();
        $coverLink->rel = "x-stanza-cover-image";
        $coverLink->type = "image/jpeg";
        $coverLink->href = "cover.php?filename=$file";

        $item->addLink($coverLink);

        $thumbnailLink = new LinkItem();
        $thumbnailLink->rel = "x-stanza-cover-image-thumbnail";
        $thumbnailLink->type = "image/jpeg";
        $thumbnailLink->href = "cover.php?filename=$file&amp;type=thumb";

        $item->addLink($thumbnailLink);

        // <link rel="x-stanza-cover-image" type="image/jpeg" href="/get/cover/3"/>
        // <link rel="x-stanza-cover-image-thumbnail" type="image/jpeg" href="/get/thumb/3"/>

        // if there is no description set it to be the same as the titke
        if($ebook->getDcDescription() == "") {
            $item->description = $item->title;
        } else {
            $item->description = strip_tags($ebook->getDcDescription());
        }

        // sometimes DcCreator is an array, so make sure we display in both cases
        if(is_array($ebook->getDcCreator())) {
            $item->author = implode(', ', $ebook->getDcCreator());
        } else {
            $item->author = $ebook->getDcCreator();
        }

        $atom->addItem($item);
    }
}

// can also saveFeed(format, filename) if we want
$atom->outputFeed("ATOM1.0");

closedir($dir);

?>
