<?php
  $dir='admin/files/';

  move_uploaded_file($_FILES["image"]["tmp_name"],$dir.$_FILES['image']['name']);

 ?>
