<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html;">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BALI SUN TOURS - Help Center</title>

	<link rel="icon" href="<?=URL_BASE_ASSETS?>img/logo-single-2025.ico" type="image/x-icon"/>
	<link rel="stylesheet" href="<?=URL_BASE_ASSETS?>css/bootstrap.min.css?<?=date('YmdHis')?>" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="<?=URL_BASE_ASSETS?>css/style.css?<?=date('YmdHis')?>" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="<?=URL_BASE_ASSETS?>css/helper.css?<?=date('YmdHis')?>" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="<?=URL_BASE_ASSETS?>css/font-awesome.min.css?<?=date('YmdHis')?>" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="<?=URL_BASE_ASSETS?>css/plugins.css?<?=date('YmdHis')?>" rel="stylesheet" type="text/css">	
	</head>
	<body id="mainbody">
		<div class="main-wrapper">
			<div class="content-body m-0 p-0">
				<div class="row">
					<div class="col-12 mt-20 mb-20 pb-20 text-center" style="border-bottom: 1px solid #e0e0e0;">
						<img src="<?=URL_BASE_ASSETS?>img/logo-single-2025.png" style="max-height: 100px">
						<h5 class="mt-3">Help Center for <?=$strPartnerType?></h5>
					</div>
					<?php
					if($dataCategory){
					?>
					<div class="col-12 px-10">
						<div class="accordion accordion-icon" id="accordionCategory">
							<?php
							$number	=	1;
							foreach($dataCategory as $keyCategory){
								$icon			=	$keyCategory->ICON;
								$categoryName	=	$keyCategory->CATEGORYNAME;
								$description	=	$keyCategory->DESCRIPTION;
								$listArticle	=	$keyCategory->LISTARTICLE;
							?>
							<div class="card">
								<div class="card-header">
									<h2><button class="collapsed h6 py-2" data-toggle="collapse" data-target="#collapse<?=$number?>"><i class="mr-1 <?=$icon?>" style="width:16px"></i> <?=$categoryName?><br/><small class="ml-4"><?=$description?></small></button></h2>
								</div>
								<div id="collapse<?=$number?>" class="collapse" data-parent="#accordionCategory">
									<div class="card-body">
										<?php
										if($listArticle){
										?>
											<ul class="list-group px-4">
										<?php
											foreach($listArticle as $keyArticle){
										?>
												<li class="font-weight-bold"><a href="<?=URL_BASE_HELP_CENTER."article/".$keyArticle->IDHELPCENTERARTICLE?>" class="list-group-item list-group-item-action mb-1"><?=$keyArticle->ARTICLETITLE?></a></li>
										<?php
											}
										?>
											</ul>
										<?php
										} else {
										?>
											<p class="text-center">Article content will be published in the future</p>
										<?php
										}
										?>
									</div>
								</div>
							</div>
							<?php
								$number++;
							}
							?>
						</div>
					</div>
					<?php
					}
					?>
				</div>
			</div>
		</div>
	</body>
	<script src="<?=URL_BASE_ASSETS?>js/define.js?<?=date('YmdHis')?>"></script>
	<script src="<?=URL_BASE_ASSETS?>js/jquery.min.js?<?=date('YmdHis')?>"></script>
	<script src="<?=URL_BASE_ASSETS?>js/bootstrap.min.js?<?=date('YmdHis')?>"></script>
</html>
