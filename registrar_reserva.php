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



if(isset($_POST["submit"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
 

    if( !empty($_SESSION['id_usuario']) && !empty($_POST['fecha_recogida']) && !empty($_POST['fecha_devolucion']) && !empty($_POST['lugar_recogida']) 
    && !empty($_POST['lugar_devolucion']) && !empty($_POST["id_coche"]) ){

        $id_usuario=$_SESSION['id_usuario'];
        $name=$_SESSION['username'];
        $rol=$_SESSION['rol'];

        $id_coche=$_POST["id_coche"];

        $lugar_recogida=$_SESSION['lugar_recogida'];
        $lugar_devolucion=$_SESSION['lugar_devolucion'];
        $fecha_recogida=$_SESSION['fecha_recogida'];
        $fecha_devolucion=$_SESSION['fecha_devolucion']; 

     
        require_once("conectar.php");

            //Registramos la reserva
            try{
                $sth = $dbh->prepare('INSERT INTO reservas(fecha_recogida, fecha_devolucion, lugar_recogida, lugar_devolucion, id_usuario, id_coche) VALUES (?, ?, ?, ?, ?, ?)');
                $sth->execute(array($fecha_recogida, $fecha_devolucion, $lugar_recogida, $lugar_devolucion, $id_usuario, $id_coche));
                $count= $sth->rowCount();

                //Devuelve el número de filas afectadas
                if($count>0){
                    //obtenemos el id de la reserva
                    $id_reserva= $dbh->lastInsertId();

                    if($id_reserva){
                        $reserved="La reserva se ha registrado satisfactoriamente";
                        //Redirige a la página de detalle de reserva
                        header('Location: detalle_reserva.php?id='.$id_reserva);
                    }

                }else{
                    $errores[] = "Ha ocurrido un error inesperado. No se ha podido completar el proceso de registro";
                }

            }catch(PDOException $e) {
                $errores[]= "Error: " .$e->getMessage();
            } 

        
        //cerrar la conexión
        $dbh=null;
        

    }else{
        $errores[] = "Error: No se han cumplimentado los datos de la reserva";
        
    }
      

}else{
    $errores[] = "Ha ocurrido un error inesperado. No se ha podido completar el proceso de registro";
 
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

                echo '<a class="btn btn-success m-3" href="./catalogo.php">Volver</a>';

            }    
           
    ?>
</div>

<?php require_once ("footer.php") ?>

