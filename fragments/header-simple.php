<?php
$projectName = explode('/', $_SERVER['REQUEST_URI'])[1]; // ex.: DockThor-3.0
?>
<div class="dockthor-header">
  <div class="row header-logo">
    <div class="col-md-3">
      <!-- Dockthor Logo -->
      <h1>
        <a href="#">
          <img class = "image-responsive col-lg-12 col-md-12 col-sm-12 col-xs-12" src="/<?php echo $projectName?>/images/header/logo_teste.png">
          <img class = "image-responsive col-lg-12 col-md-12 col-sm-12 col-xs-12" src="/<?php echo $projectName?>/images/header/slogan.png">
        </a>
      </h1>
      <!-- logo3 -->
    </div>
    <div class="image-responsive col-md-3 pull-right" style="background: linear-gradient(to left right, #5bc0de, #040404);">
      <img src="/<?php echo $projectName?>/images/header/img_right.png">
    </div>
  </div>
</div>