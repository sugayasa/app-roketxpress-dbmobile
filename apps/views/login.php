<div class="wrapper wrapper-login">
	<div class="container container-login animated fadeIn">
		<center><img src="<?=base_url_assets?>img/logo.png"/></center><br/>
		<h3 class="text-center">- <?=APP_NAME?> -<br/>Sign In</h3>
		<div class="alert alert-danger d-none" role="alert" id="warning-element"></div>
		<form id="login-form" class="login-form" method="POST" action="">
			<div class="form-group form-floating-label">
				<input id="username" name="username" type="text" class="form-control input-border-bottom" required>
				<label for="username" class="placeholder">Username</label>
			</div>
			<div class="form-group form-floating-label">
				<input id="password" name="password" type="password" class="form-control input-border-bottom" required>
				<label for="password" class="placeholder">Password</label>
				<div class="show-password">
					<i class="icon-eye"></i>
				</div>
			</div>
			<div class="form-action mb-3">
				<button type="submit" class="btn btn-primary btn-rounded btn-login" href="#" id="submitBtn">Sign in</button>
			</div>
		</form>
	</div>
</div>
<script src="<?=base_url_assets?>module/login.js"></script>