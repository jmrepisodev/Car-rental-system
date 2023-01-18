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

  
        $errores=array();
      
        function filtrado($datos){
            $datos = trim($datos); // Elimina espacios antes y después de los datos
            $datos = stripslashes($datos); // Elimina backslashes \
            $datos = htmlspecialchars($datos); // Traduce caracteres especiales en entidades HTML
            return $datos;
        }
/*
        function validar_fecha($fecha){
            //Recibe la fecha en formato y-m-d
            //obtiene un array de valores separados por "-" en año, mes, dia
            $valores = explode('-', $fecha);
            //comprueba el número de valores y si los valores tienen sentido dentro del calendario: checkdate(mes,dia, año)
            if(count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])){
                return true;
            }
            return false;
        }
*/

        // validar diferentes formatos de fecha. Formato por defecto: y-m-d
        function validateDate($date, $format = 'Y-m-d'){
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }

        //El nombre es un campo obligatorio. Si está vacío se lanza un error
        if (empty($_POST["fecha_recogida"])) {
            $errores[] = "La fecha de recogida es obligatoria";
        }else {
            // comprueba que sea una fecha válida
            if (!validateDate($_POST["fecha_recogida"])) {
                $errores[] = "El formato de fecha ".$_POST["fecha_recogida"]." es incorrecto.";
            } 
        }

        //El nombre es un campo obligatorio. Si está vacío se lanza un error
        if (empty($_POST["fecha_devolucion"])) {
            $errores[] = "La fecha de devolución es obligatoria";
        }else {
            // comprueba que sea una fecha válida
            if (!validateDate($_POST["fecha_devolucion"])) {
                $errores[] = "El formato de fecha ".$_POST["fecha_devolucion"]." es incorrecto.";
            } 
        }

        if($_POST["fecha_recogida"] > $_POST["fecha_devolucion"]){
            $errores[] = "La fecha de recogida no puede ser posterior a la fecha de devolución";
        }

        if($_POST["fecha_recogida"] < date("Y-m-d") || $_POST["fecha_devolucion"] < date("Y-m-d")){
            $errores[] = "La fecha de recogida o la fecha de devolución no pueden ser anteriores a la fecha actual";
        }

            //La ciudad es un campo obligatorio. Si está vacío se lanza un error
        if (empty($_POST["lugar_recogida"])) {
            $errores[] = "El lugar de recogida es obligatorio";
        }else {
            // comprueba que solo tenga letras, guiones, espacios en blanco y letras acentuadas 
            if (!preg_match("/^[A-Za-z ñáéíóú]*$/",$_POST["lugar_recogida"])) {
                $errores[] = "Formato ciudad incorrecto. Solo está permitido letras, guiones o espacios";
            } 
        }

            //La ciudad es un campo obligatorio. Si está vacío se lanza un error
            if (empty($_POST["lugar_devolucion"])) {
            $errores[] = "El lugar de devolución es obligatorio";
        }else {
            // comprueba que solo tenga letras, guiones y espacios en blanco
            if (!preg_match("/^[A-Za-z ñáéíóú]*$/",$_POST["lugar_devolucion"])) {
                $errores[] = "Formato ciudad incorrecto. Solo está permitido letras, guiones o espacios";
            } 
        }

        if(!isset($_POST["legals"]) || $_POST["legals"]!="checked"){
            $errores[] = "Debe aceptar los términos legales";
        }

        //si no hay errores se aceptan los datos y se almacenan en variables.
        if(count($errores)==0){
          
            $lugar_recogida=ucfirst(filtrado($_POST["lugar_recogida"])); //coloca la primera letra en mayúscula;
            $lugar_devolucion=ucfirst(filtrado($_POST["lugar_devolucion"]));

            $fecha_recogida=filtrado($_POST["fecha_recogida"]);
            $fecha_devolucion=filtrado($_POST["fecha_devolucion"]);
           
            //Almacenamos temporalmente los datos seleccionados por el usuario en la sesión
            $_SESSION['lugar_recogida'] = $lugar_recogida;
            $_SESSION['fecha_recogida'] = $fecha_recogida;
            
            $_SESSION['lugar_devolucion'] = $lugar_devolucion;
            $_SESSION['fecha_devolucion'] = $fecha_devolucion;

            //Redirigimos al catálogo de coches
            header("location: catalogo.php");
           

        }

    }

?>


<?php require_once ("header.php") ?>

<div class="container p-5">

<?php 
        if(!empty($errores)){
            echo '<div class="alert alert-danger" role="alert">';
            foreach ($errores as $error){
                echo "* $error"."<br>";
            }
            echo '</div>';

        }    
    ?>
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-sm-8">
                <h1 class="text-center">Reserva tu vehículo al mejor precio</h1>
                    <div class="card rounded-3 mb-3">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card-header text-center text-white fw-bold fs-3 p-3 bg-dark">Consultar vehículos disponibles</div>
                                <div class="card-body p-md-5 mx-md-4">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text"><i class=""></i>Lugar de recogida</span>
                                            <input type="text" name="lugar_recogida" class="form-control p-2" id="recogida" required></input>
                                        </div>

                                        <div class="input-group mb-3">
                                            <span class="input-group-text"><i class=""></i>Fecha de recogida</span>
                                            <input type="date" placeholder="Fecha recogida (dd/mm/aaaa)" name="fecha_recogida" class="form-control p-2" id="fecha_recogida" required></input>
                                        </div> 

                                        <hr>

                                        <div class="input-group mb-3">
                                            <span class="input-group-text"><i class=""></i>Lugar de devolución</span>
                                            <input type="text" name="lugar_devolucion" class="form-control p-2" id="devolucion" required></input>
                                        </div>

                                        <div class="input-group mb-4">
                                            <span class="input-group-text"><i class=""></i>Fecha de devolución</span>
                                            <input type="date" placeholder="Fecha devolución (dd/mm/aaaa)" name="fecha_devolucion" class="form-control p-2" id="fecha_devolucion" required></input>
                                        </div>

                                        <div class="mb-3 form-check">
                                            <input type="checkbox" name="legals" value="checked" class="form-check-input"
                                                id="legals">
                                            <label class="form-check-label" for="legals">Acepto los términos y condiciones</label>
                                        </div>
                                        <button type="submit" name="submit" class="btn btn-danger mb-3 p-3"
                                            style="width:100%;">BUSCAR</button>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

<?php require_once ("footer.php") ?>