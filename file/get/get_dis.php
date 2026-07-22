<?php 
    require '../../connect.php';
    $dis_id = $_POST['dis_id'] ?? '';
    $sql = $conn->prepare("SELECT * FROM village WHERE dis_id = ?");
    $sql->execute([$dis_id]);
    echo '<option>ເລືອກ</option>';
    foreach($sql as $row){
        echo "<option value='".$row['vill_id']."'>".$row['vill_name_lao']."</option>";
    }
?>
