<?php
session_start();

if(!empty($_SESSION['login'])) { //si la sesión está iniciada...

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

   require_once ("conectar.php");
   
   if(isset($_POST["submit"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
        $errores=array();

       

        /** 
         * Función de filtrado
         */
        function filtrado($datos){
            $datos = trim($datos); // Elimina espacios antes y después de los datos
            $datos = stripslashes($datos); // Elimina backslashes \
            $datos = htmlspecialchars($datos); // Traduce caracteres especiales en entidades HTML
            return $datos;
        }

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

/*
        // validar diferentes formatos de fecha. Formato por defecto: y-m-d
        function validateDate($date, $format = 'Y-m-d'){
            $d = DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) == $date;
        }
*/
        /**
         * Función para validar el formato de DNI
         */
        function validar_dni($dni){
            if (!preg_match("/^[0-9]{8}[A-Za-z]{1}$/",$dni)) return false;

            //stroupper() convierte a mayúsculas. substr(string, start, length). Si el start es negativo, la cadena devuelta empezará contando desde el final de string. 
            $letra = strtoupper(substr($dni, -1));
            //si se especifica length negativo, ese número de caracteres se omiten al final del string
	        $numeros = substr($dni, 0, -1); 

          
            /* - substr(string, start, length). Se divide el número completo entre 23 
               y el resto de la división se utiliza para obtener la letra de la posición 0 a 22 según el patrón TRWAGMYFPDXBNJZSQVHLCKE.
               - Se comprueba que la letra corresponda con la letra especificada */
    
            if (substr("TRWAGMYFPDXBNJZSQVHLCKE", $numeros%23, 1) == $letra) return true;

            return false;
          }
          

                //El nombre es un campo obligatorio. Si está vacío se lanza un error
            if (empty($_POST["name"])) {
                $errores[] = "El nombre es obligatorio";
            }else {
                // comprueba que solo tenga letras, guiones y espacios en blanco
                if (!preg_match("/^[a-zA-Z- ]*$/",$_POST["name"])) {
                    $errores[] = "Formato de nombre incorrecto. Solo está permitido letras, guiones o espacios";
                } 
            }

            //comprobamos si la fecha no está vacía y es válida
            if (empty($_POST["fecha_nacimiento"])) {
                $errores[] = "La fecha de nacimiento es obligatoria";
            }else {
                // comprueba que sea una fecha válida
                if (!validar_fecha($_POST["fecha_nacimiento"])) {
                    $errores[] = "Formato de fecha incorrecto.";
                } 
            }

            //Comprueba que la fecha de nacimiento tiene sentido
            if($_POST["fecha_nacimiento"] >= date("Y-m-d")){
                $errores[] = "La fecha de nacimiento no puede ser posterior a la fecha actual";
            }

            //El nombre es un campo obligatorio. Si está vacío se lanza un error
            if (empty($_POST["dni"])) {
                $errores[] = "El DNI obligatorio";
            }else {
                // comprueba que sea una fecha válida
                if (!validar_dni($_POST["dni"])) {
                    $errores[] = "Formato de DNI no válido.";
                } 
            }

            //La contraseña es un campo obligatorio. Si está vacío se lanza un error
            if (empty($_POST["password1"]) || empty($_POST["password2"])) {
                $errores[] = "No ha introducido ninguna contraseña o las contraseñan no coinciden";
            }else{
                if ($_POST["password1"] != $_POST["password2"]){
                    $errores[] = "Las contraseñas no coinciden";
                }else{
                    if (strlen($_POST['password1']) > 12 || strlen($_POST['password1']) < 4) {
                        $errores[] = "La contraseña debe tener entre 4 y 12 caracteres";
                        
                    }
                }    
            }

            //el email es obligatorio. Si está vacío se lanza un error
            if (empty($_POST["email"])) {
                $errores[] = "Email es obligatorio";
            } else {
                // Eliminamos cualquier carácter que pueda dar problemas
                $email_sanitized = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
                //comprobamos que el email está correctamente formado
                if (!filter_var($email_sanitized, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = "Formato de email no válido";
                }
            }

            if(!isset($_POST["legals"]) || $_POST["legals"]!="checked"){
                $errores[] = "Debe aceptar los términos legales";
            }


            //si no hay errores se aceptan los datos y se almacenan en variables.
            if(count($errores)==0){
                $name=filtrado($_POST["name"]);

                $fecha_nacimiento=filtrado($_POST["fecha_nacimiento"]);
               
                $dni=filtrado($_POST["dni"]);
                $email=filtrado($email_sanitized);
                $password=filtrado($_POST["password1"]);
                $rol="user"; //rol de usuario

                try{
                    //consultamos si existe el usuario en la base de datos
                    $stmt = $dbh->prepare("SELECT * FROM usuarios WHERE email= ?");
                    $stmt->bindParam(1, $email);
                    $stmt->execute();
                    //devuelve un array bidireccional 
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    //Devuelve el número de filas encontradas
                    $counter= $stmt->rowCount();
                    
                    if($counter>0 && $row!==false){
                        $errores[] = "El usuario ya existe. Elige otro email para registrarte";
                    }else{
                        //si no existe la cuenta, realizamos un nuevo registro en la base de datos

                         //ciframos la contraseña
                        $password=password_hash($password, PASSWORD_DEFAULT);

                        try{
                            $sth = $dbh->prepare('INSERT INTO usuarios(username, email, password, fecha_nacimiento, dni, rol) VALUES (?, ?, ?, ?, ?, ?)');
                            $sth->execute(array($name, $email, $password,$fecha_nacimiento, $dni, $rol));
                            $count= $sth->rowCount();
                            //Devuelve el número de filas afectadas
                            if($count>0){
                                $registered="Usuario registrado satisfactoriamente";
                                //Redirige a la página de registro satisfactorio
                               // header("location: success.php");
                            }else{
                                $errores[] = "El nombre de usuario o la contraseña no son válidos";
                            }
                        
                        }catch(PDOException $e) {
                            $errores[]= "Error: " . $e->getMessage();
                        }   
                    }
                
                }catch(PDOException $e) {
                    $errores[]= "Error: " . $e->getMessage();
                }   
        
            }

            //cerrar la conexión
            $dbh=null;


        
    }

?>

<?php require_once ("header.php") ?>
        
      
    <div class="container-fluid p-3">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="card rounded-3 m-3">
                    <div class="row">
                        <div class="col-sm-12">
                        <div class="card-header text-center text-white fw-bold fs-3 p-3 bg-dark">Registrar</div>
                            <div class="card-body p-md-5 mx-md-4">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="fa fa-user fa-lg"></i></span>
                                        <input placeholder="Nombre" type="text" name="name" class="form-control p-2" id="name" pattern="[a-zA-Z- ]*" title="Solo está permitido letras, guiones o espacios" required>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="fa fa-id-card fa-lg"></i></span>
                                        <input placeholder="DNI" type="text" name="dni" class="form-control p-2" id="dni" pattern="[0-9]{8}[A-Za-z]{1}" title="Formato: 8 dígitos + 1 letra" required>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="fa fa-calendar fa-lg"></i></span>
                                        <input placeholder="Fecha nacimiento (dd/mm/aaaa)" type="date" name="fecha_nacimiento" class="form-control p-2" id="fecha_nacimiento" title="Fecha de nacimiento" required>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="fa fa-envelope fa-lg"></i></span>
                                        <input type="email" name="email" placeholder="Email" class="form-control p-2" id="email" aria-describedby="emailHelp" required>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="fa fa-key fa-lg"></i></span>
                                        <input type="password" name="password1" placeholder="Password" class="form-control p-2" id="password1" required minlength="4" maxlength="12">
                                    </div>
                                    <div class="input-group mb-4">
                                        <span class="input-group-text"><i class="fa fa-key fa-lg"></i></span>
                                        <input type="password" name="password2" placeholder="Confirmar password" class="form-control p-2" id="password2" required minlength="4" maxlength="12">
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" name="legals" value="checked" class="form-check-input" id="legals">
                                        <label class="form-check-label" for="legals">Acepto los términos y condiciones</label>
                                    </div>
                                    <button type="submit" name="submit" class="btn btn-danger mb-3 p-3" style="width:100%;">Registrar</button>
                                    <p>¿Ya tienes una cuenta? <a href="./login.php">Login</a></p>
                                </form>
                                
                                <?php 
                                    if(isset($errores)){
                                        foreach ($errores as $error){
                                            echo '<span class="text-danger">*'. $error .'</span> <br>';
                                        }
                                    }    
                                    if(isset($registered)){
                                        echo '<div class="alert alert-success">'. $registered. '</div> <br>';
                                    }    
                                ?>
                               
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        
        </div>
    </div>
            
<?php require_once ("footer.php") ?>