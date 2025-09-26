<script>
if(!window.jQuery){
    window.location = window.location.origin;
}
</script>
<link href="<?=base_url_assets?>css/jquery.scrollbar.css" rel="stylesheet" type="text/css">
<link href="<?=base_url_assets?>css/daterangepicker.css" rel="stylesheet" type="text/css">
<div class="wrapper">
	<div class="main-header">
		<div class="logo-header" data-background-color="dark2">
			<a href="#" class="logo">
				<img src="<?=base_url_assets?>img/logo.png" alt="navbar brand" class="navbar-brand"> &nbsp;Zegen
			</a>
			<button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse" data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon">
					<i class="icon-menu"></i>
				</span>
			</button>
			<button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
			<div class="nav-toggle">
				<button class="btn btn-toggle toggle-sidebar">
					<i class="icon-menu"></i>
				</button>
			</div>
		</div>
		<nav class="navbar navbar-header navbar-expand-lg" data-background-color="dark2">
			<h4><?=APP_NAME?></h4>
			<ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
				<li class="nav-item dropdown hidden-caret">
					<a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fa fa-bell"></i>
					</a>
					<ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
						<li>
							<div class="dropdown-title"></div>
						</li>
						<li>
							<div class="notif-scroll scrollbar-outer">
								<div class="notif-center" id="bodynotif"></div>
							</div>
						</li>
						<li>
							<a class="see-all" href="javascript:void(0);">Lihat Semua<i class="fa fa-angle-right"></i> </a>
						</li>
					</ul>
				</li>
				<li class="nav-item dropdown hidden-caret">
					<a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
						<i class="fas fa-layer-group"></i>
					</a>
					<div class="dropdown-menu quick-actions quick-actions-info animated fadeIn">
						<div class="quick-actions-header">
							<span class="title mb-1">Pintasan</span>
						</div>
						<div class="quick-actions-scroll scrollbar-outer">
							<div class="quick-actions-items">
								<div class="row m-0">
									<a class="col-6 col-md-4 p-0" href="#">
										<div class="quick-actions-item menu-item" data-alias="TPB" data-url="transaksi_pembelian">
											<i class="flaticon-file-1"></i>
											<span class="text">Kulakan Barang</span>
										</div>
									</a>
									<a class="col-6 col-md-4 p-0" href="#">
										<div class="quick-actions-item menu-item" data-alias="RBPU" data-url="retur_penjualan_ulang">
											<i class="flaticon-database"></i>
											<span class="text">Retur Penjualan</span>
										</div>
									</a>
									<a class="col-6 col-md-4 p-0" href="#">
										<div class="quick-actions-item menu-item" data-alias="PH" data-url="pelunasan_piutang">
											<i class="flaticon-pen"></i>
											<span class="text">Pelunasan Piutang</span>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
				</li>
			</ul>
		</nav>
	</div>
	<div class="sidebar sidebar-style-2">			
		<div class="sidebar-wrapper scrollbar scrollbar-inner">
			<div class="sidebar-content">
				<div class="user">
					<div class="avatar-sm float-left mr-2">
						<img src="<?=base_url_assets?>img/user.png" alt="..." class="avatar-img rounded-circle">
					</div>
					<div class="info">
						<a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
							<span>
								<span class="user-name">-</span>
								<span class="user-level">-</span>
								<span class="caret"></span>
							</span>
						</a>
						<div class="clearfix"></div>

						<div class="collapse in" id="collapseExample">
							<ul class="nav">
								<li>
									<a href="<?=base_url()?>/logout">
										<span class="link-collapse">Logout</span>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<ul class="nav nav-primary">
					<li class="nav-item active menu-item" data-alias="DASH" data-url="dashboard" id="dashboard-menu">
						<a href="#">
							<i class="fas fa-home"></i>
							<p>Dashboard</p>
						</a>
					</li>
					<li class="nav-section">
						<span class="sidebar-mini-icon">
							<i class="fa fa-ellipsis-h"></i>
						</span>
						<h4 class="text-section">Main Menu</h4>
					</li>
					<?=$menuelement?>
				</ul>
			</div>
		</div>
	</div>
	<div class="main-panel">
		<div class="content" id="main-content">
			
		</div>
		<footer class="footer">
			<div class="container-fluid">
				<nav class="pull-left">
					<ul class="nav">
						<li class="nav-item">
							<a class="nav-link" href="https://www.themekita.com">
								About
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">
								Help
							</a>
						</li>
					</ul>
				</nav>
				<div class="copyright ml-auto">
					2018
				</div>				
			</div>
		</footer>
	</div>
</div>
<script>
	var devStatus	=	'<?=$devStatus?>',
		baseURL		=	'<?=base_url()?>',
		loaderElem	=	"<center class='mt-5'>"+
						"	<img src='<?=base_url_assets?>img/loader_content.gif'/><br/><br/>"+
						"	Loading Content..."+
						"</center>";
</script>
<script src="<?=base_url_assets?>js/jquery.scrollbar.min.js"></script>
<script src="<?=base_url_assets?>js/jquery.autocomplete.min.js"></script>
<script src="<?=base_url_assets?>js/moment.min.js"></script>
<script src="<?=base_url_assets?>js/daterangepicker.js"></script>
<script src="<?=base_url_assets?>js/sweetalert.min.js"></script>
<script src="<?=base_url_assets?>js/jquery.nicescroll.min.js"></script>
<script src="<?=base_url_assets?>js/circles.min.js"></script>
<script src="<?=base_url_assets?>js/main.js"></script>