<?php 

require_once ("header_admin.php"); 

require_once 'conectar.php';

//Formatea la fecha recibida en un campo de texto a un formato fecha d/m/y
function formatearFecha($fecha){
    $fecha_formateada=date("d-m-Y", strtotime($fecha));
    return $fecha_formateada;
}


//Muestra todas las reservas

try{
    //ver motores bbdd disponibles
    //print_r(PDO::getAvailableDrivers());

    //Obtenemos los datos de la reserva
    $stmt = $dbh->prepare("SELECT *, reservas.id as id_reserva, DATEDIFF(fecha_devolucion, fecha_recogida) as dias_reserva FROM usuarios, reservas, coches 
        WHERE usuarios.id=reservas.id_usuario and coches.id=reservas.id_coche ORDER BY reservas.id;");
    $stmt->execute();
    //devuelve una fila
    $result = $stmt->fetchAll();
   // print_r($result);

    $num_filas=count($result);


}catch(PDOException $e) {
    $errores[]= "Error: " . $e->getMessage();
}

$dbh=null; //cierra las conexiones

?>

<div class="container">
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
    
            <!-- Tabla Reservas-->
        <div class="row mb-5">
            <h3 class="text-center">Reservas</h3>
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>id</th>
                        <th>Usuario</th>
                        <th>DNI</th>
                        <th>Lugar recogida</th>
                        <th>Fecha recogida</th>
                        <th>Lugar devolución</th>
                        <th>Fecha devolución</th>
                        <th>Vehiculo</th>
                        <th>Precio/día</th>
                        <th>Nº días</th>
                        <th>Total</th>
                        <th></th>
                        
                    </tr>
                </thead>
                <tbody>
                <?php if($stmt->rowCount()>0 && $result!==false){foreach($result as $row){ ?>
                        <tr>
                            <td data-titulo="id"><?= $row['id_reserva'] ?></td>
                            <td data-titulo="usuario"><?= $row['username'] ?></td>
                            <td data-titulo="DNI"><?= $row['dni'] ?></td>
                            <td data-titulo="Lugar recogida"><?= $row['lugar_recogida'] ?></td>
                            <td data-titulo="Fecha recogida"><?php print formatearFecha($row['fecha_recogida']) ?></td>
                            <td data-titulo="Lugar devolución"><?= $row['lugar_devolucion'] ?></td>
                            <td data-titulo="Fecha devolución"><?php print formatearFecha($row['fecha_devolucion']) ?></td>
                            <td data-titulo="Vehiculo"><?= $row['marca']." ".$row['modelo'] ?></td>
                            <td data-titulo="Precio"><?= $row['precio'] ?> €</td>
                            <td data-titulo="Nº días"><?= $row['dias_reserva'] ?></td>
                            <td data-titulo="Total"><?= $row['precio']*$row['dias_reserva'] ?> €</td>
                            <td><a class="btn btn-danger" href="./delete.php?id=<?php echo $row['id_reserva'] ?>">Eliminar</a></td>
                        </tr>
                        <?php } } ?>
                </tbody>
            </table>
        </div>
</div>

<?php  require_once ("footer_admin.php"); ?>