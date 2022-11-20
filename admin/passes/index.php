
<?php if($_settings->chk_flashdata('success')): ?>

<script>
	alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `pass_history` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }
	if(isset($pass_id)){
		$pass_qry = $conn->query("SELECT p.*, c.name as `category` FROM `pass_list` p inner join category_list c on p.category_id = c.id where p.id = '{$pass_id}' ");
		if($pass_qry->num_rows > 0){
			foreach($pass_qry->fetch_array() as $k => $v){
				if(!is_numeric($k))
				$pass[$k] = $v;
			}
			if(isset($pass['balance'])){
				$hist = $conn->query("SELECT SUM(`cost`) FROM `pass_history` where pass_id = '{$pass_id}' and id !='{$id}' ")->fetch_array()[0];
				$hist = $hist > 0 ? $hist : 0 ;
				$pass['balance'] = $pass['balance'] - $hist;
			}
		}
	}
}
 ?>
<?php endif;?>
<div class="card card-outline rounded-0 card-navy">
	<div class="card-header">
		<h3 class="card-title">List of Passes</h3>
		<div class="card-tools">
			<a href="./?page=passes/manage_pass" id="create_new" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span>  Create New</a>
		</div>
	</div>
	<div class="card-body">
        <div class="container-fluid">
			<table class="table table-hover table-striped table-bordered" id="list">
				<colgroup>
					<col width="5%">
					<col width="15%">
					<col width="20%">
					<col width="20%">
					<col width="20%">
					<col width="10%">
					<col width="10%">
				</colgroup>
				<thead>
					<tr>
						<th>#</th>
						<th>Date Created</th>
						<th>Pass Code</th>
						<th>Category</th>
						<th>Owner/Driver</th>
						<!--<th>Balance</th>-->
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$i = 1;
					$uwhere="";
					if($_settings->userdata('type') != 1)
					$uwhere =" where p.user_id = '{$_settings->userdata('id')}' ";
					$qry = $conn->query("SELECT p.*, c.name as category from `pass_list` p inner join category_list c on p.category_id = c.id {$uwhere} order by unix_timestamp(p.`date_created`) desc ");
					while($row = $qry->fetch_assoc()):
					?>
						<tr>
							<td class="text-center"><?php echo $i++; ?></td>
							<td><?php echo date("Y-m-d H:i",strtotime($row['date_created'])) ?></td>
							<td><?php echo $row['code'] ?></td>
							<td><?php echo $row['category'] ?></td>
							<td><?php echo $row['owner'] ?></td>
							<!--<td><?php echo $row['balance'] ?></td>-->

							<td align="center">
								 <button type="button" class="btn btn-flat p-1 btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
				                  		Action
				                    <span class="sr-only">Toggle Dropdown</span>
				                  </button>
				                  <div class="dropdown-menu" role="menu">
				                    <a class="dropdown-item view_data" href="./?page=passes/view_pass&id=<?php echo $row['id'] ?>"><span class="fa fa-eye text-dark"></span> View</a>
				                    <div class="dropdown-divider"></div>
				                    <a class="dropdown-item edit_data" href="./?page=passes/manage_pass&id=<?php echo $row['id'] ?>"><span class="fa fa-edit text-primary"></span> Edit</a>
				                    <div class="dropdown-divider"></div>
				                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-trash text-danger"></span> Delete</a>
				                  </div>
							</td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		$('.delete_data').click(function(){
			_conf("Are you sure to delete this pass permanently?","delete_pass",[$(this).attr('data-id')])
		})
		$('.table').dataTable({
			columnDefs: [
					{ orderable: false, targets: [4] }
			],
			order:[0,'asc']
		});
		$('.dataTable td,.dataTable th').addClass('py-1 px-2 align-middle')
	})
	function delete_pass($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_pass",
			method:"POST",
			data:{id: $id},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("An error occured.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					location.reload();
				}else{
					alert_toast("An error occured.",'error');
					end_loader();
				}
			}
		})
	}
</script>