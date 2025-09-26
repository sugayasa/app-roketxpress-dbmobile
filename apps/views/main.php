<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=APP_NAME?></title>

	<link rel="icon" href="<?=base_url_assets?>/assets/img/icon.ico" type="image/x-icon"/>
    <link href="<?=base_url_assets?>css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="<?=base_url_assets?>css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="<?=base_url_assets?>css/nprogress.css" rel="stylesheet" type="text/css">
    <link href="<?=base_url_assets?>css/main.css" rel="stylesheet" type="text/css" id="maincss">
	<script src="<?=base_url_assets?>js/webfont.min.js"></script>
	<script>
		WebFont.load({
			google: {"families":["Lato:300,400,700,900"]},
			custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['<?=base_url_assets?>css/fonts.min.css']},
			active: function() {
				sessionStorage.fonts = true;
			}
		});
	</script>
	<link rel="stylesheet" href="<?=base_url_assets?>css/atlantis.min.css">
  </head>
  <body id="mainbody" class="login">
    <div>
      <div class="login_wrapper" style="margin-top:0">
        <div class="animate form login_form">
          <section class="login_content" id="center_content">
              <h3><center><?=APP_NAME?></center></h3>
              <center>
                <img src="<?=base_url_assets?>img/loader.gif"/>
                <p id="loadtext">Checking session...</p>
              </center>
          </section>
        </div>
      </div>
    </div>
	<!-- </body></html> -->
  </body>
  <script src="<?=base_url_assets?>js/jquery.min.js"></script>
  <script src="<?=base_url_assets?>js/bootstrap.min.js"></script>
  <script src="<?=base_url_assets?>js/nprogress.js"></script>
  <script src="<?=base_url_assets?>module/define.js"></script>
  <script src="<?=base_url_assets?>module/index.js"></script>
  <script src="<?=base_url_assets?>js/jquery-ui.min.js"></script>
  <script src="<?=base_url_assets?>js/popper.min.js"></script>
</html>
