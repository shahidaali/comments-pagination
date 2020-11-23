<?php
require_once "config.php";
require_once "pagination.class.php";
// echo '<pre>';

$post_id = 1;
$paginator = new Comments_Paginator($mysqli, $post_id, [
	'urlPattern' => '?page=(:num)',
	'currentPage' => isset($_GET['page']) ? $_GET['page'] : 1,
	'itemsPerPage' => 2,
]);
// print_r($paginator->getComments());
?>

<html>
  <head>
    <!-- The default, built-in template supports the Twitter Bootstrap pagination styles. -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <style type="text/css">
    	.comments {
    		margin: 15px;
    	}
    	.comment-child {
    		
    	}
    	.comment-item {
    		padding: 10px;
    		border-radius: 5px;
    		margin: 5px 0px;
    		border: 1px solid #ccc;
    	}
    </style>
  </head>
  <body>

    <?php 
    	echo $paginator->getCommentsHtml();
      // Example of rendering the pagination control with the built-in template.
      // See below for information about using other templates or custom rendering.

      echo $paginator->getLinks(); 
    ?>
    
  </body>
</html>