<?php

//
// Lists the .epub files in a directory in Atom format
//
require_once("include/feedcreator.class.php"); // generates atom files
require_once("include/ebookRead.php");         // parses .epub
require_once("include/preg_find.php");         // searches directories for files

// where are the files?
$rootpath = "C:\dropbox\ebooks";

// look in a subdirectory if requested
$param = $_GET["dir"];
$param = str_replace(".", "", $param);

$epubdir = $rootpath.$param."/";

// check it exists
$dir = @opendir($epubdir) or die("Unable to open $rootpath");

//while ($p = readdir($dir)) {
//    if (is_file($p) == FALSE && $p != "." && $p != "..") {

        //$path = $rootpath . $p . "/";

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
        $atom->syndicationURL = $scripturl

        // sort the files by date so the most recent is first in the feed
        // preg_find(pattern, starting dir, args)
        $files = preg_find('/./', $epubdir, PREG_FIND_RECURSIVE|PREG_FIND_RETURNASSOC |PREG_FIND_SORTMODIFIED|PREG_FIND_SORTDESC);
        $files = array_keys($files);

        // add the files to the feed
        foreach($files as $file) {

            if (preg_match("/\.epub$/", $file)) { //todo: replace this with a change to the preg_find pattern above
                $ebook = new ebookRead($ebookfile);

                //channel items/entries
                $item = new FeedItem();
                $item->title = $ebook->getDcTitle();
                $item->linktype = "application/epub+zip";
                $item->link = $file;
                $item->description = $ebook->getDcDescription();
                $item->source = "http://mydomain.net";
                $item->author = $ebook->getDcContributor();


                $xml->startElement('item');
                $xml->writeElement('title', $ThisFileInfo['tags']['id3v2']['title'][0]);

                $xml->writeElement('link', $url . $param . "/" . basename($file));

                //Wed, 29 Apr 2009 16:32:52 GMT
                $xml->writeElement('pubDate', date("D, d M Y H:i:s T", filemtime($file)));

                $xml->startElement('guid');
                $xml->writeAttribute('isPermaLink', 'false');
                $xml->text($file);
                $xml->endElement(); //</guid>



                $xml->startElement('enclosure');
                $xml->writeAttribute('url', $url . $param . "/" . basename($file));
                $xml->writeAttribute('length', filesize($file));
                $xml->writeAttribute('type', 'audio/mpeg');
                $xml->endElement(); //</enclosure>

                $xml->endElement(); //</item>
            }
        }
        //closedir($dir_handle);

        $xml->endElement(); // </channel>
        $xml->endElement(); // </rss>
        echo $xml->outputMemory(true);

        //$f = $path."/".$p.".rss";
        //$fh = fopen($f, 'w') or die ("can't open file ".$f);
        //fwrite($fh, $xml->outputMemory(true));
        //fclose($fh);
    //}

//}

closedir($dir);
#echo "Done!";

?>
