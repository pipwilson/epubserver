<?php

    require_once('include/ebookRead.php');

    function metadata($ebook){
        display("<b>Title:</b>", $ebook->getDcTitle());
        display("<b>Creator:</b>", $ebook->getDcCreator());
        display("<b>Description:</b>", $ebook->getDcDescription());
        display("<b>ISBN or ID:</b>", $ebook->getDcIdentifier());
        display("<b>Contributor(s):</b>", $ebook->getDcContributor());
        display("<b>Contributor(s) Role:</b>", $ebook->getDcContributor("Role"));
        display("<b>Language:</b>", $ebook->getDcLanguage());
        display("<b>Rights:</b>", $ebook->getDcRights());
        display("<b>Publisher</b>:", $ebook->getDcPublisher());
        display("<b>Subject:</b>", $ebook->getDcSubject());
        display("<b>Date:</b>", $ebook->getDcDate());
        display("<b>Type:</b>", $ebook->getDcType());
        display("<b>Format:</b>", $ebook->getDcFormat());
        display("<b>Sources:</b>", $ebook->getDcSource());
        display("<b>Relation:</b>", $ebook->getDcRelation());
        display("<b>Coverage:</b>", $ebook->getDcCoverage());
    }

    function display($title, $data){
    $info = "";

    if(is_array($data)){
        foreach($data as $element){
            if($info == "")
                $info = $element;
            else
                $info = $info.", ".$element;
        }
        $data = $info;
    }

    if($data != "")
        echo $title." ".$data."\n <br />";

    }

    $ebookfile = "C:\\dropbox\\ebooks\\Beautiful_Code.epub";

    //read our epub file
    $ebook = new ebookRead($ebookfile);

    //$creator = $ebook->ebookData->creator;
    $creator = $ebook->getDcCreator();
    print_r($creator);
    echo "<br>";
    echo $creator[0]

    //metadata($ebook);

?>