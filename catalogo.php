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

 

    $errores=array();

    if(isset($_SESSION['login'])) {

        if( !empty($_SESSION['id_usuario']) && !empty($_SESSION['fecha_recogida']) && !empty($_SESSION['fecha_devolucion']) && !empty($_SESSION['lugar_recogida']) 
          && !empty($_SESSION['lugar_devolucion'])){
            
            $id_usuario=$_SESSION['id_usuario'];
            $name=$_SESSION['username'];
            $rol=$_SESSION['rol'];

            $fechaActual = date('d-m-Y H:i:s');
            //$fecha=date('d-m-Y',strtotime('15-02-1985'));

            $lugar_recogida=$_SESSION['lugar_recogida'];
            $lugar_devolucion=$_SESSION['lugar_devolucion'];
            
            $fecha_recogida=$_SESSION['fecha_recogida'];
            $fecha_devolucion=$_SESSION['fecha_devolucion']; 

    
            //Calculamos la diferencia de días entre las dos fechas
            $diff = abs(strtotime($fecha_devolucion) - strtotime($fecha_recogida));
            $years = floor($diff / (365*60*60*24));
            $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
            $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

            //Formatea la fecha recibida en un campo de texto a un formato fecha d/m/y
            function formatearFecha($fecha){
                $fecha_formateada=date("d-m-Y", strtotime($fecha));
                return $fecha_formateada;
            }


            
            require_once 'conectar.php';

            try{
                //ver motores bbdd disponibles
                //print_r(PDO::getAvailableDrivers());

                //Obtenemos los coches del catálogo que hay disponibles en las fechas seleccionadas. Los coches reservados en esas fechas no aparecerán en el catálogo de coches disponibles
                $stmt = $dbh->prepare("SELECT * FROM coches WHERE id NOT IN (SELECT id_coche FROM reservas WHERE ('$fecha_recogida'>=fecha_recogida AND '$fecha_devolucion'<=fecha_devolucion) 
                    OR ('$fecha_recogida'>=fecha_recogida AND '$fecha_recogida'<=fecha_devolucion) OR ('$fecha_devolucion'>=fecha_recogida AND '$fecha_devolucion'<=fecha_devolucion) 
                    OR ('$fecha_recogida'<fecha_recogida AND '$fecha_devolucion'>fecha_devolucion)) AND estado=true;");
                $stmt->execute();
                //devuelve un array bidireccional de coches
                $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
                //print_r($questions);
                //shuffle($questions); ordena de forma aleatoria los coches
                $counter= $stmt->rowCount();

                if($counter==0){
                    $errores[]= "Error: No hay coches disponibles en el rango de fechas seleccionada";   
                }


            }catch(PDOException $e) {
                $errores[]= "Error: " . $e->getMessage() ;
            }

            //cerrar la conexión
            $dbh=null;
            
          
        }else{
            $errores[]="Error: No se han introducido los datos de la reserva";
        }
  
    }else{
        $errores[]="Error: Debe autenticarse para realizar la reserva";
    }


	?>

    <?php require_once ("header.php") ?>

    <div class="container">

    <?php 
        if(!empty($errores)){
            echo '<div class="alert alert-danger" role="alert my-3">';
            foreach ($errores as $error){
                echo "* $error"."<br>";
            }
            echo '</div>';

        }    
    ?>

        <div class="row mb-5">
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="panel panel-default bg-white border p-3 m-3">
                    <div class="panel-heading">
                        <h2>Detalle reserva</h2>
                        <p> <?= $fechaActual ?></p>
                    </div>

                    <div class="panel-body">
                        <p class="clearfix mt-3">
                            <strong>Fechas y Lugar de la reserva</strong>
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
                        

                        <div class="row">
                            <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="fw-bold">Duración reserva:</span></div>

                            <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12"><?php isset($days) ? print $days : "No disponible" ?> días</div>
                        </div>
                    </div><!-- /.panel-body -->
                </div>

            </div>

            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                <h2 class="text-center">Vehiculos disponibles de <span class="text-primary"><?php isset($fecha_recogida) ? print formatearFecha($fecha_recogida) : "" ?></span> 
                    a <span class="text-primary"><?php isset ($fecha_devolucion) ? print formatearFecha($fecha_devolucion) : ""?></span></h2>
                
                    <?php if(isset($counter) and $counter>0){ foreach($cars as $car){ ?>    
                            <!--item 1-->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 p-3">
                                            <img src="<?php isset($car['imagen']) ? print $car['imagen'] : "" ?>" class="card-img-top mb-3" style="max-width: 210px;" alt="coche">
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
                                            <div class="text-primary fs-6 me-2">Desde <span class="fs-3 fw-bold"><?php isset($car['precio']) ? print $car['precio'] : "" ?></span> €/día</div>
                                            <div>
                                                <a href="./confirmar_reserva.php?id=<?php isset($car['id']) ? print $car['id'] : "" ?>" class="btn btn-primary">Reservar</a>
                                            </div>
                                        </div>

                                    </div> <!--fin row -->
                               
                                </div>
                            </div>
                        
                    <?php   } } ?>
              
                        
            </div>

       

        </div>

        

 <div>

      

             
        

    <?php require_once ("footer.php") ?>