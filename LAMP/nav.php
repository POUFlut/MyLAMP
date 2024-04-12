<nav class="navbar navbar-expand-lg navbar-light" style = "background-color: white;" >
	<div class="container">
		<!-- Brand/logo -->
		<a class="navbar-brand" href="http://10.14.220.8/ma_design/N50/index.php"><img src="http://10.14.220.8/ma_design/N50/Logo.png" alt="首頁" width="200"></a>
		
		<!-- Button to toggle the navigation menu on smaller screens -->
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		
		<!-- Define the navigation menu items -->
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" href="http://10.14.220.8/ma_design/N50/index.php">首頁</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="http://10.14.220.8/ma_design/N50/memo/index.php">MEMO交接</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="http://10.14.220.8/ma_design/N50/folder_mgnt/index.php">路徑查詢</a>
				</li>
				<?php
					if ($_SESSION["permission"] > 2) {
						echo "<li class='nav-item'>
							<a class='nav-link' href='http://10.14.220.8/ma_design/N50/user_mgnt.php'>使用者管理</a>
						</li>";
					}
					
					if ($_SESSION["permission"] > 2) {
						echo "<li class='nav-item'>
							<a class='nav-link' href='http://10.14.220.8/ma_design/N50/register.php'>註冊新帳號</a>
						</li>";
					}
				?>
			</ul>
		</div>
		<div class="col-md-3 text-right">
			<div style = "font-size: 12px;">
				您好!<?php echo $_SESSION["realname"] . "!" ?>
			</div>
			<div style = "font-size: 12px;">
				登入權限:<?php echo $_SESSION["permission_zh"]; ?>
			</div>
			<div style = "font-size: 12px;">
				<a  href="http://10.14.220.8/ma_design/N50/logout.php">登出</a>
			</div>
		</div>
	</div>
</nav>