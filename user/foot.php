<?php
if($newuserfoot){
	include($newuserfoot);
}else{
?>
</div>
<?php
}
include_once SYSTEM_ROOT.'sakura.php';
loadChatWidget();
?>
