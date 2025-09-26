<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html;">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BALI SUN TOURS - Help Center - <?=$detailArticle['ARTICLETITLE']?></title>

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
						<h5 class="mt-3">Help Center for <?=$detailArticle['PARTNERTYPE']?></h5>
					</div>
					<div class="col-12 mx-20">
						<div class="card" style="border:none">
							<div class="card-body">
								<?php
								if($detailArticle){
								?>
								<h5 class="mb-1"><?=$detailArticle['CATEGORYNAME']?> </h5>
								<h6 class="mb-1"><?=$detailArticle['ARTICLETITLE']?></h6>
								<p class="mb-20">
									<small>Last Update : <?=$detailArticle['INPUTDATETIME']?></small>
								</p>
								<?=$detailArticle['ARTICLECONTENT']?>
								<?php
								} else {
								?>
									<p class="text-center">Article content details not found</p>
								<?php
								}
								?>
							</div>
						</div>
					</div>
					<div class="col-12 mt-20 text-center">
						<button class="button button-sm button-warning ml-10 mb-30" onclick="history.back()"><span><i class="fa fa-arrow-left"></i>Back</span></button>
					</div>
				</div>
			</div>
		</div>
	</body>
	<script src="<?=URL_BASE_ASSETS?>js/define.js?<?=date('YmdHis')?>"></script>
	<script src="<?=URL_BASE_ASSETS?>js/jquery.min.js?<?=date('YmdHis')?>"></script>
	<script src="<?=URL_BASE_ASSETS?>js/bootstrap.min.js?<?=date('YmdHis')?>"></script>
</html>
