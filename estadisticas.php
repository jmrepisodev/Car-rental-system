<?php 

require_once ("header_admin.php"); 

require_once 'conectar.php';

// --------Muestra las estadísticas--------

//Calcula las estadísticas generales en base a los datos almacenados en la base de datos
try{
    //consulta la media de días reservados, mayor número de días reservados, menor número de días, total de reservas, total días reservados y coche más reservado para cada usuario
    $stmt = $dbh->prepare("SELECT *, ROUND(AVG(DATEDIFF(fecha_devolucion, fecha_recogida)),2) as media, MAX(DATEDIFF(fecha_devolucion, fecha_recogida)) as max_dias, 
    MIN(DATEDIFF(fecha_devolucion, fecha_recogida)) as min_dias, COUNT(reservas.id) as total_reservas, SUM(DATEDIFF(fecha_devolucion, fecha_recogida)) as total_dias, 
    COUNT(DISTINCT reservas.id_coche) as total_coches FROM usuarios JOIN reservas ON usuarios.id=reservas.id_usuario GROUP BY usuarios.id;");
    $stmt->execute();

    $result = $stmt->fetchAll();
    // print_r($result);
    
    //$num_filas=count($result);
    $num_filas=$stmt->rowCount();


}catch(PDOException $e) {
    $errores[]= $e->getMessage();
} 

try{
    //Estadísticas generales
    $stmt1 = $dbh->prepare("SELECT AVG(DATEDIFF(fecha_devolucion, fecha_recogida)) as media, MAX(DATEDIFF(fecha_devolucion, fecha_recogida)) as max_dias, MIN(DATEDIFF(fecha_devolucion, fecha_recogida)) as min_dias, COUNT(reservas.id) as total_reservas, 
    SUM(DATEDIFF(fecha_devolucion, fecha_recogida)) as total_dias, (SELECT CONCAT(marca,' ',modelo) FROM coches WHERE id= (SELECT id_coche FROM reservas GROUP BY id_coche ORDER BY COUNT(id_coche) DESC LIMIT 1)) as mas_reservado FROM reservas;");
    $stmt1->execute();

    $result1 = $stmt1->fetchAll();
    // print_r($result);
    
    //$num_filas=count($result);
    $num_filas1=$stmt1->rowCount();



}catch(PDOException $e) {
    $errores[]= $e->getMessage();
} 

//cerramos los cursores
$stmt->closeCursor();
$stmt1->closeCursor();
   
$dbh=null; //cierra las conexiones

?>

    <div class="container p-3">

    <?php 
        if(!empty($errores)){
            echo '<div class="alert alert-danger m-3" role="alert">';
            foreach ($errores as $error){
                echo "* $error"."<br>";
            }
            echo '</div>';

            echo '<a class="btn btn-success m-3" href="./admin.php">Volver</a>';

        }    

    ?>

          <!-- Tabla Estadísticas -->
        
        <div class="row mb-3">
            <h3 class="text-center">Estadísticas por usuario</h3>

                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Usuario</th>
                            <th>Total reservas</th>
                            <th>Media días</th>
                            <th>Max. días</th>
                            <th>Min. días</th>
                            <th>Total días</th> 
                            <th>Total coches</th>      
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($stmt->rowCount()>0 && $result!==false){foreach($result as $row){ ?>
                            <tr>
                                <td data-titulo="Usuario"><?= $row['username'] ?></td>
                                <td data-titulo="Total reservas"><?= $row['total_reservas'] ?></td>
                                <td data-titulo="Media días"><?= $row['media'] ?></td>
                                <td data-titulo="Max. días"><?= $row['max_dias'] ?></td>
                                <td data-titulo="Min días"><?= $row['min_dias'] ?></td>
                                <td data-titulo="Total días"><?= $row['total_dias'] ?></td>
                                <td data-titulo="Total coches"><?= $row['total_coches'] ?></td>
                            
                            </tr>
                        <?php }} ?>
                    </tbody>
                </table> 
        </div>


        <div class="row mb-3">
            <h3 class="text-center">Estadísticas generales</h3>

                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Total reservas</th>
                            <th>Media días</th>
                            <th>Max. días</th>
                            <th>Min. días</th>
                            <th>Total días</th> 
                            <th>Coche más reservado</th>      
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($stmt1->rowCount()>0 && $result1!==false){foreach($result1 as $row1){ ?>
                            <tr>
                                <td data-titulo="Total reservas"><?= $row1['total_reservas'] ?></td>
                                <td data-titulo="Media días"><?= $row1['media'] ?></td>
                                <td data-titulo="Max días"><?= $row1['max_dias'] ?></td>
                                <td data-titulo="Min días"><?= $row1['min_dias'] ?></td>
                                <td data-titulo="Total días"><?= $row1['total_dias'] ?></td>
                                <td data-titulo="Coche más reservado"><?= $row1['mas_reservado'] ?></td>
                            
                            </tr>
                        <?php }} ?>
                    </tbody>
                </table> 
        </div>

    </div>

    


  <?php  require_once ("footer_admin.php"); ?>
                
            