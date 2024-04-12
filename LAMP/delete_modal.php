<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="confirmationModalLabel">刪除使用者</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div id = "modal-body" class="modal-body">
				是否確定刪除?
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
				<button type="button" class="btn btn-danger" id="confirmDelete">Yes</button>
			</div>
		</div>
	</div>
</div>
<script>
	const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmDeleteButton = document.getElementById('confirmDelete');
	var username = null;
	var permission = null;
	
	function delete_user(username,permission){
		
		window.username = username;
		window.permission = permission;
		
		document.getElementById('modal-body').textContent = "是否確定刪除帳號 " + username + " ?";
		confirmationModal.show();

		
	}
	
	confirmDeleteButton.addEventListener('click', function(){
		handleYesButtonClick();
	});
	
	function handleYesButtonClick() {
		
		if (window.username !== null){
			var xhr = new XMLHttpRequest();  //建立XMLHttpRequest物件
		
			xhr.open('POST', 'delete_user.php');  //設置Request資料(Method, URL, *async)
			xhr.setRequestHeader('Content-Type', 'application/json');
			
			//設置Time Out
			xhr.timeout = 60000; //單位:ms
			xhr.ontimeout = function(){
				alert('請求超時!');
			};
			
			//設置Response監聽
			xhr.onreadystatechange = function(){
				if (xhr.readyState === 4 && xhr.status === 200){
					if(xhr.responseText.trim() == "1"){
						alert('刪除成功!');
					} else {
						alert('刪除失敗，請重試!錯誤：' + xhr.responseText);
					}
					
					confirmationModal.hide();
					window.location.reload();
				};
			};
			
			//建立JSON格式物件
			var data = {
				pusername: window.username,
				ppermission: window.permission
			};
			
			//Request
			xhr.send(JSON.stringify(data));
		} else {
			alert('獲取號失敗!請重試!');
			confirmationModal.hide();
		};
	}
</script>