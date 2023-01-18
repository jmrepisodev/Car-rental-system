<?php

session_start();

// Si el usuario no está logueado se redirige a login.php
if (!isset($_SESSION['login']) || !isset($_SESSION['id_usuario'])) {
	header('Location: login.php');
	exit(); // termina la ejecución del script

}else{ //si la sesión está iniciada...

        $id_usuario=$_SESSION['id_usuario'];
        $username=$_SESSION['username'];
        $rol=$_SESSION['rol'];


        // Establece tiempo de vida de la sesión en segundos (10 minutos)
        $tiempoLimite = 600; 
        // Comprueba si $_SESSION["timeout"] está establecida
        if(isset($_SESSION["timeout"])){
            // Calcula el tiempo de vida de la sesión (TTL = Time To Live)= hora actual - hora inicio
            $sessionTTL = time() - $_SESSION["timeout"];
            if($sessionTTL > $tiempoLimite){
                session_unset();
                session_destroy();
                header("Location: logout.php");
                //Termina la ejecución del script
                exit(); 
            }
        }

        //Actualiza la hora de inicio de sesión
        $_SESSION["timeout"] = time();
  
}

 //Formatea la fecha recibida en un campo de texto a un formato fecha d/m/y
 function formatearFecha($fecha){
    $fecha_formateada=date("d-m-Y", strtotime($fecha));
    return $fecha_formateada;
}

if(!empty($_GET["id"]) && filter_var($_GET["id"],FILTER_VALIDATE_INT)){

    $id_reserva=$_GET["id"];


    require_once 'conectar.php';


    try{
        //ver motores bbdd disponibles
        //print_r(PDO::getAvailableDrivers());

        //Obtenemos los datos de la reserva
        $stmt = $dbh->prepare("SELECT *, DATEDIFF(fecha_devolucion, fecha_recogida) as dias_reserva FROM usuarios, reservas, coches WHERE usuarios.id=reservas.id_usuario and coches.id=reservas.id_coche and reservas.id=?;");
        $stmt->execute(array($id_reserva));
        //devuelve una fila
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        //print_r($row);
        //shuffle($row); ordena de forma aleatoria los coches


    }catch(PDOException $e) {
        $errores[]= "Error: " . $e->getMessage();
    }

}else{
    $errores[]= "Error: No se ha podido acceder a los datos de la reserva";
}

//cerrar la conexión
$dbh=null;


?>


<?php require_once ("header.php") ?>

    <div class="container">
            <?php 
                if(isset($id_reserva)){
                    echo '<div class="fs-3 alert alert-success text-center"> La reserva se ha registrado satisfactoriamente </div> <br>';
                }  
                
                if(!empty($errores)){
                    echo '<div class="alert alert-danger" role="alert">';
                    foreach ($errores as $error){
                        echo "* $error"."<br>";
                    }
                    echo '</div>';
        
                }    
            ?>

        <div class="border mb-3" >
            <div class="mx-auto" style="max-width:128px;">
                <img src="./img/rent_success.png" class="img-fluid p-3"  alt="rent">
            </div>
           
            <h3 class="text-center">Detalle reserva</h3>

            <div class="row p-3">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
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
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php  if($row>0 && $row!==false){  ?>
                            <tr>
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
                            </tr>
                        <?php }  ?>
                    </tbody>
                </table>
            </div> 
            
        </div>
        

        
        <div class="mb-3">
            <a class="btn btn-success my-3 float-start" href="./index.php">Volver</a>
        </div>

        
        
    </div>

<?php require_once ("footer.php") ?>
