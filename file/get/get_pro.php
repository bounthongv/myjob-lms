<?php 
    require '../../connect.php';
    $pro_id = $_POST['pro_id'] ?? '';
    $sql = $conn->prepare("SELECT * FROM district WHERE pro_id = ?");
    $sql->execute([$pro_id]);
    echo '<option>ເລືອກ</option>';
    foreach($sql as $row){
        echo "<option value='".$row['dis_id']."'>".$row['dis_name_lao']."</option>";
    }
?>
