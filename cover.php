<?php

//
// Returns the cover of a given epub file
//
require_once("include/ebookRead.php");         // parses .epub

// get the name of the file we want the cover of
// er, and what's to stop us going up a directory, eh?
$filename = $_GET["filename"];
if(!$filename) {
    $filename = "Jules Verne - From the Earth to the Moon.epub";
}

$filename = str_replace(".", "", $filename);

$epubfile = $rootpath.$subdir.$filename;

#echo $epubfile;

$ebook = new ebookRead($epubfile);

# in theory the cover is referenced in the <metadata> section by something like:
# <meta name="cover" content="cover-image"/>
# and then in the manifest something like:
# <item id="cover-image" href="the_cover.jpg" media-type="image/jpeg"/>
# but in reality it all seems to be a bit hit and miss.

# try the <meta> route first, and then make some informed guesses

#$coverReference = $this->getTag($this->ebookData->metadata, 'dclanguage');

$cover = $ebook->getManifestById('cover');

# if the type doesn't start with 'image', look elsewhere
if(stripos($cover->type, 'image') === false) {
    #echo "cover is not an image, trying book-cover<br>";
    $cover = $ebook->getManifestById('book-cover');
    # if the type *still* doesn't start with 'image', look elsewhere
    if(stripos($cover->type, 'image') === false) {
        #echo "book-cover is not an image, trying cover-image<br>";
        $cover = $ebook->getManifestById('cover-image');
        if(stripos($cover->type, 'image') === false) {
            #echo "cover-image is not an image. giving up.<br>";
        }
    }
}

header("X-EPUBSERVER-DEBUG: $cover->type".", $cover->href");
header("Content-Type: $cover->type");
echo $ebook->getContentFile($cover->href);

?>