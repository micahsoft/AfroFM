<?php

class Pages {

  var $data = array();

  function Pages(){
     // $this->getPages();
  }
  function getPages() {
      global $dbase, $session;

      $query = "SELECT * FROM ".TBL_PAGES." WHERE language = ".$session->language." ORDER BY title ASC";
      $result = $dbase->query($query);
  
      $i = 0;
      while($row = $dbase->fetch($result)) {

            $pages[$i]["id"] = $row['id'];
            $pages[$i]["title"] = $row['title'];
            $pages[$i]["description"] = $row['description'];
            $pages[$i]["created"] = $row['created'];
            $pages[$i]["updated"] = $row['updated'];
            $i++;
      }

      $this->data = $pages;
  }

  function getPage($id) {
      global $dbase;

      $query = "SELECT * FROM ".TBL_PAGES." WHERE id = ".$id."";
      $result = $dbase->query($query);

      $row = $dbase->fetch($result);
      $page["id"] = $row['id'];
      $page["title"] = $row['title'];
      $page["description"] = $row['description'];
      $page["created"] = $row['created'];
      $page["updated"] = $row['updated'];
      
      $this->data = $page;
  }
}

?>
