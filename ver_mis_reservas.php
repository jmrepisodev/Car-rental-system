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

if(!empty($_SESSION['id_usuario']) && filter_var($_SESSION['id_usuario'],FILTER_VALIDATE_INT)){

    $id_usuario=$_SESSION['id_usuario'];


    require_once 'conectar.php';


    try{
        //ver motores bbdd disponibles
        //print_r(PDO::getAvailableDrivers());

        //Obtenemos los datos de la reserva
        $stmt = $dbh->prepare("SELECT *, DATEDIFF(fecha_devolucion, fecha_recogida) as dias_reserva FROM usuarios, reservas, coches 
        WHERE usuarios.id=reservas.id_usuario and coches.id=reservas.id_coche and usuarios.id=?;");
        $stmt->execute(array($id_usuario));
        //devuelve un array de resultados
        $result= $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counter=$stmt->rowCount();
        


    }catch(PDOException $e) {
        $errores[]= "Error: " . $e->getMessage();
    }

}else{
    $errores[]= "Error: No existen datos de reserva";
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

        

        <div class="row mb-3">
            <h3 class="text-center">Mis reservas</h3>
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
                <?php if($stmt->rowCount()>0 && $result!==false){foreach($result as $row){ ?>
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
                    <?php } } ?>
                </tbody>
            </table>
        </div> 
        <div class="mb-5">
             <a class="btn btn-success my-3 float-start" href="./index.php">Volver</a>
        </div>
        
        
    </div>

<?php require_once ("footer.php") ?>
