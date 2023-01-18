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


//Recibe el id del coche para reservar
if(!empty($_GET["id"]) && filter_var($_GET["id"],FILTER_VALIDATE_INT)){

    $id_coche=$_GET["id"]; //id del coche seleccionado

    require_once 'conectar.php';


    try{
        //ver motores bbdd disponibles
        //print_r(PDO::getAvailableDrivers());

        //Obtenemos los datos del vehiculo seleccionado
        $stmt = $dbh->prepare("SELECT * FROM coches WHERE id=?");
        $stmt->execute(array($id_coche));
        //devuelve una fila
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
       // print_r($car);
      
       

    }catch(PDOException $e) {
        $errores[]= "Error: " . $e->getMessage();
    }


    //cerrar la conexión
    $dbh=null;

    if(isset($_SESSION['login'])) { //obtenemos los datos almacenados en la sesión

        if( !empty($_SESSION['id_usuario']) && !empty($_SESSION['fecha_recogida']) && !empty($_SESSION['fecha_devolucion']) && !empty($_SESSION['lugar_recogida']) 
          && !empty($_SESSION['lugar_devolucion'])){
            
            $id_usuario=$_SESSION['id_usuario'];
            $name=$_SESSION['username'];
            $rol=$_SESSION['rol'];

            $lugar_recogida=$_SESSION['lugar_recogida'];
            $lugar_devolucion=$_SESSION['lugar_devolucion'];
            $fecha_recogida=$_SESSION['fecha_recogida'];
            $fecha_devolucion=$_SESSION['fecha_devolucion']; 

            //Formatea la fecha recibida en un campo de texto a un formato fecha d/m/y
            function formatearFecha($fecha){
            $fecha_formateada=date("d-m-Y", strtotime($fecha));
            return $fecha_formateada;
            }
            
           //Calculamos la diferencia de días entre las dos fechas
           $diff = abs(strtotime($fecha_devolucion) - strtotime($fecha_recogida));
           $years = floor($diff / (365*60*60*24));
           $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
           $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));


        }else{
            $errores[]="Error: no se han cumplimentado algunos datos";
        }
  
    }
}else{
    $errores[]="Error: No se ha seleccionado ningún vehículo";
}


?>

<?php require_once ("header.php") ?>

<div class="container"> 
    <?php 
            if(!empty($errores)){
                echo '<div class="alert alert-danger m-3" role="alert">';
                foreach ($errores as $error){
                    echo "* $error"."<br>";
                }
                echo '</div>';

                echo '<a class="btn btn-success m-3" href="./catalogo_copy.php">Volver</a>';

            }    
           
        ?>

         <!-- Detalle Reserva-->
         <h1 class="text-center">Detalle de confirmación de reserva</h1>

        
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="panel panel-default bg-white border p-3 m-3">
                        <div class="panel-heading">
                            <h2>Detalle reserva</h2>
                        </div>

                        <div class="panel-body">
                            <p class="clearfix mt-3">
                                <strong>Fecha y Lugar</strong>
                                <a href="./reservar.php" class="pull-right text-capitalize ms-2">Modificar</a>
                            </p>
                            <hr>

                            <div class="row">
                                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="fw-bold">Recogida:</span></div>

                                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
                                    <?php isset($lugar_recogida) ? print $lugar_recogida : "No seleccionado" ?><br>
                                    <?php isset($fecha_recogida) ? print formatearFecha($fecha_recogida) : "No seleccionado" ?>
                                </div>
                            </div>
                        
                            <div class="row">
                                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="fw-bold">Devolución:</span></div>

                                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
                                    <?php isset($lugar_devolucion) ? print $lugar_devolucion : "No seleccionado" ?><br>
                                    <?php isset ($fecha_devolucion) ? print formatearFecha($fecha_devolucion) : "No seleccionado"?>								
                                </div>

                            </div>

                            <br>
                            <p class="clearfix mt-3">
                                <strong>Vehiculo</strong>
                                <a href="./catalogo.php" class="pull-right text-capitalize ms-2">Modificar</a>
                            </p>
                            <hr>

                            <div class="row">
                                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="fw-bold">Selección:</span></div>

                                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
                                   
                                    <?php if(isset($car['marca']) && isset($car['modelo'])){ echo $car['marca']." ".$car['modelo'];} else {echo "No seleccionado";}?>
                                    <img src="<?php isset($car['imagen']) ? print $car['imagen'] : "" ?>" class=".img-fluid" style="max-width: 40px;" alt="coche">
                                   						
                                </div>
                            </div>
                            <br><hr>

                            <div class="row">
                                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="fw-bold">Duración:</span></div>

                                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12"><?php isset($days) ? print $days : "No disponible" ?> días</div>
                            </div>

                            <div class="row">
                                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="fw-bold">Precio total:</span></div>

                                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12"><?php if(isset($days) && isset($car['precio'])){ print $days*$car['precio']; } ?> €</div>
                            </div>
                        </div><!-- /.panel-body -->
                    </div>

                </div>

                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                    
                    
                        <?php if($car!==false){ ?>    
                                <!--Detalle vehiculo -->
                                <div class="card m-3">
                                    <div class="card-body">
                                        <h2>Detalle vehiculo</h2>
                                        <div class="row">
                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 p-3">
                                                <input type="hidden" name="id_coche" value="">
                                                <img src="<?php isset($car['imagen']) ? print $car['imagen'] : "" ?>" class=".img-fluid card-img-top mb-3" style="max-width: 210px;" alt="coche">
                                                <h5 class="card-title fs-3"><?php if(isset($car['marca']) && isset($car['modelo'])){ print $car['marca']." ".$car['modelo']; } ?></h5>
                                                <div>
                                                    <span class="card-text me-2 fw-bold"><img src="./img/users.png" alt="users" class="me-1"><?php isset($car['num_puertas']) ? print $car['num_puertas'] : "" ?></span>
                                                    <span class="card-text me-2 fw-bold"><img src="./img/car_door.png" alt="doors" class="me-1"><?php isset($car['num_puertas']) ? print $car['num_puertas'] : "" ?></span>
                                                    <span class="card-text me-2 fw-bold"><img src="./img/car_transmission.png" alt="transmission" class="me-1"><?php isset($car['tipo_transmision']) ? print $car['tipo_transmision'] : "" ?></span>
                                                    <span class="card-text me-2 fw-bold"><img src="./img/fuel.png" alt="fuel" class="me-1"><?php isset($car['tipo_combustible']) ? print $car['tipo_combustible'] : "" ?></span>
                                                </div>  
                                            </div>

                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 p-3">
                                                <div class="mb-3">
                                                    <ul class="mb-3" style="list-style:none;">
                                                        <li class="card-text text-success"><i class="fas fa-check"></i> Kilometraje ilimitado</li>
                                                        <li class="card-text text-success"><i class="fas fa-check"></i> Protección básica incluida</li>
                                                        <li class="card-text text-success"><i class="fas fa-check"></i> Cancelación gratuita</li>
                                                    </ul>
                                                </div>
                                                
                                            </div>

                                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                                <div class="text-primary fs-6 me-2"><span class="fs-3 fw-bold"><?php isset($car['precio']) ? print $car['precio'] : "" ?></span> €/día</div>
                                                <div class="text-danger fs-2 fw-bold">Total: <?php if(isset($days) && isset($car['precio'])){ print $days*$car['precio']; } ?> €</div>
                                            </div>

                                        </div> <!--fin row -->
                                
                                </div>
                            
                        <?php   }  ?>
                
                </div>
                        </div>

            </div>
            <div class="row mb-6">
                <form action="./registrar_reserva.php" method="post">
                    <input type="hidden" name="id_coche" value="<?= $id_coche ?>">
                    <input type="hidden" name="lugar_recogida" value="<?php isset($lugar_recogida) ? print $lugar_recogida : "" ?>">
                    <input type="hidden" name="fecha_recogida" value="<?php isset($fecha_recogida) ? print $fecha_recogida : ""?>">
                    <input type="hidden" name="lugar_devolucion" value="<?php  isset($lugar_devolucion) ? print $lugar_devolucion : "" ?>">
                    <input type="hidden" name="fecha_devolucion" value="<?php isset($fecha_devolucion) ? print $fecha_devolucion : "" ?>">
                    
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                        <button type="submit" class="btn btn-danger m-3 p-3" name="submit">Confirmar reserva</button>
                    </div>
                </form>
                
            </div>
        

         
</div>



<?php require_once ("footer.php") ?>